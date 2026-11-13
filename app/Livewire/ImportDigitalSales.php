<?php

namespace App\Livewire;

use App\Exceptions\AiQuotaExceededException;
use App\Livewire\Concerns\ValidatesTransactionDraft;
use App\Models\Business;
use App\Services\Ai\TransactionJsonSchema;
use App\Services\AttachmentService;
use App\Services\DigitalSalesParserService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class ImportDigitalSales extends Component
{
    use WithFileUploads;
    use ValidatesTransactionDraft;

    /**
     * Berkas yang diunggah (tangkapan layar / dokumen spreadsheet CSV/XLSX).
     */
    public $uploadedFile = null;

    /**
     * Alur tahapan antarmuka:
     * - 'upload': pemilihan / drag-and-drop berkas
     * - 'preview_file': berkas valid diterima, siap ditinjau sebelum diekstrak
     * - 'parsing': sedang diekstrak oleh AI / parser tabular (KV-036)
     * - 'review': layar konfirmasi hasil ekstraksi sebelum disimpan (KV-037)
     */
    public string $step = 'upload';

    /**
     * Tipe berkas yang terdeteksi: 'screenshot' | 'spreadsheet' | ''.
     */
    public string $fileType = '';

    /**
     * Pilihan platform digital asal: 'auto' | 'gofood' | 'grabfood' | 'shopeefood' | 'qris' | 'pos' | 'other'.
     */
    public string $platform = 'auto';

    /**
     * Metadata berkas yang berhasil diunggah.
     *
     * @var array<string, mixed>
     */
    public array $fileMeta = [];

    /**
     * Data hasil ekstraksi AI / parser tabular yang siap ditinjau (KV-036).
     *
     * @var array<string, mixed>
     */
    public array $parsedData = [];

    /**
     * Draf transaksi untuk validasi duplikasi & keselamatan data.
     *
     * @var array<string, mixed>
     */
    public array $draft = [
        'amount' => 0.0,
        'type' => 'cash_in',
        'category' => 'penjualan',
        'transaction_date' => '',
        'notes' => '',
    ];

    /**
     * Status seleksi semua baris data spreadsheet.
     */
    public bool $selectAllRows = true;

    /**
     * Pesan kesalahan jika format ditolak atau proses parsing gagal.
     */
    public ?string $errorMessage = null;

    /**
     * Tipe kesalahan: 'general' | 'quota'.
     */
    public string $errorType = 'general';

    /**
     * Status proses background / loading state.
     */
    public bool $isProcessing = false;

    public function mount(): void
    {
        $business = auth()->user()?->businesses()->first();

        if (!$business) {
            session()->flash('message', 'Silakan daftarkan profil usaha Anda terlebih dahulu.');
            redirect()->route('onboarding');
        }
    }

    /**
     * Hook Livewire: dipanggil seketika saat user memilih berkas.
     */
    public function updatedUploadedFile(AttachmentService $attachmentService): void
    {
        $this->errorMessage = null;
        $this->errorType = 'general';

        if (!$this->uploadedFile) {
            return;
        }

        try {
            // Validasi format dan batasan ukuran berkas sesuai spesifikasi MVP
            $detectedType = $attachmentService->validateDigitalImportFile($this->uploadedFile);

            $this->fileType = $detectedType;
            $this->fileMeta = [
                'name' => $this->uploadedFile->getClientOriginalName(),
                'size' => $this->formatBytes($this->uploadedFile->getSize()),
                'size_bytes' => $this->uploadedFile->getSize(),
                'extension' => strtolower($this->uploadedFile->getClientOriginalExtension() ?: ''),
                'mime' => $this->uploadedFile->getMimeType(),
            ];

            $this->step = 'preview_file';
        } catch (InvalidArgumentException $e) {
            $this->errorMessage = $e->getMessage();
            $this->reset(['uploadedFile', 'fileMeta', 'fileType']);
            $this->step = 'upload';
        }
    }

    /**
     * Hapus berkas yang dipilih dan kembali ke dropzone awal.
     */
    public function removeFile(): void
    {
        $this->reset(['uploadedFile', 'fileMeta', 'fileType', 'errorMessage', 'parsedData', 'draft']);
        $this->errorType = 'general';
        $this->step = 'upload';
    }

    /**
     * Pilih platform digital asal.
     */
    public function setPlatform(string $platform): void
    {
        $allowed = ['auto', 'gofood', 'grabfood', 'shopeefood', 'qris', 'pos', 'other'];

        if (in_array($platform, $allowed, true)) {
            $this->platform = $platform;
        }
    }

    /**
     * Lanjut ke tahap parsing (KV-035).
     */
    public function proceedToParsing(): void
    {
        if (!$this->uploadedFile || empty($this->fileType)) {
            $this->errorMessage = 'Silakan pilih berkas tangkapan layar atau dokumen spreadsheet terlebih dahulu.';
            $this->step = 'upload';
            return;
        }

        $this->step = 'parsing';
    }

    /**
     * Menjalankan ekstraksi data impor (Vision AI atau Tabular Parser) sesuai KV-036.
     */
    public function parseUploadedFile(DigitalSalesParserService $parserService): void
    {
        if (!$this->uploadedFile || empty($this->fileType)) {
            $this->errorMessage = 'Silakan pilih berkas tangkapan layar atau dokumen spreadsheet terlebih dahulu.';
            $this->step = 'upload';
            return;
        }

        $this->isProcessing = true;
        $this->errorMessage = null;
        $this->errorType = 'general';
        $this->step = 'parsing';

        try {
            if ($this->fileType === 'screenshot') {
                $this->parsedData = $parserService->parseScreenshot(
                    file: $this->uploadedFile,
                    platformHint: $this->platform
                );
            } else {
                $this->parsedData = $parserService->parseSpreadsheet(
                    file: $this->uploadedFile,
                    extension: $this->fileMeta['extension'] ?? 'csv',
                    platformHint: $this->platform
                );
            }

            // Inisialisasi persetujuan semua baris (DoD KV-037)
            if (!empty($this->parsedData['rows'])) {
                foreach ($this->parsedData['rows'] as &$row) {
                    $row['approved'] = true;
                }
                unset($row);
            }
            $this->selectAllRows = true;
            $this->syncDraftForValidation();

            // Transisi ke tahap pratinjau hasil ekstraksi (DoD KV-036 & KV-037)
            $this->step = 'review';
        } catch (AiQuotaExceededException $e) {
            $this->errorType = 'quota';
            $this->errorMessage = 'Kuota AI Anda sedang penuh atau token hari ini telah habis. Anda tetap dapat melanjutkan pencatatan penjualan secara mandiri tanpa kehilangan berkas.';
            $this->step = 'preview_file';
        } catch (\Throwable $e) {
            $this->errorType = 'general';
            $this->errorMessage = 'Gagal mengekstrak data dari berkas: ' . $e->getMessage();
            $this->step = 'preview_file';
        } finally {
            $this->isProcessing = false;
        }
    }

    /**
     * Mode penanganan darurat tanpa AI (Zero Data Loss) jika kuota penuh atau AI terkendala.
     */
    public function proceedToEmergencyReview(): void
    {
        $effectivePlatform = $this->platform !== 'auto' ? $this->platform : 'other';

        $this->parsedData = [
            'type' => TransactionJsonSchema::TYPE_CASH_IN,
            'category' => TransactionJsonSchema::CATEGORY_PENJUALAN,
            'platform' => $effectivePlatform,
            'transaction_date' => now()->toDateString(),
            'total_amount' => 0.0,
            'gross_amount' => 0.0,
            'fee_amount' => 0.0,
            'net_amount' => 0.0,
            'order_count' => 0,
            'merchant_name' => null,
            'notes' => 'Pencatatan Penjualan Digital Mandiri (Darurat)',
            'confidence_score' => 1.0,
            'needs_review' => true,
            'unclear_fields' => ['total_amount'],
            'review_reason' => 'Mode darurat (kuota penuh / koneksi AI terputus). Diisi mandiri oleh pengguna.',
            'items' => [],
            'rows' => [
                [
                    'date' => now()->toDateString(),
                    'amount' => 0.0,
                    'gross_amount' => 0.0,
                    'fee_amount' => 0.0,
                    'platform' => $effectivePlatform,
                    'notes' => 'Pencatatan Penjualan Digital Mandiri',
                    'approved' => true,
                ],
            ],
        ];

        $this->selectAllRows = true;
        $this->syncDraftForValidation();

        $this->errorMessage = null;
        $this->errorType = 'general';
        $this->step = 'review';
    }

    /**
     * Toggle persetujuan baris transaksi tertentu (DoD KV-037).
     */
    public function toggleRowApproval(int $index): void
    {
        if (isset($this->parsedData['rows'][$index])) {
            $current = !empty($this->parsedData['rows'][$index]['approved']);
            $this->parsedData['rows'][$index]['approved'] = !$current;
            $this->recalculateApprovedTotal();
        }
    }

    /**
     * Toggle pilih semua baris transaksi (DoD KV-037).
     */
    public function toggleSelectAll(): void
    {
        $this->selectAllRows = !$this->selectAllRows;

        if (!empty($this->parsedData['rows'])) {
            foreach ($this->parsedData['rows'] as &$row) {
                $row['approved'] = $this->selectAllRows;
            }
            unset($row);
        }

        $this->recalculateApprovedTotal();
    }

    /**
     * Hitung ulang total penjualan hanya dari baris-baris yang disetujui (DoD KV-037).
     */
    public function recalculateApprovedTotal(): void
    {
        if (empty($this->parsedData['rows'])) {
            return;
        }

        $totalApproved = 0.0;
        $totalGross = 0.0;
        $totalFee = 0.0;
        $approvedCount = 0;

        foreach ($this->parsedData['rows'] as $row) {
            if (!empty($row['approved'])) {
                $totalApproved += (float) ($row['amount'] ?? 0);
                $totalGross += (float) ($row['gross_amount'] ?? $row['amount'] ?? 0);
                $totalFee += (float) ($row['fee_amount'] ?? 0);
                $approvedCount++;
            }
        }

        $this->parsedData['total_amount'] = $totalApproved;
        $this->parsedData['net_amount'] = $totalApproved;
        $this->parsedData['gross_amount'] = $totalGross;
        $this->parsedData['fee_amount'] = $totalFee;
        $this->parsedData['order_count'] = $approvedCount;

        $this->syncDraftForValidation();
    }

    /**
     * Sinkronisasi draf transaksi agar konsisten dengan `ValidatesTransactionDraft`.
     */
    protected function syncDraftForValidation(): void
    {
        $this->draft = [
            'amount' => (float) ($this->parsedData['total_amount'] ?? 0),
            'type' => 'cash_in',
            'category' => 'penjualan',
            'transaction_date' => $this->parsedData['transaction_date'] ?? now()->toDateString(),
            'notes' => $this->parsedData['notes'] ?? 'Impor Penjualan Digital',
        ];
    }

    /**
     * Memperbarui nominal total secara reaktif dari form review.
     */
    public function updateParsedAmount(mixed $amount): void
    {
        $clean = TransactionJsonSchema::sanitizeNumeric($amount);
        $this->parsedData['total_amount'] = $clean;
        $this->parsedData['net_amount'] = $clean;
        $this->syncDraftForValidation();
    }

    /**
     * Memperbarui tanggal transaksi secara reaktif dari form review.
     */
    public function updateParsedDate(string $date): void
    {
        $this->parsedData['transaction_date'] = $date;
        $this->syncDraftForValidation();
    }

    /**
     * Simpan transaksi penjualan digital ke database kas masuk (DoD KV-037).
     */
    public function saveTransaction(AttachmentService $attachmentService)
    {
        $business = auth()->user()?->businesses()->first();

        if (!$business) {
            session()->flash('message', 'Silakan daftarkan profil usaha Anda terlebih dahulu.');
            return redirect()->route('onboarding');
        }

        $this->syncDraftForValidation();

        // 1. Validasi baris disetujui
        if (!empty($this->parsedData['rows'])) {
            $hasApproved = false;
            foreach ($this->parsedData['rows'] as $r) {
                if (!empty($r['approved'])) {
                    $hasApproved = true;
                    break;
                }
            }
            if (!$hasApproved) {
                $this->errorMessage = 'Tidak ada baris transaksi yang disetujui. Centang setidaknya satu baris transaksi untuk disimpan.';
                return null;
            }
        }

        $approvedTotal = (float) ($this->parsedData['total_amount'] ?? 0);
        if ($approvedTotal <= 0) {
            $this->errorMessage = 'Nominal total penjualan harus lebih dari Rp 0.';
            return null;
        }

        // 2. Proteksi Duplikasi (Threshold 10 Menit)
        if (!$this->checkDuplicateSafety($business)) {
            return null;
        }

        // 3. Proteksi Atomic Cache Lock (5 detik)
        $lockKey = "import_digital_sales_lock_{$business->id}";
        $lock = Cache::lock($lockKey, 5);

        if (!$lock->get()) {
            return null;
        }

        try {
            $transactionDate = $this->parsedData['transaction_date'] ?? now()->toDateString();
            $platform = $this->parsedData['platform'] ?? 'other';
            $notes = trim((string) ($this->parsedData['notes'] ?? ''));

            if (!$notes) {
                $notes = "Impor Penjualan Digital ({$platform})";
            }

            $tx = DB::transaction(function () use ($business, $approvedTotal, $transactionDate, $notes, $attachmentService) {
                $transaction = $business->transactions()->create([
                    'type' => 'cash_in',
                    'category' => 'penjualan',
                    'amount' => $approvedTotal,
                    'transaction_date' => $transactionDate,
                    'source_type' => 'digital_import',
                    'notes' => $notes,
                    'is_cash_settled' => true,
                ]);

                // Simpan berkas bukti ke tabel attachments jika ada
                if ($this->uploadedFile && $this->uploadedFile instanceof \Illuminate\Http\UploadedFile) {
                    try {
                        $attachmentService->uploadDigitalImport(
                            file: $this->uploadedFile,
                            transactionId: $transaction->id,
                            source: 'digital_import'
                        );
                    } catch (\Throwable $e) {
                        Log::warning('Gagal mengunggah lampiran berkas impor digital', [
                            'transaction_id' => $transaction->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }

                return $transaction;
            });

            session()->flash('message', 'Penjualan digital sebesar Rp ' . number_format($approvedTotal, 0, ',', '.') . ' berhasil dicatat ke kas masuk!');

            return redirect()->route('transactions.index');
        } finally {
            $lock->release();
        }
    }

    /**
     * Format ukuran bita (bytes) ke format yang mudah dibaca manusia.
     */
    protected function formatBytes(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1, ',', '.') . ' MB';
        }

        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 0, ',', '.') . ' KB';
        }

        return $bytes . ' B';
    }

    public function render()
    {
        $business = auth()->user()?->businesses()->first();

        return view('livewire.transactions.import-digital-sales', [
            'business' => $business,
        ]);
    }
}
