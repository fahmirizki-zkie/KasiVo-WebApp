<?php

namespace Tests\Feature;

use App\Livewire\Dashboard;
use App\Livewire\TransactionHistory;
use App\Models\Business;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use App\Services\CashFlowService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CashFlowReconciliationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Business $business;
    private CashFlowService $cashFlowService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cashFlowService = app(CashFlowService::class);

        $this->user = User::factory()->create();
        $this->business = Business::create([
            'user_id' => $this->user->id,
            'name' => 'Kopi KasiVo Rekonsiliasi',
            'business_type' => 'food_beverage',
        ]);
    }

    /**
     * Uji kasus spesifikasi resmi SPS KV-044:
     * Kas masuk 1 juta, beli stok 300rb, operasional 100rb => Arus kas bersih tepat 600rb.
     * Terverifikasi pada level CashFlowService dan Livewire Dashboard.
     */
    public function test_exact_sps_case_in_1m_stock_300k_operational_100k_yields_net_600k(): void
    {
        // 1. Kas Masuk 1.000.000
        Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_in',
            'category' => 'penjualan_tunai',
            'amount' => 1000000,
            'transaction_date' => Carbon::today()->toDateString(),
            'source_type' => 'text',
            'is_cash_settled' => true,
        ]);

        // 2. Beli Stok (Kas Keluar) 300.000
        Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_out',
            'category' => 'bahan_baku',
            'amount' => 300000,
            'transaction_date' => Carbon::today()->toDateString(),
            'source_type' => 'text',
            'is_cash_settled' => true,
        ]);

        // 3. Biaya Operasional (Kas Keluar) 100.000
        Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_out',
            'category' => 'operasional',
            'amount' => 100000,
            'transaction_date' => Carbon::today()->toDateString(),
            'source_type' => 'text',
            'is_cash_settled' => true,
        ]);

        // Verifikasi pada Service
        $summary = $this->cashFlowService->getSummary(
            $this->business,
            Carbon::today()->startOfMonth(),
            Carbon::today()->endOfMonth()
        );

        $this->assertEquals(1000000.0, $summary['total_cash_in']);
        $this->assertEquals(400000.0, $summary['total_cash_out']);
        $this->assertEquals(600000.0, $summary['net_cash_flow']);
        $this->assertEquals('Rp 1.000.000', $summary['formatted_cash_in']);
        $this->assertEquals('Rp 400.000', $summary['formatted_cash_out']);
        $this->assertEquals('Rp 600.000', $summary['formatted_net']);
        $this->assertFalse($summary['is_negative']);
        $this->assertEquals(3, $summary['total_transactions']);

        // Verifikasi pada Livewire Dashboard
        Livewire::actingAs($this->user)
            ->test(Dashboard::class)
            ->assertSee('Rp 1.000.000')
            ->assertSee('Rp 400.000')
            ->assertSee('Rp 600.000')
            ->assertSee('Kas Surplus')
            ->assertSee('3 Transaksi');
    }

    /**
     * Uji rekonsiliasi pasca koreksi/edit nominal transaksi (KV-016):
     * Ketika transaksi belanja stok Rp 300.000 diedit menjadi Rp 350.000 via Livewire,
     * arus kas di Dashboard otomatis terkoreksi dari Rp 600.000 menjadi Rp 550.000.
     */
    public function test_reconciliation_after_editing_transaction_amount_via_livewire(): void
    {
        // 1. Kas Masuk 1.000.000
        Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_in',
            'category' => 'penjualan_tunai',
            'amount' => 1000000,
            'transaction_date' => Carbon::today()->toDateString(),
            'source_type' => 'text',
            'is_cash_settled' => true,
        ]);

        // 2. Beli Stok (Kas Keluar) 300.000
        $stockTx = Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_out',
            'category' => 'bahan_baku',
            'amount' => 300000,
            'transaction_date' => Carbon::today()->toDateString(),
            'source_type' => 'text',
            'is_cash_settled' => true,
        ]);

        // 3. Operasional 100.000
        Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_out',
            'category' => 'operasional',
            'amount' => 100000,
            'transaction_date' => Carbon::today()->toDateString(),
            'source_type' => 'text',
            'is_cash_settled' => true,
        ]);

        // Sebelum edit: Net = Rp 600.000
        Livewire::actingAs($this->user)
            ->test(Dashboard::class)
            ->assertSee('Rp 600.000');

        // Pengguna melakukan edit nominal stok menjadi 350.000 via TransactionHistory Livewire (KV-016)
        Livewire::actingAs($this->user)
            ->test(TransactionHistory::class)
            ->call('showDetail', $stockTx->id)
            ->call('startEditing')
            ->set('edit_amount', 350000)
            ->set('edit_category', 'bahan_baku')
            ->set('edit_transaction_date', Carbon::today()->toDateString())
            ->call('updateTransaction')
            ->assertHasNoErrors();

        // Database terbukti terbarui
        $this->assertDatabaseHas('transactions', [
            'id' => $stockTx->id,
            'amount' => 350000,
        ]);

        // Dashboard langsung terekonsiliasi otomatis:
        // Total Kas Keluar: 350.000 + 100.000 = 450.000
        // Arus Kas Bersih: 1.000.000 - 450.000 = 550.000
        Livewire::actingAs($this->user)
            ->test(Dashboard::class)
            ->assertSee('Rp 450.000')
            ->assertSee('Rp 550.000')
            ->assertDontSee('Rp 600.000');
    }

    /**
     * Uji rekonsiliasi pasca hapus transaksi (KV-017):
     * Ketika biaya operasional Rp 100.000 dihapus via Livewire,
     * arus kas di Dashboard otomatis pulih menjadi Rp 700.000 dan counter transaksi berkurang menjadi 2.
     */
    public function test_reconciliation_after_deleting_transaction_via_livewire(): void
    {
        // 1. Kas Masuk 1.000.000
        Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_in',
            'category' => 'penjualan_tunai',
            'amount' => 1000000,
            'transaction_date' => Carbon::today()->toDateString(),
            'source_type' => 'text',
            'is_cash_settled' => true,
        ]);

        // 2. Beli Stok 300.000
        Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_out',
            'category' => 'bahan_baku',
            'amount' => 300000,
            'transaction_date' => Carbon::today()->toDateString(),
            'source_type' => 'text',
            'is_cash_settled' => true,
        ]);

        // 3. Operasional 100.000 yang akan dihapus
        $opTx = Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_out',
            'category' => 'operasional',
            'amount' => 100000,
            'transaction_date' => Carbon::today()->toDateString(),
            'source_type' => 'text',
            'is_cash_settled' => true,
        ]);

        // Pengguna menghapus transaksi operasional via Livewire (KV-017)
        Livewire::actingAs($this->user)
            ->test(TransactionHistory::class)
            ->call('confirmDelete', $opTx->id)
            ->call('deleteTransaction');

        $this->assertDatabaseMissing('transactions', ['id' => $opTx->id]);

        // Dashboard langsung terekonsiliasi:
        // Kas Keluar: 300.000
        // Arus Kas Bersih: 1.000.000 - 300.000 = 700.000
        // Total Transaksi: 2
        Livewire::actingAs($this->user)
            ->test(Dashboard::class)
            ->assertSee('Rp 300.000')
            ->assertSee('Rp 700.000')
            ->assertSee('2 Transaksi')
            ->assertDontSee('Rp 400.000')
            ->assertDontSee('Rp 600.000');
    }

    /**
     * Uji rekonsiliasi pergeseran tanggal transaksi lintas periode:
     * Mengubah tanggal transaksi kas masuk dari bulan ini ke bulan lalu otomatis
     * mengeluarkan nominal tersebut dari filter 'this_month'.
     */
    public function test_reconciliation_after_editing_transaction_date_across_periods(): void
    {
        // Transaksi kas masuk 800.000 pada hari ini
        $incomeTx = Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_in',
            'category' => 'penjualan',
            'amount' => 800000,
            'transaction_date' => Carbon::today()->toDateString(),
            'source_type' => 'text',
            'is_cash_settled' => true,
        ]);

        // Awalnya muncul di dashboard Bulan Ini
        Livewire::actingAs($this->user)
            ->test(Dashboard::class)
            ->assertSee('Rp 800.000');

        // Edit tanggal transaksi menjadi bulan lalu
        $lastMonthDate = Carbon::today()->subMonthsNoOverflow(1)->startOfMonth()->toDateString();

        Livewire::actingAs($this->user)
            ->test(TransactionHistory::class)
            ->call('showDetail', $incomeTx->id)
            ->call('startEditing')
            ->set('edit_amount', 800000)
            ->set('edit_category', 'penjualan')
            ->set('edit_transaction_date', $lastMonthDate)
            ->call('updateTransaction')
            ->assertHasNoErrors();

        // Pada filter default 'this_month', kas masuk menjadi Rp 0 (karena transaksi sudah berpindah ke bulan lalu)
        Livewire::actingAs($this->user)
            ->test(Dashboard::class)
            ->assertSet('period', 'this_month')
            ->assertSee('Rp 0')
            ->assertSee('Belum Ada Catatan Kas di Periode Ini')
            ->assertDontSee('Rp 800.000');
    }

    /**
     * Uji pencegahan double-counting dengan banyak rincian item (DoD KV-038 & KV-044):
     * Belanja bahan baku Rp 500.000 dengan 10 TransactionItem tidak pernah berlipat ganda.
     */
    public function test_reconciliation_no_double_count_on_multi_item_stock_purchases(): void
    {
        $tx = Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_out',
            'category' => 'bahan_baku',
            'amount' => 500000,
            'transaction_date' => Carbon::today()->toDateString(),
            'source_type' => 'photo',
            'is_cash_settled' => true,
        ]);

        // Buat 10 baris rincian item (@ Rp 50.000)
        for ($i = 1; $i <= 10; $i++) {
            TransactionItem::create([
                'transaction_id' => $tx->id,
                'item_name' => "Bahan Dapur {$i}",
                'quantity' => 2,
                'unit' => 'kg',
                'unit_price' => 25000,
                'subtotal' => 50000,
            ]);
        }

        $summary = $this->cashFlowService->getSummary($this->business);

        // Kas keluar tetap 500.000 (bukan 1.000.000)
        $this->assertEquals(500000.0, $summary['total_cash_out']);
        $this->assertEquals(1, $summary['cash_out_count']);

        Livewire::actingAs($this->user)
            ->test(Dashboard::class)
            ->assertSee('Rp 500.000')
            ->assertDontSee('Rp 1.000.000');
    }

    /**
     * Uji batas rentang tanggal (Date Boundary Precision):
     * Transaksi pada tanggal 1 bulan dan tanggal akhir bulan wajib masuk hitungan 'this_month',
     * sedangkan transaksi 1 hari sebelum atau sesudahnya wajib terisolasi keluar.
     */
    public function test_date_boundary_reconciliation_for_monthly_filters(): void
    {
        $startOfMonth = Carbon::today()->startOfMonth();
        $endOfMonth = Carbon::today()->endOfMonth();

        // 1. Tepat di hari pertama bulan (Masuk)
        Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_in',
            'category' => 'penjualan',
            'amount' => 100000,
            'transaction_date' => $startOfMonth->toDateString(),
            'source_type' => 'text',
            'is_cash_settled' => true,
        ]);

        // 2. Tepat di hari terakhir bulan (Masuk)
        Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_in',
            'category' => 'penjualan',
            'amount' => 200000,
            'transaction_date' => $endOfMonth->toDateString(),
            'source_type' => 'text',
            'is_cash_settled' => true,
        ]);

        // 3. Tepat 1 hari sebelum awal bulan (Keluar / Excluded)
        Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_in',
            'category' => 'penjualan',
            'amount' => 999999,
            'transaction_date' => $startOfMonth->copy()->subDay()->toDateString(),
            'source_type' => 'text',
            'is_cash_settled' => true,
        ]);

        // 4. Tepat 1 hari setelah akhir bulan (Keluar / Excluded)
        Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_in',
            'category' => 'penjualan',
            'amount' => 888888,
            'transaction_date' => $endOfMonth->copy()->addDay()->toDateString(),
            'source_type' => 'text',
            'is_cash_settled' => true,
        ]);

        $summary = $this->cashFlowService->getSummary(
            $this->business,
            $startOfMonth,
            $endOfMonth
        );

        // Hanya 100.000 + 200.000 = 300.000 yang masuk
        $this->assertEquals(300000.0, $summary['total_cash_in']);
        $this->assertEquals(2, $summary['cash_in_count']);

        Livewire::actingAs($this->user)
            ->test(Dashboard::class)
            ->assertSee('Rp 300.000')
            ->assertDontSee('Rp 999.999')
            ->assertDontSee('Rp 888.888');
    }

    /**
     * Uji isolasi rekonsiliasi multi-tenant:
     * Aksi edit atau hapus transaksi pada Bisnis Tetangga sama sekali tidak
     * mempengaruhi pembukuan atau dashboard Bisnis Pengguna.
     */
    public function test_multitenant_reconciliation_isolation(): void
    {
        $otherUser = User::factory()->create();
        $otherBusiness = Business::create([
            'user_id' => $otherUser->id,
            'name' => 'Warung Kompetitor',
            'business_type' => 'food_beverage',
        ]);

        // Bisnis Sendiri: Kas Masuk 1.000.000, Belanja 400.000 -> Net: 600.000
        Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_in',
            'category' => 'penjualan',
            'amount' => 1000000,
            'transaction_date' => Carbon::today()->toDateString(),
            'source_type' => 'text',
            'is_cash_settled' => true,
        ]);
        Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_out',
            'category' => 'bahan_baku',
            'amount' => 400000,
            'transaction_date' => Carbon::today()->toDateString(),
            'source_type' => 'text',
            'is_cash_settled' => true,
        ]);

        // Bisnis Lain: Transaksi 50.000.000
        $otherTx = Transaction::create([
            'business_id' => $otherBusiness->id,
            'type' => 'cash_in',
            'category' => 'penjualan',
            'amount' => 50000000,
            'transaction_date' => Carbon::today()->toDateString(),
            'source_type' => 'text',
            'is_cash_settled' => true,
        ]);

        // User Lain menghapus transaksi miliknya
        Livewire::actingAs($otherUser)
            ->test(TransactionHistory::class)
            ->call('confirmDelete', $otherTx->id)
            ->call('deleteTransaction');

        // Dashboard Bisnis Sendiri tetap kokoh di angka Rp 600.000
        Livewire::actingAs($this->user)
            ->test(Dashboard::class)
            ->assertSee('Rp 1.000.000')
            ->assertSee('Rp 400.000')
            ->assertSee('Rp 600.000')
            ->assertDontSee('Rp 50.000.000');
    }
}
