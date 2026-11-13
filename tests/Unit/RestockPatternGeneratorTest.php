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

class RestockPatternGeneratorTest extends TestCase
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
            'name' => 'Waroeng Kopi Restock',
            'business_type' => 'food_beverage',
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /**
     * Uji kalkulasi interval antar-pembelian secara matematis (DoD KV-047).
     */
    public function test_calculates_purchase_intervals_accurately(): void
    {
        // 1. Tanggal interval teratur (selisih 4 hari konsisten)
        $datesRegular = ['2026-11-01', '2026-11-05', '2026-11-09', '2026-11-13'];
        $resRegular = $this->service->calculateItemIntervals($datesRegular);

        $this->assertTrue($resRegular['has_intervals']);
        $this->assertEquals([4, 4, 4], $resRegular['intervals']);
        $this->assertEquals(4, $resRegular['average_interval_days']);
        $this->assertEquals(4, $resRegular['min_interval_days']);
        $this->assertEquals(4, $resRegular['max_interval_days']);

        // 2. Tanggal interval bervariasi (selisih 3 hari dan 6 hari -> rata-rata 4.5 -> dibulatkan 5 hari)
        $datesIrregular = ['2026-11-01', '2026-11-04', '2026-11-10'];
        $resIrregular = $this->service->calculateItemIntervals($datesIrregular);

        $this->assertTrue($resIrregular['has_intervals']);
        $this->assertEquals([3, 6], $resIrregular['intervals']);
        $this->assertEquals(5, $resIrregular['average_interval_days']);
        $this->assertEquals(3, $resIrregular['min_interval_days']);
        $this->assertEquals(6, $resIrregular['max_interval_days']);

        // 3. Hanya 1 tanggal atau kosong -> tidak ada interval
        $resSingle = $this->service->calculateItemIntervals(['2026-11-01']);
        $this->assertFalse($resSingle['has_intervals']);
        $this->assertEquals(0, $resSingle['average_interval_days']);
    }

    /**
     * Uji pola belanja 'due_today' (hari ini tepat tiba siklus rutin).
     */
    public function test_identifies_due_today_pattern_correctly(): void
    {
        // Beras dibeli tiap 4 hari: 1 Nov, 5 Nov, 9 Nov
        $this->createRawMaterialTransaction('2026-11-01', 'Beras Rojolele', 5, 'kg', 15000);
        $this->createRawMaterialTransaction('2026-11-05', 'Beras Rojolele', 5, 'kg', 15000);
        $this->createRawMaterialTransaction('2026-11-09', 'Beras Rojolele', 5, 'kg', 15000);

        // Reference date = 13 Nov 2026 (9 Nov + 4 hari = 13 Nov -> Hari ini!)
        $refDate = Carbon::parse('2026-11-13');
        Carbon::setTestNow($refDate);

        $result = $this->service->generateRestockRecommendations($this->business, $refDate);

        $this->assertTrue($result['is_sufficient']);
        $this->assertCount(1, $result['recommendations']);

        $item = $result['recommendations'][0];
        $this->assertEquals('Beras Rojolele', $item['item_name']);
        $this->assertEquals('due_today', $item['urgency']);
        $this->assertEquals('Perlu Restock Hari Ini', $item['urgency_label']);
        $this->assertTrue($item['is_recommended']);
        $this->assertEquals(4, $item['average_interval_days']);
        $this->assertEquals(4, $item['days_since_last_purchase']);
        $this->assertEquals(0, $item['days_until_restock']);
        $this->assertEquals('2026-11-13', $item['estimated_next_purchase_date']);
        $this->assertStringContainsString('rata-rata dibeli setiap 4 hari sekali', $item['pattern_reason']);
        $this->assertStringContainsString('estimasi waktu belanja rutin berikutnya adalah hari ini', $item['pattern_reason']);
    }

    /**
     * Uji pola belanja 'overdue' (sudah melewati siklus rutin).
     */
    public function test_identifies_overdue_pattern_when_past_cycle(): void
    {
        // Beras dibeli tiap 4 hari: 1 Nov, 5 Nov, 9 Nov
        $this->createRawMaterialTransaction('2026-11-01', 'Beras Rojolele', 5, 'kg', 15000);
        $this->createRawMaterialTransaction('2026-11-05', 'Beras Rojolele', 5, 'kg', 15000);
        $this->createRawMaterialTransaction('2026-11-09', 'Beras Rojolele', 5, 'kg', 15000);

        // Reference date = 15 Nov 2026 (sudah 6 hari sejak 9 Nov, padahal siklus 4 hari -> lewat 2 hari!)
        $refDate = Carbon::parse('2026-11-15');
        Carbon::setTestNow($refDate);

        $result = $this->service->generateRestockRecommendations($this->business, $refDate);

        $this->assertTrue($result['is_sufficient']);
        $this->assertCount(1, $result['recommendations']);

        $item = $result['recommendations'][0];
        $this->assertEquals('overdue', $item['urgency']);
        $this->assertEquals('Perlu Segera (Lewat Siklus)', $item['urgency_label']);
        $this->assertTrue($item['is_recommended']);
        $this->assertEquals(6, $item['days_since_last_purchase']);
        $this->assertEquals(-2, $item['days_until_restock']);
        $this->assertStringContainsString('terlewat 2 hari', $item['pattern_reason']);
    }

    /**
     * Uji pola belanja 'upcoming' (1-2 hari lagi) dan 'safe' (> 2 hari lagi).
     */
    public function test_identifies_upcoming_and_safe_patterns(): void
    {
        // Beras dibeli tiap 4 hari: 1 Nov, 5 Nov, 9 Nov
        $this->createRawMaterialTransaction('2026-11-01', 'Beras Rojolele', 5, 'kg', 15000);
        $this->createRawMaterialTransaction('2026-11-05', 'Beras Rojolele', 5, 'kg', 15000);
        $this->createRawMaterialTransaction('2026-11-09', 'Beras Rojolele', 5, 'kg', 15000);

        // 1. Kasus Upcoming (12 Nov: 3 hari sejak 9 Nov -> sisa 1 hari lagi menuju 13 Nov)
        $refUpcoming = Carbon::parse('2026-11-12');
        Carbon::setTestNow($refUpcoming);
        $resUpcoming = $this->service->generateRestockRecommendations($this->business, $refUpcoming);
        $itemUpcoming = $resUpcoming['recommendations'][0];

        $this->assertEquals('upcoming', $itemUpcoming['urgency']);
        $this->assertTrue($itemUpcoming['is_recommended']);
        $this->assertEquals(1, $itemUpcoming['days_until_restock']);
        $this->assertEquals('Perlu Restock 1 Hari Lagi', $itemUpcoming['urgency_label']);
        $this->assertStringContainsString('tiba dalam 1 hari lagi', $itemUpcoming['pattern_reason']);

        // 2. Kasus Safe (10 Nov: 1 hari sejak 9 Nov -> sisa 3 hari lagi menuju 13 Nov)
        $refSafe = Carbon::parse('2026-11-10');
        Carbon::setTestNow($refSafe);
        $resSafe = $this->service->generateRestockRecommendations($this->business, $refSafe);
        $itemSafe = $resSafe['recommendations'][0];

        $this->assertEquals('safe', $itemSafe['urgency']);
        $this->assertFalse($itemSafe['is_recommended']);
        $this->assertEquals(3, $itemSafe['days_until_restock']);
        $this->assertEquals('Stok Pola Masih Aman', $itemSafe['urgency_label']);
        $this->assertStringContainsString('masih berjarak 3 hari', $itemSafe['pattern_reason']);
    }

    /**
     * Uji estimasi kuantitas yang disarankan dan estimasi anggaran biaya belanja.
     */
    public function test_suggests_realistic_quantity_and_cost_based_on_history(): void
    {
        // Pembelian Kopi:
        // 1 Nov: 2 kg @ 100.000 = 200.000
        // 5 Nov: 4 kg @ 100.000 = 400.000
        // 9 Nov: 3 kg @ 100.000 = 300.000
        // Total 9 kg / 3 kali = rata-rata 3 kg @ 100.000 = 300.000
        $this->createRawMaterialTransaction('2026-11-01', 'Biji Kopi Arabika', 2, 'kg', 100000);
        $this->createRawMaterialTransaction('2026-11-05', 'Biji Kopi Arabika', 4, 'kg', 100000);
        $this->createRawMaterialTransaction('2026-11-09', 'Biji Kopi Arabika', 3, 'kg', 100000);

        $refDate = Carbon::parse('2026-11-13');
        Carbon::setTestNow($refDate);

        $result = $this->service->generateRestockRecommendations($this->business, $refDate);
        $item = $result['recommendations'][0];

        $this->assertEquals(3.0, $item['suggested_quantity']);
        $this->assertEquals('kg', $item['unit']);
        $this->assertEquals(100000.0, $item['avg_unit_price']);
        $this->assertEquals(300000.0, $item['estimated_cost']);
        $this->assertEquals(300000.0, $result['total_estimated_budget']);
    }

    /**
     * Uji pengurutan prioritas rekomendasi (overdue -> due_today -> upcoming -> safe).
     */
    public function test_sorts_recommendations_by_urgency_priority(): void
    {
        // Reference Date = 13 Nov 2026 (span transaksi 1 Nov - 13 Nov >= 7 hari)
        $refDate = Carbon::parse('2026-11-13');
        Carbon::setTestNow($refDate);

        // 1. Gula: Interval 3 hari (1 Nov, 4 Nov, 7 Nov). Terakhir beli 7 Nov.
        // Jadwal: 7 + 3 = 10 Nov. Hari ini 13 Nov -> Overdue 3 hari!
        $this->createRawMaterialTransaction('2026-11-01', 'Gula Pasir', 5, 'kg', 14000);
        $this->createRawMaterialTransaction('2026-11-04', 'Gula Pasir', 5, 'kg', 14000);
        $this->createRawMaterialTransaction('2026-11-07', 'Gula Pasir', 5, 'kg', 14000);

        // 2. Beras: Interval 4 hari (1 Nov, 5 Nov, 9 Nov). Terakhir beli 9 Nov.
        // Jadwal: 9 + 4 = 13 Nov. Hari ini 13 Nov -> Due Today!
        $this->createRawMaterialTransaction('2026-11-01', 'Beras', 10, 'kg', 15000);
        $this->createRawMaterialTransaction('2026-11-05', 'Beras', 10, 'kg', 15000);
        $this->createRawMaterialTransaction('2026-11-09', 'Beras', 10, 'kg', 15000);

        // 3. Susu: Interval 3 hari (5 Nov, 8 Nov, 11 Nov). Terakhir beli 11 Nov.
        // Jadwal: 11 + 3 = 14 Nov. Hari ini 13 Nov -> Upcoming 1 hari!
        $this->createRawMaterialTransaction('2026-11-05', 'Susu UHT', 6, 'liter', 20000);
        $this->createRawMaterialTransaction('2026-11-08', 'Susu UHT', 6, 'liter', 20000);
        $this->createRawMaterialTransaction('2026-11-11', 'Susu UHT', 6, 'liter', 20000);

        // 4. Sirup: Interval 5 hari (2 Nov, 7 Nov, 12 Nov). Terakhir beli 12 Nov.
        // Jadwal: 12 + 5 = 17 Nov. Hari ini 13 Nov -> Safe 4 hari!
        $this->createRawMaterialTransaction('2026-11-02', 'Sirup Vanilla', 2, 'botol', 35000);
        $this->createRawMaterialTransaction('2026-11-07', 'Sirup Vanilla', 2, 'botol', 35000);
        $this->createRawMaterialTransaction('2026-11-12', 'Sirup Vanilla', 2, 'botol', 35000);

        $result = $this->service->generateRestockRecommendations($this->business, $refDate);

        $this->assertTrue($result['is_sufficient']);
        $this->assertCount(4, $result['recommendations']);

        // Urutan 1: Overdue (Gula)
        $this->assertEquals('Gula Pasir', $result['recommendations'][0]['item_name']);
        $this->assertEquals('overdue', $result['recommendations'][0]['urgency']);

        // Urutan 2: Due Today (Beras)
        $this->assertEquals('Beras', $result['recommendations'][1]['item_name']);
        $this->assertEquals('due_today', $result['recommendations'][1]['urgency']);

        // Urutan 3: Upcoming (Susu)
        $this->assertEquals('Susu Uht', $result['recommendations'][2]['item_name']);
        $this->assertEquals('upcoming', $result['recommendations'][2]['urgency']);

        // Urutan 4: Safe (Sirup)
        $this->assertEquals('Sirup Vanilla', $result['recommendations'][3]['item_name']);
        $this->assertEquals('safe', $result['recommendations'][3]['urgency']);

        $this->assertEquals(3, $result['recommended_count']); // Gula, Beras, Susu
        $this->assertEquals(1, $result['safe_count']); // Sirup
        $this->assertEquals(4, $result['total_analyzed_count']);
    }

    /**
     * Uji kepatuhan DoD KV-047: Saran didasarkan pola pembelian, bukan jumlah stok pasti.
     */
    public function test_pattern_reason_does_not_claim_physical_stock_level(): void
    {
        $this->createRawMaterialTransaction('2026-11-01', 'Beras Rojolele', 5, 'kg', 15000);
        $this->createRawMaterialTransaction('2026-11-05', 'Beras Rojolele', 5, 'kg', 15000);
        $this->createRawMaterialTransaction('2026-11-09', 'Beras Rojolele', 5, 'kg', 15000);

        $refDate = Carbon::parse('2026-11-13');
        Carbon::setTestNow($refDate);

        $result = $this->service->generateRestockRecommendations($this->business, $refDate);
        $item = $result['recommendations'][0];

        // Harus menjelaskan pola & kebiasaan belanja berkala
        $this->assertStringContainsString('rata-rata dibeli setiap', $item['pattern_reason']);
        $this->assertStringContainsString('siklus', $item['pattern_reason']);

        // Tidak mengklaim sisa stok fisik riil gudang
        $this->assertStringNotContainsString('sisa stok gudang tinggal', $item['pattern_reason']);
        $this->assertStringNotContainsString('stok fisik tersisa', $item['pattern_reason']);

        // Disclaimer terpasang
        $this->assertEquals(RestockService::DISCLAIMER, $result['disclaimer']);
    }

    /**
     * Uji bisnis dengan histori < 7 hari menghasilkan rekomendasi kosong secara graceful.
     */
    public function test_business_with_insufficient_history_returns_empty_recommendations(): void
    {
        // Hanya 3 hari histori (1 Nov s.d. 3 Nov)
        $refDate = Carbon::parse('2026-11-03');
        Carbon::setTestNow($refDate);

        $this->createRawMaterialTransaction('2026-11-01', 'Beras Rojolele', 5, 'kg', 15000);
        $this->createRawMaterialTransaction('2026-11-03', 'Beras Rojolele', 5, 'kg', 15000);

        $result = $this->service->generateRestockRecommendations($this->business, $refDate);

        $this->assertFalse($result['is_sufficient']);
        $this->assertEmpty($result['recommendations']);
        $this->assertEquals(0, $result['recommended_count']);
        $this->assertEquals(0, $result['safe_count']);
    }

    /**
     * Helper membuat transaksi bahan baku belanja keluar.
     */
    private function createRawMaterialTransaction(
        string $date,
        string $itemName,
        float $quantity,
        string $unit,
        float $unitPrice
    ): Transaction {
        $subtotal = $quantity * $unitPrice;

        $tx = Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_out',
            'category' => 'bahan_baku',
            'amount' => $subtotal,
            'transaction_date' => $date,
            'source_type' => 'text',
            'is_cash_settled' => true,
        ]);

        TransactionItem::create([
            'transaction_id' => $tx->id,
            'item_name' => $itemName,
            'quantity' => $quantity,
            'unit' => $unit,
            'unit_price' => $unitPrice,
            'subtotal' => $subtotal,
        ]);

        return $tx;
    }
}
