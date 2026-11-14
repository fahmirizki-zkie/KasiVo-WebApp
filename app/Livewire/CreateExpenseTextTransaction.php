<?php

namespace App\Livewire;

use App\Exceptions\AiQuotaExceededException;
use App\Livewire\Concerns\ValidatesTransactionDraft;
use App\Models\Business;
use App\Models\Transaction;
use App\Services\Ai\GroqService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class CreateExpenseTextTransaction extends Component
{
    use ValidatesTransactionDraft;
    public string $sourceType = 'text';

    /**
     * Input teks bebas dari pedagang.
     */
    public string $rawText = '';

    /**
     * Alur antarmuka: 'input' (mengetik teks) | 'review' (konfirmasi draft).
     */
    public string $step = 'input';

    /**
     * Indikator animasi loading pemrosesan AI.
     */
    public bool $isProcessing = false;

    /**
     * Data draft transaksi hasil ekstraksi AI.
     *
     * @var array<string, mixed>
     */
    public array $draft = [
        'type' => 'cash_out',
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
     * Pesan kesalahan jika terjadi kendala.
     */
    public ?string $errorMessage = null;

    /**
     * Jenis kegagalan (misal: 'quota_exceeded', 'error').
     */
    public ?string $errorType = null;

    public function mount(): void
    {
        $this->draft['transaction_date'] = now()->toDateString();
    }

    /**
     * Ambil profil usaha aktif milik pengguna yang terautentikasi.
     */
    protected function getActiveBusiness(): ?Business
    {
        return auth()->user()?->businesses()->first();
    }

    /**
     * Isi cepat textarea dengan template contoh kalimat warung.
     */
    public function applyTemplate(string $template): void
    {
        $this->rawText = $template;
        $this->errorMessage = null;
    }

    /**
     * Proses teks bebas pengguna menggunakan Groq LLM.
     */
    public function processText(GroqService $groqService): void
    {
        $this->errorMessage = null;

        $this->validate([
            'rawText' => 'required|string|min:5|max:1000',
        ], [
            'rawText.required' => 'Tuliskan catatan transaksi terlebih dahulu.',
            'rawText.min' => 'Catatan transaksi minimal 5 karakter (contoh: "Beli beras 50rb").',
            'rawText.max' => 'Catatan transaksi maksimal 1.000 karakter.',
        ]);

        $business = $this->getActiveBusiness();
        if (!$business) {
            session()->flash('message', 'Silakan daftarkan profil usaha terlebih dahulu.');
            $this->redirect(route('onboarding'), navigate: true);
            return;
        }

        $this->isProcessing = true;

        try {
            // Ekstraksi data terstruktur via GroqService
            $extracted = $groqService->extractFromText(
                text: trim($this->rawText),
                businessType: $business->business_type,
                businessId: $business->id,
                userId: auth()->id()
            );

            // Normalisasi items
            $items = [];
            foreach ($extracted['items'] ?? [] as $it) {
                $qty = (float) ($it['quantity'] ?? 1);
                $unitPrice = (float) ($it['price_per_unit'] ?? $it['unit_price'] ?? 0);
                $subtotal = (float) ($it['subtotal'] ?? ($qty * $unitPrice));

                $items[] = [
                    'item_name' => $it['item_name'] ?? 'Barang',
                    'quantity' => $qty,
                    'unit' => $it['unit'] ?? 'pcs',
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ];
            }

            $type = in_array($extracted['type'] ?? '', ['cash_out', 'cash_in'], true)
                ? $extracted['type']
                : 'cash_out';

            $defaultCategory = $type === 'cash_in' ? 'penjualan' : 'bahan_baku';
            $category = !empty($extracted['category']) ? $extracted['category'] : $defaultCategory;

            $this->draft = [
                'type' => $type,
                'category' => $category,
                'amount' => (float) ($extracted['total_amount'] ?? 0),
                'transaction_date' => $extracted['transaction_date'] ?? now()->toDateString(),
                'merchant_name' => $extracted['merchant_name'] ?? '',
                'notes' => $extracted['notes'] ?? trim($this->rawText),
                'confidence_score' => (float) ($extracted['confidence_score'] ?? 0.95),
                'needs_review' => (bool) ($extracted['needs_review'] ?? false),
                'unclear_fields' => $extracted['unclear_fields'] ?? [],
                'review_reason' => $extracted['review_reason'] ?? null,
                'items' => $items,
            ];

            $this->recalculateMathReconciliation();

            // Pindah ke tampilan review
            $this->step = 'review';
            $this->errorMessage = null;
            $this->errorType = null;
        } catch (AiQuotaExceededException $e) {
            Log::warning('Text AI Quota Exceeded: ' . $e->getMessage());
            $this->errorType = 'quota_exceeded';
            $this->errorMessage = $e->getMessage();
        } catch (\Throwable $e) {
            Log::error('Text Transaction Extraction Failed: ' . $e->getMessage(), [
                'text' => $this->rawText,
            ]);
            $this->errorType = 'error';
            $this->errorMessage = $this->sanitizeAiErrorMessage($e, 'text');
        } finally {
            $this->isProcessing = false;
        }
    }

    /**
     * Sanitasi pesan error teknis menjadi pesan ramah pengguna UMKM.
     */
    protected function sanitizeAiErrorMessage(\Throwable $e, string $mode = 'text'): string
    {
        $raw = strtolower($e->getMessage());

        if (str_contains($raw, 'api key') || str_contains($raw, 'groq_api_key')) {
            return 'Layanan AI belum tersedia saat ini (Konfigurasi GROQ_API_KEY belum tersedia di sistem). Anda tetap dapat mencatat transaksi secara manual.';
        }

        if (
            str_contains($raw, 'curl error') ||
            str_contains($raw, 'timed out') ||
            str_contains($raw, 'timeout') ||
            str_contains($raw, 'could not resolve host') ||
            str_contains($raw, 'failed to connect') ||
            str_contains($raw, 'connection refused') ||
            str_contains($raw, 'network')
        ) {
            return 'Koneksi internet atau layanan AI sedang mengalami gangguan. Silakan coba kembali atau lanjutkan pencatatan secara manual.';
        }

        return 'Catatan teks belum dapat diproses dengan baik. Anda dapat mencoba kembali atau memasukkan nominal transaksi secara manual.';
    }

    /**
     * Penyelamat darurat: jika AI kuota penuh atau offline, pedagang langsung masuk ke layar konfirmasi
     * dengan teks catatan tetap terjaga dan tinggal mengisi nominal.
     */
    public function proceedToEmergencyReview(): void
    {
        $this->draft = [
            'type' => 'cash_out',
            'category' => 'bahan_baku',
            'amount' => 0,
            'transaction_date' => now()->toDateString(),
            'merchant_name' => '',
            'notes' => trim($this->rawText) ?: 'Pengisian darurat (koreksi langsung)',
            'confidence_score' => 1.0,
            'needs_review' => true,
            'unclear_fields' => ['amount'],
            'review_reason' => 'Pengisian darurat: kuota AI penuh atau kendala koneksi.',
            'items' => [],
        ];

        $this->step = 'review';
        $this->errorMessage = null;
        $this->errorType = null;
        $this->recalculateMathReconciliation();
    }

    /**
     * Coba ulang memproses teks dengan AI.
     */
    public function retryProcess(): void
    {
        $this->errorMessage = null;
        $this->errorType = null;
        $this->processText(app(GroqService::class));
    }

    /**
     * Tambah baris barang baru di layar konfirmasi.
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
     * Hapus baris barang.
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
     * Hitung ulang subtotal item ketika kuantitas atau harga satuan diubah.
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
     * Hitung ulang total transaksi dari akumulasi subtotal barang.
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
     * Kembali ke formulir input teks.
     */
    public function resetToInput(): void
    {
        $this->step = 'input';
        $this->errorMessage = null;
        $this->errorType = null;
        $this->showDuplicateModal = false;
        $this->duplicateConfirmed = false;
        $this->duplicateInfo = null;
        $this->mathReconciliation = null;
        $this->draft = [
            'type' => 'cash_out',
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
     * Simpan transaksi ke database.
     */
    public function saveTransaction()
    {
        $business = $this->getActiveBusiness();

        if (!$business) {
            session()->flash('message', 'Silakan daftarkan profil usaha terlebih dahulu.');
            return redirect()->route('onboarding');
        }

        $this->validate([
            'draft.type' => 'required|in:cash_out,cash_in',
            'draft.amount' => 'required|numeric|min:100',
            'draft.category' => 'required|string',
            'draft.transaction_date' => 'required|date|before_or_equal:today',
            'draft.notes' => 'nullable|string|max:500',
            'draft.items' => 'nullable|array|max:50',
            'draft.items.*.item_name' => 'nullable|string|max:100',
        ], [
            'draft.type.required' => 'Pilih jenis transaksi (kas keluar / kas masuk).',
            'draft.amount.required' => 'Nominal transaksi wajib diisi.',
            'draft.amount.min' => 'Nominal transaksi minimal Rp 100.',
            'draft.category.required' => 'Pilih kategori transaksi.',
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
                $type = $this->draft['type'];
                $defaultNote = $type === 'cash_in' ? 'Pemasukan Kas via Teks' : 'Pengeluaran Kas via Teks';

                $transaction = $business->transactions()->create([
                    'type' => $type,
                    'category' => $this->draft['category'],
                    'amount' => (float) $this->draft['amount'],
                    'transaction_date' => $this->draft['transaction_date'],
                    'source_type' => 'text',
                    'notes' => trim($this->draft['notes'] ?: $this->draft['merchant_name'] ?: $defaultNote),
                    'is_cash_settled' => true,
                ]);

                foreach ($this->draft['items'] as $item) {
                    $name = trim($item['item_name'] ?? '');
                    if ($name !== '') {
                        $transaction->items()->create([
                            'item_name' => $name,
                            'quantity' => max(0.01, (float) ($item['quantity'] ?? 1)),
                            'unit' => $item['unit'] ?? 'pcs',
                            'unit_price' => (float) ($item['unit_price'] ?? 0),
                            'subtotal' => (float) ($item['subtotal'] ?? 0),
                        ]);
                    }
                }
            });

            $label = $this->draft['type'] === 'cash_in' ? 'Pemasukan' : 'Pengeluaran';
            session()->flash('message', "{$label} sebesar Rp " . number_format((float) $this->draft['amount'], 0, ',', '.') . ' berhasil dicatat!');

            return redirect()->route('transactions.index');
        } finally {
            $lock->release();
        }
    }

    public function render()
    {
        $business = $this->getActiveBusiness();

        return view('livewire.create-expense-text-transaction', [
            'hasBusiness' => (bool) $business,
            'business' => $business,
        ]);
    }
}

