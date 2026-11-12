<?php

namespace App\Livewire;

use App\Livewire\Concerns\ValidatesTransactionDraft;
use App\Models\Business;
use App\Models\Transaction;
use App\Services\Ai\GroqService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class CreateExpenseVoiceTransaction extends Component
{
    use WithFileUploads;
    use ValidatesTransactionDraft;

    public string $sourceType = 'voice';

    /**
     * Berkas audio yang direkam atau diunggah (temporary file).
     */
    public $audioFile = null;

    /**
     * Alur antarmuka: 'record' (perekaman) | 'review' (konfirmasi draft).
     */
    public string $step = 'record';

    /**
     * Indikator status pengerjaan AI.
     */
    public bool $isProcessing = false;

    /**
     * Teks hasil transkripsi Whisper STT.
     */
    public ?string $transcriptionText = null;

    /**
     * Data draft transaksi hasil parsing AI.
     *
     * @var array<string, mixed>
     */
    public array $draft = [
        'category' => 'bahan_baku',
        'amount' => 0,
        'transaction_date' => '',
        'merchant_name' => '',
        'notes' => '',
        'confidence_score' => 1.0,
        'needs_review' => false,
        'unclear_fields' => [],
        'review_reason' => null,
        'items' => [],
    ];

    /**
     * Pesan kesalahan jika terjadi kegagalan.
     */
    public ?string $errorMessage = null;

    public function mount(): void
    {
        $this->draft['transaction_date'] = now()->toDateString();
    }

    /**
     * Ambil profil usaha aktif milik pengguna yang sedang terautentikasi.
     */
    protected function getActiveBusiness(): ?Business
    {
        return auth()->user()?->businesses()->first();
    }

    /**
     * Proses audio: transkripsi Whisper -> ekstraksi LLM ke format JSON.
     */
    public function processVoice(GroqService $groqService): void
    {
        $this->errorMessage = null;
        $this->isProcessing = true;

        if (!$this->audioFile) {
            $this->errorMessage = 'Belum ada rekaman atau berkas audio yang dipilih.';
            $this->isProcessing = false;
            return;
        }

        $business = $this->getActiveBusiness();
        if (!$business) {
            session()->flash('message', 'Silakan lengkapi profil usaha terlebih dahulu.');
            $this->redirect(route('onboarding'), navigate: true);
            return;
        }

        try {
            $filePath = $this->audioFile->getRealPath();

            // 1. Transkripsi suara ke teks menggunakan Groq Whisper
            $transcript = $groqService->transcribeAudio(
                audioFilePath: $filePath,
                language: 'id',
                businessId: $business->id,
                userId: auth()->id()
            );

            $transcript = trim($transcript);

            if (empty($transcript) || mb_strlen($transcript) < 3) {
                $this->errorMessage = 'Suara tidak terdengar jelas atau hening. Harap dekatkan mikrofon dan rekam kembali.';
                $this->isProcessing = false;
                return;
            }

            $this->transcriptionText = $transcript;

            // 2. Ekstraksi data transaksi dari teks transkrip
            $extracted = $groqService->extractFromText(
                text: $transcript,
                businessType: $business->business_type,
                businessId: $business->id,
                userId: auth()->id()
            );

            // 3. Normalisasi items untuk form review
            $items = [];
            foreach ($extracted['items'] ?? [] as $it) {
                $qty = (float) ($it['quantity'] ?? 1);
                $unitPrice = (float) ($it['price_per_unit'] ?? $it['unit_price'] ?? 0);
                $subtotal = (float) ($it['subtotal'] ?? ($qty * $unitPrice));

                $items[] = [
                    'item_name' => $it['item_name'] ?? 'Barang Belanjaan',
                    'quantity' => $qty,
                    'unit' => $it['unit'] ?? 'pcs',
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ];
            }

            $this->draft = [
                'category' => in_array($extracted['category'] ?? '', ['bahan_baku', 'operasional'], true)
                    ? $extracted['category']
                    : 'bahan_baku',
                'amount' => (float) ($extracted['total_amount'] ?? 0),
                'transaction_date' => $extracted['transaction_date'] ?? now()->toDateString(),
                'merchant_name' => $extracted['merchant_name'] ?? '',
                'notes' => $extracted['notes'] ?? $transcript,
                'confidence_score' => (float) ($extracted['confidence_score'] ?? 0.9),
                'needs_review' => (bool) ($extracted['needs_review'] ?? false),
                'unclear_fields' => $extracted['unclear_fields'] ?? [],
                'review_reason' => $extracted['review_reason'] ?? null,
                'items' => $items,
            ];

            $this->recalculateMathReconciliation();

            // Pindah ke layar konfirmasi draft
            $this->step = 'review';
        } catch (\Throwable $e) {
            Log::error('Voice Transaction Processing Failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            $this->errorMessage = 'Terjadi kendala saat memproses suara dengan AI: ' . $e->getMessage();
        } finally {
            $this->isProcessing = false;
        }
    }

    /**
     * Tambah baris rincian barang baru di layar konfirmasi.
     */
    public function addItem(): void
    {
        $this->draft['items'][] = [
            'item_name' => '',
            'quantity' => 1,
            'unit' => 'pcs',
            'unit_price' => 0,
            'subtotal' => 0,
        ];
    }

    /**
     * Hapus baris rincian barang.
     */
    public function removeItem(int $index): void
    {
        if (isset($this->draft['items'][$index])) {
            unset($this->draft['items'][$index]);
            $this->draft['items'] = array_values($this->draft['items']);
            $this->recalculateTotal();
        }
    }

    /**
     * Hitung ulang total belanja berdasarkan subtotal barang.
     */
    public function recalculateTotal(): void
    {
        $sum = 0.0;
        foreach ($this->draft['items'] as $item) {
            $sum += (float) ($item['subtotal'] ?? 0);
        }

        if ($sum > 0) {
            $this->draft['amount'] = $sum;
        }

        $this->recalculateMathReconciliation();
    }

    /**
     * Update subtotal item otomatis ketika quantity atau unit_price diubah.
     */
    public function updateItemSubtotal(int $index): void
    {
        if (isset($this->draft['items'][$index])) {
            $qty = (float) ($this->draft['items'][$index]['quantity'] ?? 0);
            $price = (float) ($this->draft['items'][$index]['unit_price'] ?? 0);
            $this->draft['items'][$index]['subtotal'] = $qty * $price;
            $this->recalculateTotal();
        }
    }

    /**
     * Kembali ke langkah perekaman suara.
     */
    public function resetToRecord(): void
    {
        $this->step = 'record';
        $this->audioFile = null;
        $this->transcriptionText = null;
        $this->errorMessage = null;
        $this->showDuplicateModal = false;
        $this->duplicateConfirmed = false;
        $this->duplicateInfo = null;
        $this->mathReconciliation = null;
        $this->draft = [
            'category' => 'bahan_baku',
            'amount' => 0,
            'transaction_date' => now()->toDateString(),
            'merchant_name' => '',
            'notes' => '',
            'confidence_score' => 1.0,
            'needs_review' => false,
            'unclear_fields' => [],
            'review_reason' => null,
            'items' => [],
        ];
    }

    /**
     * Simpan transaksi pengeluaran final ke database kasivo_db.
     */
    public function saveTransaction()
    {
        $business = $this->getActiveBusiness();

        if (!$business) {
            session()->flash('message', 'Silakan daftarkan profil usaha terlebih dahulu.');
            return redirect()->route('onboarding');
        }

        $this->validate([
            'draft.amount' => 'required|numeric|min:100',
            'draft.category' => 'required|in:bahan_baku,operasional',
            'draft.transaction_date' => 'required|date|before_or_equal:today',
            'draft.notes' => 'nullable|string|max:500',
            'draft.items' => 'nullable|array|max:50',
            'draft.items.*.item_name' => 'nullable|string|max:100',
        ], [
            'draft.amount.required' => 'Nominal pengeluaran wajib diisi.',
            'draft.amount.min' => 'Nominal pengeluaran minimal Rp 100.',
            'draft.category.required' => 'Pilih kategori pengeluaran.',
            'draft.transaction_date.required' => 'Tanggal transaksi wajib diisi.',
            'draft.transaction_date.before_or_equal' => 'Tanggal tidak boleh melebihi hari ini.',
            'draft.items.max' => 'Rincian barang maksimal 50 baris.',
            'draft.items.*.item_name.max' => 'Nama barang maksimal 100 karakter.',
        ]);

        $lockKey = "business_save_tx:{$business->id}";
        $lock = Cache::lock($lockKey, 5);

        if (! $lock->get()) {
            return;
        }

        try {
            if (! $this->checkDuplicateSafety($business)) {
                return;
            }

            DB::transaction(function () use ($business) {
                $transaction = $business->transactions()->create([
                    'type' => 'cash_out',
                    'category' => $this->draft['category'],
                    'amount' => (float) $this->draft['amount'],
                    'transaction_date' => $this->draft['transaction_date'],
                    'source_type' => 'voice',
                    'notes' => trim($this->draft['notes'] ?: $this->draft['merchant_name'] ?: 'Pengeluaran via Suara'),
                    'is_cash_settled' => true,
                ]);

                foreach ($this->draft['items'] as $item) {
                    $itemName = trim($item['item_name'] ?? '');
                    if ($itemName !== '') {
                        $transaction->items()->create([
                            'item_name' => $itemName,
                            'quantity' => max(0.01, (float) ($item['quantity'] ?? 1)),
                            'unit' => $item['unit'] ?? 'pcs',
                            'unit_price' => (float) ($item['unit_price'] ?? 0),
                            'subtotal' => (float) ($item['subtotal'] ?? 0),
                        ]);
                    }
                }
            });

            session()->flash('message', 'Pengeluaran sebesar Rp ' . number_format((float) $this->draft['amount'], 0, ',', '.') . ' berhasil dicatat!');

            return redirect()->route('transactions.index');
        } finally {
            $lock->release();
        }
    }

    public function render()
    {
        $business = $this->getActiveBusiness();

        return view('livewire.create-expense-voice-transaction', [
            'hasBusiness' => (bool) $business,
            'business' => $business,
        ]);
    }
}
