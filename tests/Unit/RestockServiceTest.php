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

class RestockServiceTest extends TestCase
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

    /**
     * Uji agregasi frekuensi, kuantitas total, unit dominan, dan tanggal pembelian (DoD KV-045).
     */
    public function test_aggregates_item_purchase_frequency_and_quantity_correctly(): void
    {
        // 1. Pembelian Beras ke-1: 1 Nov 2026, 5kg @ 15.000 = 75.000
        $tx1 = Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_out',
            'category' => 'bahan_baku',
            'amount' => 75000,
            'transaction_date' => '2026-11-01',
            'source_type' => 'photo',
            'is_cash_settled' => true,
        ]);
        TransactionItem::create([
            'transaction_id' => $tx1->id,
            'item_name' => 'Beras Rojolele',
            'quantity' => 5,
            'unit' => 'kg',
            'unit_price' => 15000,
            'subtotal' => 75000,
        ]);

        // 2. Pembelian Beras ke-2: 4 Nov 2026, 5kg @ 15.000 = 75.000
        $tx2 = Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_out',
            'category' => 'bahan_baku',
            'amount' => 75000,
            'transaction_date' => '2026-11-04',
            'source_type' => 'photo',
            'is_cash_settled' => true,
        ]);
        TransactionItem::create([
            'transaction_id' => $tx2->id,
            'item_name' => 'Beras Rojolele',
            'quantity' => 5,
            'unit' => 'kg',
            'unit_price' => 15000,
            'subtotal' => 75000,
        ]);

        // 3. Pembelian Beras ke-3: 9 Nov 2026, 10kg @ 15.000 = 150.000
        $tx3 = Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_out',
            'category' => 'bahan_baku',
            'amount' => 150000,
            'transaction_date' => '2026-11-09',
            'source_type' => 'text',
            'is_cash_settled' => true,
        ]);
        TransactionItem::create([
            'transaction_id' => $tx3->id,
            'item_name' => 'Beras Rojolele',
            'quantity' => 10,
            'unit' => 'kg',
            'unit_price' => 15000,
            'subtotal' => 150000,
        ]);

        $history = $this->service->getItemPurchaseHistory($this->business);

        $this->assertCount(1, $history);

        $beras = $history[0];
        $this->assertEquals('Beras Rojolele', $beras['item_name']);
        $this->assertEquals(3, $beras['purchase_count']);
        $this->assertEquals(20.0, $beras['total_quantity']);
        $this->assertEquals('kg', $beras['unit']);
        $this->assertEquals(300000.0, $beras['total_spent']);
        $this->assertEquals('Rp 300.000', $beras['formatted_total_spent']);
        $this->assertEquals(15000.0, $beras['average_unit_price']);
        $this->assertEquals('2026-11-01', $beras['first_purchase_date']);
        $this->assertEquals('2026-11-09', $beras['last_purchase_date']);
        $this->assertEquals(['2026-11-01', '2026-11-04', '2026-11-09'], $beras['purchase_dates']);
        // Interval: 1 ke 4 (3 hari), 4 ke 9 (5 hari) -> Rata-rata interval = (3+5)/2 = 4.0 hari
        $this->assertEquals(4.0, $beras['average_interval_days']);
    }

    /**
     * Uji normalisasi nama item (case-insensitive & pembersihan whitespace).
     */
    public function test_case_insensitive_and_trimmed_item_name_normalization(): void
    {
        $tx = Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_out',
            'category' => 'bahan_baku',
            'amount' => 90000,
            'transaction_date' => '2026-11-05',
            'source_type' => 'text',
            'is_cash_settled' => true,
        ]);

        // 3 baris item dengan variasi penulisan huruf besar/kecil & spasi
        TransactionItem::create([
            'transaction_id' => $tx->id,
            'item_name' => 'kopi arabika gayo',
            'quantity' => 1,
            'unit' => 'kg',
            'unit_price' => 30000,
            'subtotal' => 30000,
        ]);
        TransactionItem::create([
            'transaction_id' => $tx->id,
            'item_name' => '  Kopi Arabika Gayo  ',
            'quantity' => 1,
            'unit' => 'kg',
            'unit_price' => 30000,
            'subtotal' => 30000,
        ]);
        TransactionItem::create([
            'transaction_id' => $tx->id,
            'item_name' => 'KOPI ARABIKA GAYO',
            'quantity' => 1,
            'unit' => 'kg',
            'unit_price' => 30000,
            'subtotal' => 30000,
        ]);

        $history = $this->service->getItemPurchaseHistory($this->business);

        // Teragregasi menjadi 1 item tunggal yang rapi
        $this->assertCount(1, $history);
        $this->assertEquals('Kopi Arabika Gayo', $history[0]['item_name']);
        $this->assertEquals(3.0, $history[0]['total_quantity']);
        $this->assertEquals(90000.0, $history[0]['total_spent']);
    }

    /**
     * Uji isolasi data: Transaksi penjualan dan transaksi bisnis lain tidak ikut masuk agregasi.
     */
    public function test_excludes_income_transactions_and_other_businesses(): void
    {
        $otherUser = User::factory()->create();
        $otherBusiness = Business::create([
            'user_id' => $otherUser->id,
            'name' => 'Kedai Sebelah',
            'business_type' => 'food_beverage',
        ]);

        // 1. Transaksi belanja bisnis lain
        $otherTx = Transaction::create([
            'business_id' => $otherBusiness->id,
            'type' => 'cash_out',
            'category' => 'bahan_baku',
            'amount' => 500000,
            'transaction_date' => '2026-11-05',
            'source_type' => 'text',
        ]);
        TransactionItem::create([
            'transaction_id' => $otherTx->id,
            'item_name' => 'Daging Sapi Premium',
            'quantity' => 5,
            'unit' => 'kg',
            'unit_price' => 100000,
            'subtotal' => 500000,
        ]);

        // 2. Transaksi bisnis sendiri
        $myTx = Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_out',
            'category' => 'bahan_baku',
            'amount' => 50000,
            'transaction_date' => '2026-11-05',
            'source_type' => 'text',
        ]);
        TransactionItem::create([
            'transaction_id' => $myTx->id,
            'item_name' => 'Susu Segar',
            'quantity' => 2,
            'unit' => 'liter',
            'unit_price' => 25000,
            'subtotal' => 50000,
        ]);

        $history = $this->service->getItemPurchaseHistory($this->business);

        $this->assertCount(1, $history);
        $this->assertEquals('Susu Segar', $history[0]['item_name']);
    }

    /**
     * Uji perhitungan rentang waktu histori usaha (getBusinessHistorySpan).
     */
    public function test_business_history_span_calculation(): void
    {
        // Transaksi belanja pertama 10 hari lalu
        Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_out',
            'category' => 'bahan_baku',
            'amount' => 100000,
            'transaction_date' => Carbon::today()->subDays(10)->toDateString(),
            'source_type' => 'text',
        ]);

        // Transaksi belanja kedua 3 hari lalu
        Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_out',
            'category' => 'bahan_baku',
            'amount' => 150000,
            'transaction_date' => Carbon::today()->subDays(3)->toDateString(),
            'source_type' => 'text',
        ]);

        $span = $this->service->getBusinessHistorySpan($this->business);

        $this->assertTrue($span['has_history']);
        $this->assertEquals(Carbon::today()->subDays(10)->toDateString(), $span['earliest_date']);
        $this->assertEquals(Carbon::today()->subDays(3)->toDateString(), $span['latest_date']);
        $this->assertGreaterThanOrEqual(10, $span['span_days']);
        $this->assertEquals(2, $span['unique_transaction_days']);
        // Karena rentang >= 7 hari, data dinilai cukup untuk aturan 7 hari (KV-046)
        $this->assertTrue($span['is_sufficient_for_prediction']);
    }

    /**
     * Uji keadaan tanpa data (empty state) pada bisnis yang belum pernah belanja bahan.
     */
    public function test_empty_state_returns_empty_array_gracefully(): void
    {
        $history = $this->service->getItemPurchaseHistory($this->business);
        $this->assertIsArray($history);
        $this->assertEmpty($history);

        $span = $this->service->getBusinessHistorySpan($this->business);
        $this->assertFalse($span['has_history']);
        $this->assertEquals(0, $span['span_days']);
        $this->assertFalse($span['is_sufficient_for_prediction']);
    }
}

