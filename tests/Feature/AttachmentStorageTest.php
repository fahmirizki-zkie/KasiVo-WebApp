<?php

namespace Tests\Feature;

use App\Models\Attachment;
use App\Services\AttachmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Tests\TestCase;

class AttachmentStorageTest extends TestCase
{
    use RefreshDatabase;

    private AttachmentService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AttachmentService();
        Storage::fake('public');
    }

    public function test_can_upload_receipt_image_successfully(): void
    {
        $file = UploadedFile::fake()->image('nota_belanja.jpg', 600, 800)->size(500);

        $attachment = $this->service->upload($file, null, 'camera_nota');

        $this->assertInstanceOf(Attachment::class, $attachment);
        $this->assertDatabaseHas('attachments', [
            'id' => $attachment->id,
            'file_name' => 'nota_belanja.jpg',
            'source' => 'camera_nota',
        ]);

        Storage::disk('public')->assertExists($attachment->file_path);
        $this->assertNotEmpty($attachment->url);
        $this->assertStringContainsString('storage/attachments', $attachment->url);
    }

    public function test_can_convert_stored_image_to_base64_for_vision_ai(): void
    {
        $file = UploadedFile::fake()->image('struk_warung.png', 400, 400);

        $attachment = $this->service->upload($file);
        $base64 = $this->service->getBase64($attachment);

        $this->assertNotEmpty($base64);
        $this->assertIsString($base64);
        // Memastikan decode base64 valid
        $this->assertNotFalse(base64_decode($base64, true));
    }

    public function test_rejects_unsupported_file_mime_type(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Format file tidak didukung');

        $invalidFile = UploadedFile::fake()->create('dokumen.pdf', 100, 'application/pdf');

        $this->service->upload($invalidFile);
    }

    public function test_rejects_oversized_file(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Ukuran file melebihi batas maksimum 5 MB');

        // Buat file lebih dari 5MB (misal 6000 KB)
        $oversizedFile = UploadedFile::fake()->image('foto_jumbo.jpg')->size(6000);

        $this->service->upload($oversizedFile);
    }

    public function test_deleting_attachment_record_removes_physical_file_from_storage(): void
    {
        $file = UploadedFile::fake()->image('nota_dihapus.jpg');
        $attachment = $this->service->upload($file);

        $filePath = $attachment->file_path;
        Storage::disk('public')->assertExists($filePath);

        // Hapus record via model
        $attachment->delete();

        // Verifikasi database bersih dan file fisik terhapus otomatis dari disk
        $this->assertDatabaseMissing('attachments', ['id' => $attachment->id]);
        Storage::disk('public')->assertMissing($filePath);
    }
}

