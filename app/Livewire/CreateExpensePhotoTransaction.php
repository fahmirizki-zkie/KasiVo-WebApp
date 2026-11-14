<?php

namespace App\Livewire;

use App\Exceptions\AiQuotaExceededException;
use App\Livewire\Concerns\ValidatesTransactionDraft;
use App\Models\Business;
use App\Models\Transaction;
use App\Services\Ai\GroqService;
use App\Services\AttachmentService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class CreateExpensePhotoTransaction extends Component
{
    use WithFileUploads;
    use ValidatesTransactionDraft;

    public string $sourceType = 'photo';

    /**
     * Berkas foto nota yang dipilih/difoto (temporary file).
     */
    public $photo = null;

    /**
     * Alur antarmuka: 'upload' (pilih/foto struk) | 'review' (konfirmasi draft).
     */
    public string $step = 'upload';

    /**
     * Indikator status pengerjaan Vision AI.
     */
    public bool $isProcessing = false;

    /**
     * Data draft transaksi hasil ekstraksi Vision AI.
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

    /**
     * Jenis kegagalan (misal: 'quota_exceeded', 'error').
     */
    public ?string $errorType = null;

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
     * Hook validasi otomatis saat berkas foto dipilih.
     */
    public function updatedPhoto(): void
    {
        $this->errorMessage = null;

        $this->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ], [
            'photo.required' => 'Pilih atau potret foto nota terlebih dahulu.',
            'photo.image' => 'Berkas yang dipilih harus berupa file gambar.',
            'photo.mimes' => 'Format foto harus berupa JPG, PNG, atau WEBP.',
            'photo.max' => 'Ukuran foto maksimal 5 MB.',
        ]);
    }

    /**
     * Proses foto nota menggunakan Vision AI (Groq Qwen Multimodal).
     */
    public function processPhoto(GroqService $groqService, AttachmentService $attachmentService): void
    {
        $this->errorMessage = null;

        $this->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ], [
            'photo.required' => 'Pilih atau potret foto nota terlebih dahulu.',
            'photo.image' => 'Berkas yang dipilih harus berupa gambar.',
            'photo.mimes' => 'Format foto harus JPG, PNG, atau WEBP.',
            'photo.max' => 'Ukuran foto maksimal 5 MB.',
        ]);

        $business = $this->getActiveBusiness();
        if (!$business) {
            session()->flash('message', 'Silakan lengkapi profil usaha terlebih dahulu.');
            $this->redirect(route('onboarding'), navigate: true);
            return;
        }

        $this->isProcessing = true;

        try {
            $filePath = $this->photo->getRealPath();
            $fileContent = file_get_contents($filePath);
            $base64 = base64_encode($fileContent);
            $mimeType = $this->photo->getMimeType() ?: 'image/jpeg';

            // 1. Ekstraksi multimodal foto struk
            $extracted = $groqService->extractFromImage(
                base64Image: $base64,
                mimeType: $mimeType,
                additionalContext: "Usaha: {$business->name} ({$business->business_type})",
                businessType: $business->business_type,
                businessId: $business->id,
                userId: auth()->id()
            );

            // 2. Normalisasi rincian items barang
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

            $merchant = trim((string) ($extracted['merchant_name'] ?? ''));
            $defaultNotes = $merchant !== '' ? "Belanja di {$merchant}" : 'Pengeluaran Struk Belanja';

            $this->draft = [
                'category' => in_array($extracted['category'] ?? '', ['bahan_baku', 'operasional'], true)
                    ? $extracted['category']
                    : 'bahan_baku',
                'amount' => (float) ($extracted['total_amount'] ?? 0),
                'transaction_date' => $extracted['transaction_date'] ?? now()->toDateString(),
                'merchant_name' => $merchant,
                'notes' => $extracted['notes'] ?? $defaultNotes,
                'confidence_score' => (float) ($extracted['confidence_score'] ?? 0.95),
                'needs_review' => (bool) ($extracted['needs_review'] ?? false),
                'unclear_fields' => $extracted['unclear_fields'] ?? [],
                'review_reason' => $extracted['review_reason'] ?? null,
                'items' => $items,
            ];

            $this->recalculateMathReconciliation();

            // 3. Pindah ke layar konfirmasi draft
            $this->step = 'review';
            $this->errorType = null;
            $this->errorMessage = null;
        } catch (AiQuotaExceededException $e) {
            Log::warning('Photo Vision AI Quota Exceeded: ' . $e->getMessage());
            $this->errorType = 'quota_exceeded';
            $this->errorMessage = $e->getMessage();
        } catch (\Throwable $e) {
            Log::error('Photo Transaction Extraction Failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            $this->errorType = 'error';
            $this->errorMessage = $this->sanitizeAiErrorMessage($e, 'photo');
        } finally {
            $this->isProcessing = false;
        }
    }

    /**
     * Sanitasi pesan error teknis menjadi pesan ramah pengguna UMKM.
     */
    protected function sanitizeAiErrorMessage(\Throwable $e, string $mode = 'photo'): string
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

        return 'Nota belum dapat dibaca dengan baik. Anda dapat mencoba kembali atau memasukkan nominal transaksi secara manual.';
    }

    /**
     * Penyelamat darurat: jika AI kuota penuh atau offline, pedagang langsung masuk ke layar konfirmasi
     * dengan foto nota tetap utuh dan tinggal mengisi nominal.
     */
    public function proceedToEmergencyReview(): void
    {
        $this->draft = [
            'category' => 'bahan_baku',
            'amount' => 0,
            'transaction_date' => now()->toDateString(),
            'merchant_name' => '',
            'notes' => 'Pengisian darurat (koreksi langsung)',
            'confidence_score' => 1.0,
            'needs_review' => true,
            'unclear_fields' => ['amount'],
            'review_reason' => 'Pengisian darurat: kuota AI penuh atau nota tidak terbaca otomatis.',
            'items' => [],
        ];

        $this->step = 'review';
        $this->errorMessage = null;
        $this->errorType = null;
        $this->recalculateMathReconciliation();
    }

    /**
     * Coba ulang memproses foto dengan AI.
     */
    public function retryProcess(): void
    {
        $this->errorMessage = null;
        $this->errorType = null;
        $this->processPhoto(app(GroqService::class), app(AttachmentService::class));
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
     * Hitung ulang subtotal item ketika quantity atau unit_price diubah.
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
     * Hitung ulang total belanja berdasarkan akumulasi subtotal barang.
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
     * Kembali ke langkah pilih/foto struk.
     */
    public function resetToUpload(): void
    {
        $this->step = 'upload';
        $this->photo = null;
        $this->errorMessage = null;
        $this->errorType = null;
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
     * Simpan transaksi pengeluaran struk dan ikat lampiran foto ke database.
     */
    public function saveTransaction(AttachmentService $attachmentService)
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

            DB::transaction(function () use ($business, $attachmentService) {
                // 1. Simpan Transaksi Kas Keluar
                $transaction = $business->transactions()->create([
                    'type' => 'cash_out',
                    'category' => $this->draft['category'],
                    'amount' => (float) $this->draft['amount'],
                    'transaction_date' => $this->draft['transaction_date'],
                    'source_type' => 'photo',
                    'notes' => trim($this->draft['notes'] ?: $this->draft['merchant_name'] ?: 'Pengeluaran via Foto Nota'),
                    'is_cash_settled' => true,
                ]);

                // 2. Simpan Rincian Items Barang
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

                // 3. Simpan dan Ikat Foto Bukti Struk ke tabel attachments
                if ($this->photo) {
                    $attachmentService->upload(
                        file: $this->photo,
                        transactionId: $transaction->id,
                        source: 'camera_nota'
                    );
                }
            });

            session()->flash('message', 'Pengeluaran struk sebesar Rp ' . number_format((float) $this->draft['amount'], 0, ',', '.') . ' berhasil dicatat!');

            return redirect()->route('transactions.index');
        } finally {
            $lock->release();
        }
    }

    public function render()
    {
        $business = $this->getActiveBusiness();

        return view('livewire.create-expense-photo-transaction', [
            'hasBusiness' => (bool) $business,
            'business' => $business,
        ]);
    }
}

