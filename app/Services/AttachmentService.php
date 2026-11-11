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
     * Batas ukuran maksimum file: 5 MB (dalam bytes).
     */
    public const MAX_FILE_SIZE_BYTES = 5 * 1024 * 1024;

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

