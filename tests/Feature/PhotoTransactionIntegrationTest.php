<?php

namespace Tests\Feature;

use App\Livewire\CreateExpensePhotoTransaction;
use App\Models\Business;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class PhotoTransactionIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Business $business;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.groq.api_key' => 'gsk_mock_test_key_12345',
            'services.groq.vision_model' => 'qwen/qwen3.8-27b',
        ]);

        Storage::fake('public');

        $this->user = User::factory()->create();
        $this->business = Business::create([
            'user_id' => $this->user->id,
            'name' => 'Kedai Kopi Nusantara',
            'business_type' => 'F&B Warung Kopi',
            'description' => 'Kedai kopi untuk pengujian foto KasiVo',
        ]);
    }

    public function test_user_can_access_photo_transaction_page(): void
    {
        $response = $this->actingAs($this->user)->get(route('transactions.photo'));

        $response->assertStatus(200);
        $response->assertSee('Catat Pengeluaran via Foto Nota');
        $response->assertSee('Ambil Foto via Kamera');
        $response->assertSee('Pilih dari Galeri');
    }

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get(route('transactions.photo'));

        $response->assertRedirect(route('login'));
    }

    public function test_user_can_upload_receipt_photo_and_extract_draft(): void
    {
        Http::fake([
            'https://api.groq.com/openai/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'type' => 'cash_out',
                                'category' => 'bahan_baku',
                                'total_amount' => 125000,
                                'transaction_date' => '2026-11-12',
                                'merchant_name' => 'Supermarket Segar',
                                'notes' => 'Belanja di Supermarket Segar',
                                'confidence_score' => 0.98,
                                'needs_review' => false,
                                'items' => [
                                    [
                                        'item_name' => 'Beras Rojolele 5kg',
                                        'quantity' => 1,
                                        'unit' => 'karung',
                                        'price_per_unit' => 75000,
                                        'subtotal' => 75000,
                                    ],
                                    [
                                        'item_name' => 'Minyak Goreng 2L',
                                        'quantity' => 1,
                                        'unit' => 'pouch',
                                        'price_per_unit' => 50000,
                                        'subtotal' => 50000,
                                    ],
                                ],
                            ]),
                        ],
                    ],
                ],
            ], 200),
        ]);

        $photo = UploadedFile::fake()->image('nota-belanja.jpg', 600, 800);

        Livewire::actingAs($this->user)
            ->test(CreateExpensePhotoTransaction::class)
            ->assertSet('step', 'upload')
            ->set('photo', $photo)
            ->call('processPhoto')
            ->assertSet('step', 'review')
            ->assertSet('draft.category', 'bahan_baku')
            ->assertSet('draft.amount', 125000)
            ->assertSet('draft.merchant_name', 'Supermarket Segar')
            ->assertSet('draft.items.0.item_name', 'Beras Rojolele 5kg')
            ->assertSee('Beras Rojolele 5kg')
            ->assertSee('Supermarket Segar');
    }

    public function test_flags_needs_review_when_receipt_is_blurry_or_unclear(): void
    {
        Http::fake([
            'https://api.groq.com/openai/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'type' => 'cash_out',
                                'category' => 'bahan_baku',
                                'total_amount' => 60000,
                                'transaction_date' => '2026-11-12',
                                'merchant_name' => 'Toko Kelontong',
                                'confidence_score' => 0.65,
                                'needs_review' => true,
                                'unclear_fields' => ['total_amount'],
                                'review_reason' => 'Foto struk agak buram, angka total kurang jelas.',
                                'items' => [],
                            ]),
                        ],
                    ],
                ],
            ], 200),
        ]);

        $photo = UploadedFile::fake()->image('nota-buram.jpg', 400, 400);

        Livewire::actingAs($this->user)
            ->test(CreateExpensePhotoTransaction::class)
            ->set('photo', $photo)
            ->call('processPhoto')
            ->assertSet('step', 'review')
            ->assertSet('draft.needs_review', true)
            ->assertSee('Perhatian Kualitas Nota')
            ->assertSee('Foto struk agak buram');
    }

    public function test_user_can_save_transaction_and_bind_attachment(): void
    {
        $photo = UploadedFile::fake()->image('bukti-nota.jpg', 600, 800);

        Livewire::actingAs($this->user)
            ->test(CreateExpensePhotoTransaction::class)
            ->set('step', 'review')
            ->set('photo', $photo)
            ->set('draft', [
                'category' => 'bahan_baku',
                'amount' => 125000,
                'transaction_date' => now()->toDateString(),
                'merchant_name' => 'Supermarket Segar',
                'notes' => 'Belanja mingguan bahan dapur',
                'confidence_score' => 0.98,
                'needs_review' => false,
                'unclear_fields' => [],
                'review_reason' => null,
                'items' => [
                    [
                        'item_name' => 'Beras Rojolele 5kg',
                        'quantity' => 1,
                        'unit' => 'karung',
                        'unit_price' => 75000,
                        'subtotal' => 75000,
                    ],
                ],
            ])
            ->call('saveTransaction')
            ->assertRedirect(route('transactions.index'));

        // Cek Transaksi Tersimpan
        $this->assertDatabaseHas('transactions', [
            'business_id' => $this->business->id,
            'type' => 'cash_out',
            'category' => 'bahan_baku',
            'amount' => 125000,
            'source_type' => 'photo',
            'is_cash_settled' => true,
        ]);

        $transaction = Transaction::latest('id')->first();
        $this->assertNotNull($transaction);

        // Cek Attachment Bukti Foto Terikat ke Transaksi
        $this->assertDatabaseHas('attachments', [
            'transaction_id' => $transaction->id,
            'source' => 'camera_nota',
        ]);

        $this->assertDatabaseHas('transaction_items', [
            'transaction_id' => $transaction->id,
            'item_name' => 'Beras Rojolele 5kg',
            'subtotal' => 75000,
        ]);
    }

    public function test_validates_image_file_type_and_size(): void
    {
        // Berkas teks bukan gambar
        $textDoc = UploadedFile::fake()->create('dokumen.txt', 100, 'text/plain');

        Livewire::actingAs($this->user)
            ->test(CreateExpensePhotoTransaction::class)
            ->set('photo', $textDoc)
            ->assertHasErrors(['photo' => 'image']);

        // Berkas melebihi 5 MB (misal 6000 KB)
        $largeImage = UploadedFile::fake()->image('gambar-raksasa.jpg')->size(6000);

        Livewire::actingAs($this->user)
            ->test(CreateExpensePhotoTransaction::class)
            ->set('photo', $largeImage)
            ->assertHasErrors(['photo' => 'max']);
    }
}

