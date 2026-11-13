<?php

namespace App\Livewire;

use App\Models\Business;
use App\Services\AttachmentService;
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
     * Pesan kesalahan jika format ditolak atau ukuran melebihi batas.
     */
    public ?string $errorMessage = null;

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
        $this->reset(['uploadedFile', 'fileMeta', 'fileType', 'errorMessage']);
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
     * Lanjut ke tahap ekstraksi/parsing data impor (KV-036).
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

