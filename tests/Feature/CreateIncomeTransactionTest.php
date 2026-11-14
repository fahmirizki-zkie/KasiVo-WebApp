<?php

namespace Tests\Feature;

use App\Livewire\CreateIncomeTransaction;
use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CreateIncomeTransactionTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Business $business;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->business = Business::create([
            'user_id' => $this->user->id,
            'name' => 'Warung Mas Joko',
            'business_type' => 'Warung Makan',
        ]);
    }

    public function test_authenticated_user_can_access_create_income_page(): void
    {
        $response = $this->actingAs($this->user)->get(route('transactions.income'));

        $response->assertStatus(200);
        $response->assertSee('Catat Penjualan Harian');
        $response->assertSee('Total Nominal Penjualan');
    }

    public function test_can_record_daily_sales_successfully(): void
    {
        Livewire::actingAs($this->user)
            ->test(CreateIncomeTransaction::class)
            ->set('amount', 750000)
            ->set('category', 'penjualan_tunai')
            ->set('transaction_date', now()->toDateString())
            ->set('notes', 'Omzet ramai hari minggu')
            ->call('save')
            ->assertRedirect(route('transactions.index'));

        $this->assertDatabaseHas('transactions', [
            'business_id' => $this->business->id,
            'type' => 'cash_in',
            'category' => 'penjualan_tunai',
            'amount' => 750000,
            'notes' => 'Omzet ramai hari minggu',
            'is_cash_settled' => true,
        ]);
    }

    public function test_validation_rejects_empty_or_negative_amount(): void
    {
        Livewire::actingAs($this->user)
            ->test(CreateIncomeTransaction::class)
            ->set('amount', 0)
            ->call('save')
            ->assertHasErrors(['amount' => 'min']);

        Livewire::actingAs($this->user)
            ->test(CreateIncomeTransaction::class)
            ->set('amount', -50000)
            ->call('save')
            ->assertHasErrors(['amount' => 'min']);
    }

    public function test_validation_rejects_future_transaction_date(): void
    {
        $tomorrow = now()->addDays(2)->toDateString();

        Livewire::actingAs($this->user)
            ->test(CreateIncomeTransaction::class)
            ->set('amount', 250000)
            ->set('transaction_date', $tomorrow)
            ->call('save')
            ->assertHasErrors(['transaction_date' => 'before_or_equal']);
    }

    public function test_validation_rejects_invalid_category(): void
    {
        Livewire::actingAs($this->user)
            ->test(CreateIncomeTransaction::class)
            ->set('amount', 250000)
            ->set('category', 'kategori_palsu')
            ->call('save')
            ->assertHasErrors(['category' => 'in']);
    }

    public function test_quick_add_amount_pills_work(): void
    {
        Livewire::actingAs($this->user)
            ->test(CreateIncomeTransaction::class)
            ->call('addAmount', 50000)
            ->assertSet('amount', 50000)
            ->call('addAmount', 100000)
            ->assertSet('amount', 150000)
            ->call('setAmount', 0)
            ->assertSet('amount', 0);
    }

    public function test_extract_photo_ai_uses_gemini_vision_to_parse_income_receipt(): void
    {
        config([
            'services.gemini.api_key' => 'AIzaSyMockKeyForIncomeTest12345',
            'services.gemini.model' => 'gemini-1.5-flash',
        ]);

        \Illuminate\Support\Facades\Http::fake([
            'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent*' => \Illuminate\Support\Facades\Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => json_encode([
                                        'type' => 'cash_in',
                                        'category' => 'penjualan_tunai',
                                        'total_amount' => 450000,
                                        'transaction_date' => '2026-11-14',
                                        'merchant_name' => 'Kedai Kopi Mas Joko',
                                        'notes' => 'Rekap penjualan kasir sore',
                                        'confidence_score' => 0.98,
                                        'needs_review' => false,
                                        'items' => [
                                            [
                                                'item_name' => 'Kopi Susu Gula Aren',
                                                'quantity' => 15,
                                                'unit' => 'cup',
                                                'price_per_unit' => 20000,
                                                'subtotal' => 300000,
                                            ],
                                            [
                                                'item_name' => 'Roti Bakar Coklat',
                                                'quantity' => 10,
                                                'unit' => 'porsi',
                                                'price_per_unit' => 15000,
                                                'subtotal' => 150000,
                                            ],
                                        ],
                                    ]),
                                ],
                            ],
                            'role' => 'model',
                        ],
                    ],
                ],
            ], 200),
        ]);

        $mockBase64 = 'data:image/jpeg;base64,' . base64_encode('fake-sales-receipt');

        $component = Livewire::actingAs($this->user)
            ->test(CreateIncomeTransaction::class)
            ->call('extractPhotoAi', $mockBase64)
            ->assertSet('amount', 450000)
            ->assertSet('category', 'penjualan');

        $result = $component->instance()->extractPhotoAi(
            $mockBase64,
            app(\App\Services\Ai\GeminiService::class),
            app(\App\Services\Ai\GroqService::class)
        );

        $this->assertEquals(450000, $result['amount']);
        $this->assertCount(2, $result['items']);
    }

    public function test_extract_text_ai_uses_groq_nlp_to_parse_income_sentence(): void
    {
        config([
            'services.groq.api_key' => 'gsk_mock_groq_test_123',
        ]);

        \Illuminate\Support\Facades\Http::fake([
            'https://api.groq.com/openai/v1/chat/completions' => \Illuminate\Support\Facades\Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'type' => 'cash_in',
                                'category' => 'penjualan_qris',
                                'total_amount' => 850000,
                                'transaction_date' => '2026-11-14',
                                'notes' => 'Omzet katering makan siang via QRIS',
                                'confidence_score' => 0.95,
                                'needs_review' => false,
                                'items' => [],
                            ]),
                        ],
                    ],
                ],
            ], 200),
        ]);

        Livewire::actingAs($this->user)
            ->test(CreateIncomeTransaction::class)
            ->call('extractTextAi', 'Omzet katering siang 850rb via QRIS')
            ->assertSet('amount', 850000)
            ->assertSet('category', 'penjualan');
    }
}

