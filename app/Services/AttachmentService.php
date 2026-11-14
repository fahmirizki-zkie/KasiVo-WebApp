<?php

namespace App\Services;

use App\Models\Attachment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

class AttachmentService
{
    /**
     * Tipe MIME gambar yang diizinkan untuk bukti transaksi.
     */
    public const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/webp',
    ];

    /**
     * Tipe MIME gambar screenshot yang diizinkan untuk impor penjualan digital.
     */
    public const DIGITAL_IMPORT_IMAGE_MIMES = [
        'image/jpeg',
        'image/png',
        'image/webp',
    ];

    /**
     * Tipe MIME dokumen spreadsheet/tabular yang diizinkan untuk impor penjualan digital.
     */
    public const DIGITAL_IMPORT_DOC_MIMES = [
        'text/csv',
        'text/plain',
        'application/csv',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-excel',
    ];

    /**
     * Ekstensi berkas yang diizinkan untuk impor penjualan digital.
     */
    public const DIGITAL_IMPORT_ALLOWED_EXTENSIONS = [
        'jpg', 'jpeg', 'png', 'webp', 'csv', 'xlsx', 'xls',
    ];

    /**
     * Batas ukuran maksimum file: 5 MB (dalam bytes).
     */
    public const MAX_FILE_SIZE_BYTES = 5 * 1024 * 1024;

    /**
     * Batas ukuran maksimum dokumen impor digital: 10 MB (dalam bytes).
     */
    public const MAX_DOC_FILE_SIZE_BYTES = 10 * 1024 * 1024;

    /**
     * Upload dan simpan file bukti transaksi ke storage dan database.
     *
     * @param UploadedFile $file File foto yang diunggah
     * @param int|null $transactionId ID transaksi (opsional, bisa dihubungkan kemudian)
     * @param string $source Sumber foto ('camera' atau 'gallery')
     * @return Attachment
     * 
     * @throws InvalidArgumentException jika format atau ukuran file tidak valid
     */
    public function upload(
        UploadedFile $file,
        ?int $transactionId = null,
        string $source = 'camera_nota'
    ): Attachment {
        $this->validateFile($file);

        // Buat struktur direktori per tahun/bulan: attachments/2026/11/
        $folderPath = 'attachments/' . date('Y/m');

        // Buat nama file unik dan aman untuk mencegah collision
        $extension = $file->getClientOriginalExtension() ?: 'jpg';
        $uniqueFileName = Str::random(32) . '.' . strtolower($extension);

        // Simpan file ke disk 'public'
        $storedPath = $file->storeAs($folderPath, $uniqueFileName, 'public');

        if (!$storedPath) {
            throw new \RuntimeException('Gagal menyimpan file bukti foto ke storage disk.');
        }

        // Catat metadata ke database
        return Attachment::create([
            'transaction_id' => $transactionId,
            'file_path' => $storedPath,
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType() ?: 'image/jpeg',
            'file_size_bytes' => $file->getSize(),
            'source' => $source,
        ]);
    }

    /**
     * Upload dan simpan berkas bukti impor penjualan digital (screenshot atau spreadsheet CSV/XLSX).
     */
    public function uploadDigitalImport(
        UploadedFile $file,
        ?int $transactionId = null,
        string $source = 'digital_import'
    ): Attachment {
        $detectedType = $this->validateDigitalImportFile($file);

        $folderPath = 'attachments/' . date('Y/m');
        $extension = $file->getClientOriginalExtension() ?: ($detectedType === 'screenshot' ? 'jpg' : 'csv');
        $uniqueFileName = Str::random(32) . '.' . strtolower($extension);

        $storedPath = $file->storeAs($folderPath, $uniqueFileName, 'public');

        if (!$storedPath) {
            throw new \RuntimeException('Gagal menyimpan berkas impor digital ke storage disk.');
        }

        return Attachment::create([
            'transaction_id' => $transactionId,
            'file_path' => $storedPath,
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType() ?: ($detectedType === 'screenshot' ? 'image/jpeg' : 'text/csv'),
            'file_size_bytes' => $file->getSize(),
            'source' => $source,
        ]);
    }

    /**
     * Validasi format dan ukuran file.
     */
    public function validateFile(UploadedFile $file): void
    {
        $mime = $file->getMimeType();
        if (!in_array($mime, self::ALLOWED_MIME_TYPES, true)) {
            throw new InvalidArgumentException(
                "Format file tidak didukung: {$mime}. Gunakan format JPG, PNG, atau WEBP."
            );
        }

        if ($file->getSize() > self::MAX_FILE_SIZE_BYTES) {
            throw new InvalidArgumentException(
                'Ukuran file melebihi batas maksimum 5 MB.'
            );
        }
    }

    /**
     * Validasi berkas impor penjualan digital (tangkapan layar atau dokumen tabular CSV/XLSX).
     *
     * @param UploadedFile $file
     * @return string 'screenshot'|'spreadsheet'
     * @throws InvalidArgumentException jika format atau ukuran berkas tidak didukung
     */
    public function validateDigitalImportFile(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: '');
        $mime = strtolower($file->getMimeType() ?: '');

        $isImageExt = in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true);
        $isDocExt = in_array($extension, ['csv', 'xlsx', 'xls'], true);

        $isImageMime = in_array($mime, self::DIGITAL_IMPORT_IMAGE_MIMES, true);
        $isDocMime = in_array($mime, self::DIGITAL_IMPORT_DOC_MIMES, true);

        // Jika tangkapan layar (screenshot)
        if (($isImageExt || $isImageMime) && !$isDocExt) {
            if ($file->getSize() > self::MAX_FILE_SIZE_BYTES) {
                throw new InvalidArgumentException(
                    'Ukuran tangkapan layar melebihi batas maksimum 5 MB.'
                );
            }
            return 'screenshot';
        }

        // Jika dokumen tabular (CSV / Excel)
        if ($isDocExt || $isDocMime) {
            if ($file->getSize() > self::MAX_DOC_FILE_SIZE_BYTES) {
                throw new InvalidArgumentException(
                    'Ukuran berkas dokumen melebihi batas maksimum 10 MB.'
                );
            }
            return 'spreadsheet';
        }

        $displayExt = $extension ? ".{$extension}" : ($mime ?: 'tidak dikenal');
        throw new InvalidArgumentException(
            "Format berkas '{$displayExt}' tidak didukung. Harap unggah tangkapan layar (.png, .jpg, .webp) atau dokumen data (.csv, .xlsx)."
        );
    }

    /**
     * Ambil isi file foto dalam format Base64 (siap dikirim ke Groq Vision AI).
     */
    public function getBase64(Attachment $attachment): string
    {
        if (!Storage::disk('public')->exists($attachment->file_path)) {
            throw new \RuntimeException("Berkas foto tidak ditemukan pada disk: {$attachment->file_path}");
        }

        $fileContent = Storage::disk('public')->get($attachment->file_path);
        return base64_encode($fileContent);
    }

    /**
     * Hapus berkas attachment (file di disk terhapus otomatis via model event).
     */
    public function delete(Attachment $attachment): bool
    {
        return $attachment->delete();
    }
}

