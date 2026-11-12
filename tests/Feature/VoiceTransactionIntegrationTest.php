<?php

namespace Tests\Feature;

use App\Livewire\CreateExpenseVoiceTransaction;
use App\Models\Business;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class VoiceTransactionIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Business $business;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.groq.api_key' => 'gsk_mock_test_key_12345',
        ]);

        $this->user = User::factory()->create();
        $this->business = Business::create([
            'user_id' => $this->user->id,
            'name' => 'Kedai Kopi Nusantara',
            'business_type' => 'F&B Warung Kopi',
            'description' => 'Kedai kopi untuk pengujian suara KasiVo',
        ]);
    }

    public function test_user_can_process_audio_and_transition_to_review_step(): void
    {
        Http::fake([
            'https://api.groq.com/openai/v1/audio/transcriptions' => Http::response([
                'text' => 'Beli telur ayam 2kg 56rb sama minyak goreng 2 liter 35rb di Toko Berkah',
            ], 200),
            'https://api.groq.com/openai/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'type' => 'cash_out',
                                'category' => 'bahan_baku',
                                'total_amount' => 91000,
                                'transaction_date' => '2026-11-12',
                                'merchant_name' => 'Toko Berkah',
                                'notes' => 'Beli telur dan minyak',
                                'confidence_score' => 0.98,
                                'needs_review' => false,
                                'items' => [
                                    [
                                        'item_name' => 'Telur Ayam',
                                        'quantity' => 2,
                                        'unit' => 'kg',
                                        'price_per_unit' => 28000,
                                        'subtotal' => 56000,
                                    ],
                                    [
                                        'item_name' => 'Minyak Goreng',
                                        'quantity' => 2,
                                        'unit' => 'liter',
                                        'price_per_unit' => 17500,
                                        'subtotal' => 35000,
                                    ],
                                ],
                            ]),
                        ],
                    ],
                ],
            ], 200),
        ]);

        $audioFile = UploadedFile::fake()->create('suara-transaksi.webm', 128, 'audio/webm');

        Livewire::actingAs($this->user)
            ->test(CreateExpenseVoiceTransaction::class)
            ->assertSet('step', 'record')
            ->set('audioFile', $audioFile)
            ->call('processVoice')
            ->assertSet('step', 'review')
            ->assertSet('transcriptionText', 'Beli telur ayam 2kg 56rb sama minyak goreng 2 liter 35rb di Toko Berkah')
            ->assertSet('draft.items.0.item_name', 'Telur Ayam')
            ->assertSet('draft.items.1.item_name', 'Minyak Goreng')
            ->assertSet('draft.amount', 91000)
            ->assertSee('Telur Ayam')
            ->assertSee('Minyak Goreng');
    }

    public function test_user_can_save_transaction_from_voice_draft(): void
    {
        Livewire::actingAs($this->user)
            ->test(CreateExpenseVoiceTransaction::class)
            ->set('step', 'review')
            ->set('transcriptionText', 'Beli gas elpiji 3kg dua tabung 44rb')
            ->set('draft', [
                'category' => 'operasional',
                'amount' => 44000,
                'transaction_date' => now()->toDateString(),
                'merchant_name' => 'Pangkalan Gas',
                'notes' => 'Beli gas elpiji 3kg',
                'confidence_score' => 0.95,
                'needs_review' => false,
                'unclear_fields' => [],
                'review_reason' => null,
                'items' => [
                    [
                        'item_name' => 'Gas Elpiji 3kg',
                        'quantity' => 2,
                        'unit' => 'tabung',
                        'unit_price' => 22000,
                        'subtotal' => 44000,
                    ],
                ],
            ])
            ->call('saveTransaction')
            ->assertRedirect(route('transactions.index'));

        $this->assertDatabaseHas('transactions', [
            'business_id' => $this->business->id,
            'type' => 'cash_out',
            'category' => 'operasional',
            'amount' => 44000,
            'source_type' => 'voice',
            'is_cash_settled' => true,
        ]);

        $this->assertDatabaseHas('transaction_items', [
            'item_name' => 'Gas Elpiji 3kg',
            'quantity' => 2,
            'unit_price' => 22000,
            'subtotal' => 44000,
        ]);
    }

    public function test_handles_unclear_or_silent_audio_gracefully(): void
    {
        Http::fake([
            'https://api.groq.com/openai/v1/audio/transcriptions' => Http::response([
                'text' => '',
            ], 200),
        ]);

        $audioFile = UploadedFile::fake()->create('silent.webm', 64, 'audio/webm');

        Livewire::actingAs($this->user)
            ->test(CreateExpenseVoiceTransaction::class)
            ->set('audioFile', $audioFile)
            ->call('processVoice')
            ->assertSet('step', 'record')
            ->assertSee('Suara tidak terdengar jelas');
    }

    public function test_can_add_and_remove_items_in_review_step(): void
    {
        $test = Livewire::actingAs($this->user)
            ->test(CreateExpenseVoiceTransaction::class)
            ->set('step', 'review')
            ->set('draft.items', [
                [
                    'item_name' => 'Gula Pasir 1kg',
                    'quantity' => 1,
                    'unit' => 'kg',
                    'unit_price' => 18000,
                    'subtotal' => 18000,
                ],
            ])
            ->set('draft.amount', 18000);

        // Add item
        $test->call('addItem')
            ->assertCount('draft.items', 2);

        // Remove item
        $test->call('removeItem', 0)
            ->assertCount('draft.items', 1);
    }
}
