<?php

namespace Tests\Unit;

use App\Models\Business;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use App\Services\CashFlowService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashFlowServiceTest extends TestCase
{
    use RefreshDatabase;

    private CashFlowService $service;
    private Business $business;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new CashFlowService();

        $this->user = User::factory()->create();
        $this->business = Business::create([
            'user_id' => $this->user->id,
            'name' => 'Kopi KasiVo Nusantara',
            'business_type' => 'food_beverage',
        ]);
    }

    /**
     * Uji rumus dasar sesuai mandat SPS KV-038 & KV-044:
     * Kas masuk 1 juta, beli stok 300rb, operasional 100rb => Arus kas bersih tepat 600rb.
     */
    public function test_basic_net_cash_flow_calculation_matches_sps_example(): void
    {
        // 1. Kas Masuk 1.000.000
        Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_in',
            'category' => 'penjualan_tunai',
            'amount' => 1000000,
            'transaction_date' => '2026-11-10',
            'source_type' => 'text',
            'is_cash_settled' => true,
        ]);

        // 2. Beli Stok (Kas Keluar) 300.000
        Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_out',
            'category' => 'bahan_baku',
            'amount' => 300000,
            'transaction_date' => '2026-11-11',
            'source_type' => 'text',
            'is_cash_settled' => true,
        ]);

        // 3. Operasional (Kas Keluar) 100.000
        Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_out',
            'category' => 'operasional',
            'amount' => 100000,
            'transaction_date' => '2026-11-12',
            'source_type' => 'text',
            'is_cash_settled' => true,
        ]);

        $summary = $this->service->getSummary(
            $this->business,
            Carbon::parse('2026-11-01'),
            Carbon::parse('2026-11-30')
        );

        $this->assertEquals(1000000.0, $summary['total_cash_in']);
        $this->assertEquals(400000.0, $summary['total_cash_out']);
        $this->assertEquals(600000.0, $summary['net_cash_flow']);
        $this->assertFalse($summary['is_negative']);
        $this->assertEquals(1, $summary['cash_in_count']);
        $this->assertEquals(2, $summary['cash_out_count']);
        $this->assertEquals(3, $summary['total_transactions']);
        $this->assertEquals('Rp 1.000.000', $summary['formatted_cash_in']);
        $this->assertEquals('Rp 400.000', $summary['formatted_cash_out']);
        $this->assertEquals('Rp 600.000', $summary['formatted_net']);
    }

    /**
     * Uji pencegahan double-counting (DoD KV-038):
     * Pembelian stok yang memiliki banyak rincian item wajib hanya dihitung 1x di kas keluar.
     */
    public function test_no_double_count_on_stock_purchases_with_multiple_items(): void
    {
        // Transaksi pembelian bahan baku 300.000 dengan 5 rincian item @ 60.000
        $tx = Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_out',
            'category' => 'bahan_baku',
            'amount' => 300000,
            'transaction_date' => '2026-11-10',
            'source_type' => 'photo',
            'is_cash_settled' => true,
        ]);

        for ($i = 1; $i <= 5; $i++) {
            TransactionItem::create([
                'transaction_id' => $tx->id,
                'item_name' => "Bahan Baku {$i}",
                'quantity' => 1,
                'unit' => 'kg',
                'unit_price' => 60000,
                'subtotal' => 60000,
            ]);
        }

        $summary = $this->service->getSummary($this->business);

        // Kas keluar tetap 300.000, TIDAK menjadi 600.000 karena item tidak di-double count
        $this->assertEquals(300000.0, $summary['total_cash_out']);
        $this->assertEquals(1, $summary['cash_out_count']);
    }

    /**
     * Uji filter rentang tanggal:
     * Transaksi di luar rentang tanggal tidak boleh ikut terjumlah.
     */
    public function test_date_range_filtering_excludes_outside_transactions(): void
    {
        // Transaksi di bulan Oktober 2026
        Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_in',
            'category' => 'penjualan',
            'amount' => 500000,
            'transaction_date' => '2026-10-31',
            'source_type' => 'text',
        ]);

        // Transaksi di rentang 10-15 November 2026
        Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_in',
            'category' => 'penjualan',
            'amount' => 750000,
            'transaction_date' => '2026-11-12',
            'source_type' => 'text',
        ]);

        // Transaksi di bulan Desember 2026
        Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_in',
            'category' => 'penjualan',
            'amount' => 900000,
            'transaction_date' => '2026-12-01',
            'source_type' => 'text',
        ]);

        $summary = $this->service->getSummary(
            $this->business,
            Carbon::parse('2026-11-01'),
            Carbon::parse('2026-11-30')
        );

        $this->assertEquals(750000.0, $summary['total_cash_in']);
        $this->assertEquals(1, $summary['cash_in_count']);
    }

    /**
     * Uji penanda arus kas negatif (DoD KV-043):
     * Ketika pengeluaran > pemasukan, is_negative harus true dan format menampilkan minus.
     */
    public function test_negative_cash_flow_detection(): void
    {
        // Kas masuk 200.000
        Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_in',
            'category' => 'penjualan_tunai',
            'amount' => 200000,
            'transaction_date' => '2026-11-10',
            'source_type' => 'text',
        ]);

        // Kas keluar 800.000
        Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_out',
            'category' => 'sewa',
            'amount' => 800000,
            'transaction_date' => '2026-11-10',
            'source_type' => 'text',
        ]);

        $summary = $this->service->getSummary($this->business);

        $this->assertEquals(-600000.0, $summary['net_cash_flow']);
        $this->assertTrue($summary['is_negative']);
        $this->assertEquals('-Rp 600.000', $summary['formatted_net']);
    }

    /**
     * Uji keadaan tanpa data (empty state) nol transaksi.
     */
    public function test_empty_state_returns_zero_without_exceptions(): void
    {
        $summary = $this->service->getSummary($this->business);

        $this->assertEquals(0.0, $summary['total_cash_in']);
        $this->assertEquals(0.0, $summary['total_cash_out']);
        $this->assertEquals(0.0, $summary['net_cash_flow']);
        $this->assertFalse($summary['is_negative']);
        $this->assertEquals(0, $summary['total_transactions']);
        $this->assertEquals('Rp 0', $summary['formatted_net']);

        $breakdown = $this->service->getCategoryBreakdown($this->business);
        $this->assertEmpty($breakdown['cash_in']);
        $this->assertEmpty($breakdown['cash_out']);
        $this->assertEquals(0.0, $breakdown['total_cash_in']);
        $this->assertEquals(0.0, $breakdown['total_cash_out']);
    }

    /**
     * Uji runtun waktu harian (getDailySeries) dengan zero-fill untuk grafik (KV-042).
     */
    public function test_daily_series_generates_zero_filled_days_for_chart(): void
    {
        // Rentang 3 hari: 10 Nov - 12 Nov
        $start = Carbon::parse('2026-11-10');
        $end = Carbon::parse('2026-11-12');

        // Transaksi hanya ada di tanggal 11 Nov (Kas Masuk 250.000 & Kas Keluar 100.000)
        Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_in',
            'category' => 'penjualan',
            'amount' => 250000,
            'transaction_date' => '2026-11-11',
            'source_type' => 'text',
        ]);

        Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_out',
            'category' => 'operasional',
            'amount' => 100000,
            'transaction_date' => '2026-11-11',
            'source_type' => 'text',
        ]);

        $series = $this->service->getDailySeries($this->business, $start, $end);

        $this->assertCount(3, $series);

        // Hari 1: 10 Nov (Zero-filled)
        $this->assertEquals('2026-11-10', $series[0]['date']);
        $this->assertEquals(0.0, $series[0]['cash_in']);
        $this->assertEquals(0.0, $series[0]['cash_out']);
        $this->assertEquals(0.0, $series[0]['net']);

        // Hari 2: 11 Nov (Ada transaksi)
        $this->assertEquals('2026-11-11', $series[1]['date']);
        $this->assertEquals(250000.0, $series[1]['cash_in']);
        $this->assertEquals(100000.0, $series[1]['cash_out']);
        $this->assertEquals(150000.0, $series[1]['net']);

        // Hari 3: 12 Nov (Zero-filled)
        $this->assertEquals('2026-11-12', $series[2]['date']);
        $this->assertEquals(0.0, $series[2]['cash_in']);
        $this->assertEquals(0.0, $series[2]['cash_out']);
        $this->assertEquals(0.0, $series[2]['net']);
    }

    /**
     * Uji breakdown kategori beserta persentase kontribusi.
     */
    public function test_category_breakdown_calculates_correct_totals_and_percentages(): void
    {
        // Kas Keluar: Bahan Baku 300.000 (75%) & Operasional 100.000 (25%) -> Total 400.000
        Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_out',
            'category' => 'bahan_baku',
            'amount' => 300000,
            'transaction_date' => '2026-11-10',
            'source_type' => 'text',
        ]);

        Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_out',
            'category' => 'operasional',
            'amount' => 100000,
            'transaction_date' => '2026-11-11',
            'source_type' => 'text',
        ]);

        $breakdown = $this->service->getCategoryBreakdown($this->business);

        $this->assertEquals(400000.0, $breakdown['total_cash_out']);
        $this->assertCount(2, $breakdown['cash_out']);

        $bahanBaku = $breakdown['cash_out'][0];
        $this->assertEquals('bahan_baku', $bahanBaku['category']);
        $this->assertEquals('Bahan Baku & Dapur', $bahanBaku['label']);
        $this->assertEquals(300000.0, $bahanBaku['total']);
        $this->assertEquals(75.0, $bahanBaku['percentage']);

        $operasional = $breakdown['cash_out'][1];
        $this->assertEquals('operasional', $operasional['category']);
        $this->assertEquals('Biaya Operasional', $operasional['label']);
        $this->assertEquals(100000.0, $operasional['total']);
        $this->assertEquals(25.0, $operasional['percentage']);
    }

    /**
     * Uji resolver rentang tanggal (KV-041):
     * Default adalah 'this_month'.
     */
    public function test_period_resolver_handles_all_modes_properly(): void
    {
        // 1. Default (this_month)
        $month = $this->service->resolvePeriodDates();
        $this->assertEquals('this_month', $month['period']);
        $this->assertEquals(Carbon::today()->startOfMonth()->toDateString(), $month['start_date']->toDateString());
        $this->assertEquals(Carbon::today()->endOfMonth()->toDateString(), $month['end_date']->toDateString());

        // 2. Today
        $today = $this->service->resolvePeriodDates('today');
        $this->assertEquals('today', $today['period']);
        $this->assertEquals(Carbon::today()->toDateString(), $today['start_date']->toDateString());
        $this->assertEquals(Carbon::today()->toDateString(), $today['end_date']->toDateString());

        // 3. 7 Hari Terakhir
        $sevenDays = $this->service->resolvePeriodDates('7_days');
        $this->assertEquals('7_days', $sevenDays['period']);
        $this->assertEquals(Carbon::today()->subDays(6)->toDateString(), $sevenDays['start_date']->toDateString());
        $this->assertEquals(Carbon::today()->toDateString(), $sevenDays['end_date']->toDateString());

        // 4. Custom
        $custom = $this->service->resolvePeriodDates('custom', '2026-11-05', '2026-11-15');
        $this->assertEquals('custom', $custom['period']);
        $this->assertEquals('2026-11-05', $custom['start_date']->toDateString());
        $this->assertEquals('2026-11-15', $custom['end_date']->toDateString());

        // 5. Custom Terbalik (Start > End) tetap tertata rapi
        $inverted = $this->service->resolvePeriodDates('custom', '2026-11-20', '2026-11-10');
        $this->assertEquals('2026-11-10', $inverted['start_date']->toDateString());
        $this->assertEquals('2026-11-20', $inverted['end_date']->toDateString());

        // 6. All
        $all = $this->service->resolvePeriodDates('all');
        $this->assertEquals('all', $all['period']);
        $this->assertNull($all['start_date']);
        $this->assertNull($all['end_date']);
    }

    /**
     * Uji isolasi data bisnis (multi-tenant safety):
     * Transaksi bisnis lain tidak boleh merembes ke perhitungan arus kas.
     */
    public function test_multitenancy_isolation_prevents_data_leakage(): void
    {
        $otherUser = User::factory()->create();
        $otherBusiness = Business::create([
            'user_id' => $otherUser->id,
            'name' => 'Bisnis Tetangga',
            'business_type' => 'food_beverage',
        ]);

        // Transaksi bisnis sendiri: Kas Masuk 100.000
        Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_in',
            'category' => 'penjualan',
            'amount' => 100000,
            'transaction_date' => '2026-11-10',
            'source_type' => 'text',
        ]);

        // Transaksi bisnis lain: Kas Masuk 900.000
        Transaction::create([
            'business_id' => $otherBusiness->id,
            'type' => 'cash_in',
            'category' => 'penjualan',
            'amount' => 900000,
            'transaction_date' => '2026-11-10',
            'source_type' => 'text',
        ]);

        $summary = $this->service->getSummary($this->business);
        $this->assertEquals(100000.0, $summary['total_cash_in']);
        $this->assertEquals(1, $summary['cash_in_count']);
    }
}

