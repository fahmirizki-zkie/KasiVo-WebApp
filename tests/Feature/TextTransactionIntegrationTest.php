<?php

namespace Tests\Feature;

use App\Livewire\CreateExpenseTextTransaction;
use App\Models\Business;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class TextTransactionIntegrationTest extends TestCase
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
            'description' => 'Kedai kopi untuk pengujian teks KasiVo',
        ]);
    }

    public function test_user_can_access_text_transaction_page(): void
    {
        $response = $this->actingAs($this->user)->get(route('transactions.text'));

        $response->assertStatus(200);
        $response->assertSee('Catat Transaksi via Teks Bebas');
        $response->assertSee('Tuliskan Catatan Transaksi');
        $response->assertSee('Ekstrak Catatan dengan AI');
    }

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get(route('transactions.text'));

        $response->assertRedirect(route('login'));
    }

    public function test_user_can_submit_text_and_extract_expense_draft(): void
    {
        Http::fake([
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
                                'notes' => 'Beli telur ayam 2kg dan minyak goreng 2L',
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

        Livewire::actingAs($this->user)
            ->test(CreateExpenseTextTransaction::class)
            ->assertSet('step', 'input')
            ->set('rawText', 'Tadi sore beli telur ayam 2kg 56rb sama minyak goreng 2L 35rb di Toko Berkah')
            ->call('processText')
            ->assertSet('step', 'review')
            ->assertSet('draft.type', 'cash_out')
            ->assertSet('draft.category', 'bahan_baku')
            ->assertSet('draft.amount', 91000)
            ->assertSet('draft.items.0.item_name', 'Telur Ayam')
            ->assertSet('draft.items.1.item_name', 'Minyak Goreng')
            ->assertSee('Telur Ayam')
            ->assertSee('Minyak Goreng');
    }

    public function test_user_can_submit_text_and_extract_income_draft(): void
    {
        Http::fake([
            'https://api.groq.com/openai/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'type' => 'cash_in',
                                'category' => 'penjualan',
                                'total_amount' => 850000,
                                'transaction_date' => '2026-11-12',
                                'notes' => 'Alhamdulillah omzet penjualan warung hari ini tembus 850rb',
                                'confidence_score' => 0.99,
                                'needs_review' => false,
                                'items' => [
                                    [
                                        'item_name' => 'Omzet Penjualan Harian',
                                        'quantity' => 1,
                                        'unit' => 'hari',
                                        'price_per_unit' => 850000,
                                        'subtotal' => 850000,
                                    ],
                                ],
                            ]),
                        ],
                    ],
                ],
            ], 200),
        ]);

        Livewire::actingAs($this->user)
            ->test(CreateExpenseTextTransaction::class)
            ->set('rawText', 'Alhamdulillah omzet penjualan warung hari ini tembus 850rb')
            ->call('processText')
            ->assertSet('step', 'review')
            ->assertSet('draft.type', 'cash_in')
            ->assertSet('draft.category', 'penjualan')
            ->assertSet('draft.amount', 850000);
    }

    public function test_user_can_save_transaction_to_database(): void
    {
        Livewire::actingAs($this->user)
            ->test(CreateExpenseTextTransaction::class)
            ->set('step', 'review')
            ->set('rawText', 'Beli gas elpiji 3kg 2 tabung total 44rb')
            ->set('draft', [
                'type' => 'cash_out',
                'category' => 'operasional',
                'amount' => 44000,
                'transaction_date' => now()->toDateString(),
                'merchant_name' => 'Pangkalan Gas',
                'notes' => 'Beli gas elpiji 3kg 2 tabung',
                'confidence_score' => 0.98,
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
            'source_type' => 'text',
            'is_cash_settled' => true,
        ]);

        $this->assertDatabaseHas('transaction_items', [
            'item_name' => 'Gas Elpiji 3kg',
            'quantity' => 2,
            'unit_price' => 22000,
            'subtotal' => 44000,
        ]);
    }

    public function test_validates_empty_or_too_short_text(): void
    {
        Livewire::actingAs($this->user)
            ->test(CreateExpenseTextTransaction::class)
            ->set('rawText', 'halo')
            ->call('processText')
            ->assertHasErrors(['rawText' => 'min'])
            ->assertSet('step', 'input');
    }

    public function test_can_apply_template_chip(): void
    {
        Livewire::actingAs($this->user)
            ->test(CreateExpenseTextTransaction::class)
            ->call('applyTemplate', 'Beli gas elpiji 3kg 2 tabung total 44rb tadi pagi')
            ->assertSet('rawText', 'Beli gas elpiji 3kg 2 tabung total 44rb tadi pagi');
    }
}

