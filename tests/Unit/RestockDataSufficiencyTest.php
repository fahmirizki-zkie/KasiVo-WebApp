<?php

namespace Tests\Unit;

use App\Models\Business;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use App\Services\RestockService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RestockDataSufficiencyTest extends TestCase
{
    use RefreshDatabase;

    private RestockService $service;
    private Business $business;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new RestockService();

        $this->user = User::factory()->create();
        $this->business = Business::create([
            'user_id' => $this->user->id,
            'name' => 'Waroeng Restock KasiVo',
            'business_type' => 'food_beverage',
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /**
     * Uji histori < 7 hari menghasilkan status 'insufficient_data' dan remaining_days akurat.
     */
    public function test_business_with_less_than_seven_days_is_marked_as_insufficient_data(): void
    {
        Carbon::setTestNow('2026-11-03 10:00:00');

        // Transaksi tanggal 1 Nov 2026 dan 3 Nov 2026 (rentang = 3 hari: 1, 2, 3)
        $tx1 = Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_out',
            'category' => 'bahan_baku',
            'amount' => 50000,
            'transaction_date' => '2026-11-01',
            'source_type' => 'text',
            'is_cash_settled' => true,
        ]);
        TransactionItem::create([
            'transaction_id' => $tx1->id,
            'item_name' => 'Beras Rojolele',
            'quantity' => 5,
            'unit' => 'kg',
            'unit_price' => 10000,
            'subtotal' => 50000,
        ]);

        $tx2 = Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_out',
            'category' => 'bahan_baku',
            'amount' => 50000,
            'transaction_date' => '2026-11-03',
            'source_type' => 'text',
            'is_cash_settled' => true,
        ]);
        TransactionItem::create([
            'transaction_id' => $tx2->id,
            'item_name' => 'Beras Rojolele',
            'quantity' => 5,
            'unit' => 'kg',
            'unit_price' => 10000,
            'subtotal' => 50000,
        ]);

        $result = $this->service->evaluateDataSufficiency($this->business);

        $this->assertFalse($result['is_sufficient']);
        $this->assertEquals('insufficient_data', $result['status']);
        $this->assertEquals('Data Belum Cukup', $result['status_label']);
        $this->assertEquals(7, $result['threshold_days']);
        $this->assertEquals(3, $result['current_span_days']);
        $this->assertEquals(4, $result['remaining_days']); // 7 - 3 = 4
        $this->assertStringContainsString('sisa 4 hari lagi', $result['guidance_message']);
        $this->assertEquals('2026-11-03', $result['last_purchase_date']);
    }

    /**
     * Uji histori >= 7 hari menghasilkan status 'sufficient' dan remaining_days 0.
     */
    public function test_business_with_seven_or_more_days_is_marked_as_sufficient(): void
    {
        Carbon::setTestNow('2026-11-08 10:00:00');

        // Transaksi tanggal 1 Nov 2026 dan 8 Nov 2026 (rentang = 8 hari: 1 s.d. 8)
        $tx1 = Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_out',
            'category' => 'bahan_baku',
            'amount' => 50000,
            'transaction_date' => '2026-11-01',
            'source_type' => 'text',
            'is_cash_settled' => true,
        ]);
        TransactionItem::create([
            'transaction_id' => $tx1->id,
            'item_name' => 'Beras Rojolele',
            'quantity' => 5,
            'unit' => 'kg',
            'unit_price' => 10000,
            'subtotal' => 50000,
        ]);

        $tx2 = Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_out',
            'category' => 'bahan_baku',
            'amount' => 50000,
            'transaction_date' => '2026-11-08',
            'source_type' => 'text',
            'is_cash_settled' => true,
        ]);
        TransactionItem::create([
            'transaction_id' => $tx2->id,
            'item_name' => 'Beras Rojolele',
            'quantity' => 5,
            'unit' => 'kg',
            'unit_price' => 10000,
            'subtotal' => 50000,
        ]);

        $result = $this->service->evaluateDataSufficiency($this->business);

        $this->assertTrue($result['is_sufficient']);
        $this->assertEquals('sufficient', $result['status']);
        $this->assertEquals('Data Cukup untuk Analisis', $result['status_label']);
        $this->assertEquals(7, $result['threshold_days']);
        $this->assertEquals(8, $result['current_span_days']);
        $this->assertEquals(0, $result['remaining_days']);
        $this->assertStringContainsString('Data memadai untuk memprediksi pola belanja', $result['guidance_message']);
    }

    /**
     * Uji disclaimer eksplisit: tidak mengklaim menghitung stok aktual fisik gudang (DoD KV-046).
     */
    public function test_disclaimer_explicitly_does_not_claim_actual_stock(): void
    {
        $this->assertStringContainsString('BUKAN melacak atau mengklaim jumlah fisik sisa stok aktual', RestockService::DISCLAIMER);
        $this->assertStringContainsString('pola interval pembelian rutin', RestockService::DISCLAIMER);

        $sufficiency = $this->service->evaluateDataSufficiency($this->business);
        $this->assertArrayHasKey('disclaimer', $sufficiency);
        $this->assertEquals(RestockService::DISCLAIMER, $sufficiency['disclaimer']);

        $overview = $this->service->getRestockOverview($this->business);
        $this->assertArrayHasKey('disclaimer', $overview);
        $this->assertEquals(RestockService::DISCLAIMER, $overview['disclaimer']);
    }

    /**
     * Uji evaluasi tingkat item: item dengan < 2 pembelian berstatus 'insufficient_data'.
     */
    public function test_item_level_sufficiency_requires_at_least_two_purchases(): void
    {
        Carbon::setTestNow('2026-11-08 10:00:00');

        // 1. Kasus bisnis sudah cukup (span 8 hari: 1 Nov - 8 Nov)
        $tx1 = Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_out',
            'category' => 'bahan_baku',
            'amount' => 100000,
            'transaction_date' => '2026-11-01',
            'source_type' => 'text',
            'is_cash_settled' => true,
        ]);
        // Beras dibeli di tx1
        TransactionItem::create([
            'transaction_id' => $tx1->id,
            'item_name' => 'Beras Rojolele',
            'quantity' => 5,
            'unit' => 'kg',
            'unit_price' => 10000,
            'subtotal' => 50000,
        ]);
        // Minyak hanya dibeli di tx1 (1 tanggal saja)
        TransactionItem::create([
            'transaction_id' => $tx1->id,
            'item_name' => 'Minyak Goreng',
            'quantity' => 2,
            'unit' => 'liter',
            'unit_price' => 25000,
            'subtotal' => 50000,
        ]);

        $tx2 = Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_out',
            'category' => 'bahan_baku',
            'amount' => 50000,
            'transaction_date' => '2026-11-08',
            'source_type' => 'text',
            'is_cash_settled' => true,
        ]);
        // Beras dibeli lagi di tx2 (total 2 tanggal)
        TransactionItem::create([
            'transaction_id' => $tx2->id,
            'item_name' => 'Beras Rojolele',
            'quantity' => 5,
            'unit' => 'kg',
            'unit_price' => 10000,
            'subtotal' => 50000,
        ]);

        $overview = $this->service->getRestockOverview($this->business);

        $this->assertTrue($overview['sufficiency']['is_sufficient']);
        $this->assertEquals(2, $overview['total_items_count']);
        $this->assertEquals(1, $overview['ready_items_count']); // Beras
        $this->assertCount(1, $overview['ready_items']);
        $this->assertCount(1, $overview['pending_items']); // Minyak

        $beras = $overview['ready_items'][0];
        $this->assertEquals('Beras Rojolele', $beras['item_name']);
        $this->assertTrue($beras['sufficiency']['is_sufficient']);
        $this->assertEquals('sufficient', $beras['sufficiency']['status']);

        $minyak = $overview['pending_items'][0];
        $this->assertEquals('Minyak Goreng', $minyak['item_name']);
        $this->assertFalse($minyak['sufficiency']['is_sufficient']);
        $this->assertEquals('insufficient_data', $minyak['sufficiency']['status']);
        $this->assertStringContainsString('minimal 2 kali pembelian', $minyak['sufficiency']['reason']);
    }

    /**
     * Uji timestamp last_updated_at dan format tanggal update tersedia dan akurat.
     */
    public function test_last_updated_timestamp_and_date_are_present_and_accurate(): void
    {
        Carbon::setTestNow('2026-11-14 15:30:00');

        $tx = Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_out',
            'category' => 'bahan_baku',
            'amount' => 50000,
            'transaction_date' => '2026-11-10',
            'source_type' => 'text',
            'is_cash_settled' => true,
        ]);
        TransactionItem::create([
            'transaction_id' => $tx->id,
            'item_name' => 'Tepung Terigu',
            'quantity' => 2,
            'unit' => 'kg',
            'unit_price' => 25000,
            'subtotal' => 50000,
        ]);

        $result = $this->service->evaluateDataSufficiency($this->business);

        $this->assertEquals('2026-11-14T15:30:00+00:00', Carbon::parse($result['last_updated_at'])->toIso8601String());
        $this->assertStringContainsString('14 November 2026', $result['formatted_last_updated']);
        $this->assertStringContainsString('WIB', $result['formatted_last_updated']);
        $this->assertEquals('2026-11-10', $result['last_purchase_date']);
    }

    /**
     * Uji keadaan tanpa transaksi: status insufficient, span 0 hari, remaining 7 hari.
     */
    public function test_empty_state_returns_insufficient_with_seven_days_remaining(): void
    {
        $result = $this->service->evaluateDataSufficiency($this->business);

        $this->assertFalse($result['is_sufficient']);
        $this->assertEquals('insufficient_data', $result['status']);
        $this->assertEquals(0, $result['current_span_days']);
        $this->assertEquals(7, $result['remaining_days']);
        $this->assertNull($result['last_purchase_date']);
        $this->assertStringContainsString('Belum ada riwayat pembelian bahan baku', $result['guidance_message']);

        $overview = $this->service->getRestockOverview($this->business);
        $this->assertCount(0, $overview['items']);
        $this->assertEquals(0, $overview['total_items_count']);
        $this->assertEquals(0, $overview['ready_items_count']);
    }
}
