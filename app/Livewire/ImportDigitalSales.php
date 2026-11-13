<?php

namespace App\Livewire;

use App\Exceptions\AiQuotaExceededException;
use App\Models\Business;
use App\Services\Ai\TransactionJsonSchema;
use App\Services\AttachmentService;
use App\Services\DigitalSalesParserService;
use InvalidArgumentException;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class ImportDigitalSales extends Component
{
    use WithFileUploads;

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
        $this->reset(['uploadedFile', 'fileMeta', 'fileType', 'errorMessage', 'parsedData']);
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

            // Transisi ke tahap pratinjau hasil ekstraksi (DoD KV-036)
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
                ],
            ],
        ];

        $this->errorMessage = null;
        $this->errorType = 'general';
        $this->step = 'review';
    }

    /**
     * Memperbarui nominal total secara reaktif dari form review.
     */
    public function updateParsedAmount(mixed $amount): void
    {
        $clean = TransactionJsonSchema::sanitizeNumeric($amount);
        $this->parsedData['total_amount'] = $clean;
        $this->parsedData['net_amount'] = $clean;
    }

    /**
     * Memperbarui tanggal transaksi secara reaktif dari form review.
     */
    public function updateParsedDate(string $date): void
    {
        $this->parsedData['transaction_date'] = $date;
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
