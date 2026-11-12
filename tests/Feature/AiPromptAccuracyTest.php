<?php

namespace Tests\Feature;

use App\Livewire\CreateExpenseTextTransaction;
use App\Models\Business;
use App\Models\User;
use App\Services\Ai\GroqService;
use App\Services\Ai\TransactionJsonSchema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;

class AiPromptAccuracyTest extends TestCase
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
            'name' => 'Kedai Kopi & Kuliner KasiVo',
            'business_type' => 'food_beverage',
        ]);
    }

    /**
     * Uji akurasi sanitasi berbagai variasi notasi mata uang & dialek pasar Indonesia.
     */
    public function test_parses_diverse_indonesian_currency_notations(): void
    {
        $testCases = [
            '50rb' => 50000.0,
            '50 rb' => 50000.0,
            '50ribu' => 50000.0,
            '25k' => 25000.0,
            '25 k' => 25000.0,
            '1.5jt' => 1500000.0,
            '1,5 juta' => 1500000.0,
            '2jt' => 2000000.0,
            '3 rebu' => 3000.0,
            '10 rebu' => 10000.0,
            'Rp 125.000,00' => 125000.0,
            'Rp 75.000' => 75000.0,
            '150.000' => 150000.0,
            '500' => 500.0,
        ];

        foreach ($testCases as $input => $expected) {
            $parsed = TransactionJsonSchema::sanitizeNumeric($input);
            $this->assertEquals(
                $expected,
                $parsed,
                "Gagal mem-parsing notasi rupiah [{$input}]. Ekspektasi: {$expected}, Hasil: {$parsed}"
            );
        }
    }

    /**
     * Uji normalisasi skema output AI untuk klasifikasi otomatis cash_in (omzet) vs cash_out (belanja).
     */
    public function test_classifies_income_vs_expense_automatically(): void
    {
        // 1. Kas Masuk (Omzet Penjualan)
        $omzetPayload = [
            'type' => 'cash_in',
            'category' => 'penjualan',
            'total_amount' => '850rb',
            'transaction_date' => now()->toDateString(),
            'notes' => 'Alhamdulillah penjualan warung tembus 850rb',
            'items' => [
                ['item_name' => 'Omzet Harian', 'quantity' => 1, 'unit' => 'pcs', 'price_per_unit' => '850000', 'subtotal' => '850000'],
            ],
        ];

        $normalizedOmzet = TransactionJsonSchema::validateAndNormalize($omzetPayload);
        $this->assertEquals('cash_in', $normalizedOmzet['type']);
        $this->assertEquals('penjualan', $normalizedOmzet['category']);
        $this->assertEquals(850000, $normalizedOmzet['total_amount']);
        $this->assertFalse($normalizedOmzet['needs_review']);

        // 2. Kas Keluar (Bahan Baku)
        $bahanPayload = [
            'type' => 'cash_out',
            'category' => 'bahan_baku',
            'total_amount' => '65k',
            'transaction_date' => now()->toDateString(),
            'notes' => 'Beli ayam potong 2 ekor',
            'items' => [
                ['item_name' => 'Ayam Potong', 'quantity' => 2, 'unit' => 'ekor', 'price_per_unit' => '32500', 'subtotal' => '65000'],
            ],
        ];

        $normalizedBahan = TransactionJsonSchema::validateAndNormalize($bahanPayload);
        $this->assertEquals('cash_out', $normalizedBahan['type']);
        $this->assertEquals('bahan_baku', $normalizedBahan['category']);
        $this->assertEquals(65000, $normalizedBahan['total_amount']);

        // 3. Kas Keluar (Operasional Gas/Listrik)
        $opsPayload = [
            'type' => 'cash_out',
            'category' => 'operasional',
            'total_amount' => '44000',
            'transaction_date' => now()->toDateString(),
            'notes' => 'Beli tabung gas elpiji 3kg 2 tabung',
            'items' => [
                ['item_name' => 'Gas Elpiji 3kg', 'quantity' => 2, 'unit' => 'tabung', 'price_per_unit' => '22000', 'subtotal' => '44000'],
            ],
        ];

        $normalizedOps = TransactionJsonSchema::validateAndNormalize($opsPayload);
        $this->assertEquals('cash_out', $normalizedOps['type']);
        $this->assertEquals('operasional', $normalizedOps['category']);
        $this->assertEquals(44000, $normalizedOps['total_amount']);
    }

    /**
     * Uji rekonsiliasi belanja multi-barang tanpa selisih pembulatan.
     */
    public function test_multi_item_breakdown_and_math_reconciliation(): void
    {
        $multiItemPayload = [
            'type' => 'cash_out',
            'category' => 'bahan_baku',
            'total_amount' => '132rb', // 132.000
            'transaction_date' => now()->toDateString(),
            'merchant_name' => 'Toko Berkah',
            'notes' => 'Beli beras 5kg, telur 1kg, sama minyak 2L',
            'items' => [
                ['item_name' => 'Beras 5kg', 'quantity' => 1, 'unit' => 'karung', 'price_per_unit' => '70000', 'subtotal' => '70000'],
                ['item_name' => 'Telur Ayam', 'quantity' => 1, 'unit' => 'kg', 'price_per_unit' => '28000', 'subtotal' => '28000'],
                ['item_name' => 'Minyak Goreng 2L', 'quantity' => 1, 'unit' => 'pouch', 'price_per_unit' => '34000', 'subtotal' => '34000'],
            ],
        ];

        $normalized = TransactionJsonSchema::validateAndNormalize($multiItemPayload);

        $this->assertEquals(132000, $normalized['total_amount']);
        $this->assertCount(3, $normalized['items']);
        $this->assertFalse($normalized['needs_review']);

        $subtotalSum = array_sum(array_column($normalized['items'], 'subtotal'));
        $this->assertEquals(132000, $subtotalSum);
    }

    /**
     * Uji penanganan aman terhadap edge case: teks tanpa nominal uang (harus ditandai needs_review).
     */
    public function test_flags_text_without_nominal_as_needs_review(): void
    {
        $ambiguousPayload = [
            'type' => 'cash_out',
            'category' => 'bahan_baku',
            'total_amount' => 0,
            'transaction_date' => now()->toDateString(),
            'notes' => 'Kemarin pergi ke pasar belanja sayur mayur',
            'needs_review' => true,
            'unclear_fields' => ['total_amount'],
            'review_reason' => 'Nominal belanja tidak disebutkan dalam catatan.',
            'items' => [],
        ];

        $normalized = TransactionJsonSchema::validateAndNormalize($ambiguousPayload);

        $this->assertTrue($normalized['needs_review']);
        $this->assertEquals(0, $normalized['total_amount']);
        $this->assertContains('total_amount', $normalized['unclear_fields']);
    }

    /**
     * Uji penanganan aman terhadap edge case: foto nota buram / robek.
     */
    public function test_blurry_receipt_detection_sets_warning_flags(): void
    {
        $blurryPayload = [
            'type' => 'cash_out',
            'category' => 'bahan_baku',
            'total_amount' => '45000',
            'transaction_date' => now()->toDateString(),
            'notes' => 'Struk belanjaan basah',
            'confidence_score' => 0.65,
            'needs_review' => true,
            'unclear_fields' => ['total_amount', 'items'],
            'review_reason' => 'Kertas nota kusut dan sebagian angka buram.',
            'items' => [
                ['item_name' => 'Barang Tidak Jelas', 'quantity' => 1, 'unit' => 'pcs', 'price_per_unit' => '45000', 'subtotal' => '45000'],
            ],
        ];

        $normalized = TransactionJsonSchema::validateAndNormalize($blurryPayload);

        $this->assertTrue($normalized['needs_review']);
        $this->assertContains('total_amount', $normalized['unclear_fields']);
        $this->assertContains('items', $normalized['unclear_fields']);
        $this->assertStringContainsString('buram', $normalized['review_reason']);
    }

    /**
     * Uji penanganan diskon / potongan belanja dalam kalimat pedagang.
     */
    public function test_handles_discount_and_price_reductions(): void
    {
        // 120rb diskon 20rb = 100rb bersih
        $discountPayload = [
            'type' => 'cash_out',
            'category' => 'bahan_baku',
            'total_amount' => 100000,
            'transaction_date' => now()->toDateString(),
            'notes' => 'Beli daging sapi 120rb dapat potongan diskon 20rb',
            'items' => [
                ['item_name' => 'Daging Sapi (setelah diskon 20rb)', 'quantity' => 1, 'unit' => 'kg', 'price_per_unit' => 100000, 'subtotal' => 100000],
            ],
        ];

        $normalized = TransactionJsonSchema::validateAndNormalize($discountPayload);

        $this->assertEquals(100000, $normalized['total_amount']);
        $this->assertStringContainsString('diskon', strtolower($normalized['notes']));
    }

    /**
     * Uji simulasi integrasi Livewire: input teks pedagang diproses akurat ke layar review.
     */
    public function test_livewire_text_transaction_end_to_end_accuracy(): void
    {
        $mockGroq = Mockery::mock(GroqService::class);
        $mockGroq->shouldReceive('extractFromText')
            ->once()
            ->withArgs(function ($text) {
                return str_contains($text, 'Beli cup plastik 16oz');
            })
            ->andReturn([
                'type' => 'cash_out',
                'category' => 'bahan_baku',
                'total_amount' => 135000,
                'transaction_date' => now()->toDateString(),
                'merchant_name' => 'Toko Plastik Makmur',
                'notes' => 'Beli cup plastik 16oz 1 dus 120rb sama sedotan 15rb',
                'confidence_score' => 0.98,
                'needs_review' => false,
                'unclear_fields' => [],
                'review_reason' => null,
                'items' => [
                    ['item_name' => 'Cup Plastik 16oz', 'quantity' => 1, 'unit' => 'dus', 'unit_price' => 120000, 'subtotal' => 120000],
                    ['item_name' => 'Sedotan Steril', 'quantity' => 1, 'unit' => 'pack', 'unit_price' => 15000, 'subtotal' => 15000],
                ],
            ]);

        $this->app->instance(GroqService::class, $mockGroq);

        Livewire::actingAs($this->user)
            ->test(CreateExpenseTextTransaction::class)
            ->set('rawText', 'Beli cup plastik 16oz 1 dus 120rb sama sedotan 15rb di Toko Plastik Makmur')
            ->call('processText')
            ->assertSet('step', 'review')
            ->assertSet('draft.amount', 135000)
            ->assertSet('draft.category', 'bahan_baku')
            ->assertSet('draft.merchant_name', 'Toko Plastik Makmur')
            ->assertCount('draft.items', 2)
            ->assertSet('draft.needs_review', false);
    }
}
