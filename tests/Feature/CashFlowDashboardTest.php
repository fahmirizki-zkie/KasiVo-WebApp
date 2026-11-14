<?php

namespace Tests\Feature;

use App\Livewire\Dashboard;
use App\Models\Business;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CashFlowDashboardTest extends TestCase
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
            'name' => 'Kedai Kopi KasiVo',
            'business_type' => 'food_beverage',
        ]);
    }

    /**
     * Uji proteksi autentikasi: Tamu tidak terautentikasi diarahkan ke login.
     */
    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    /**
     * Uji 3 kartu metrik kas utama menampilkan angka riil sesuai database (DoD KV-040).
     */
    public function test_dashboard_displays_three_cash_flow_cards_with_accurate_numbers(): void
    {
        // 1. Kas Masuk 1.500.000
        Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_in',
            'category' => 'penjualan_tunai',
            'amount' => 1500000,
            'transaction_date' => now()->toDateString(),
            'source_type' => 'text',
            'is_cash_settled' => true,
        ]);

        // 2. Kas Keluar 500.000
        Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_out',
            'category' => 'bahan_baku',
            'amount' => 500000,
            'transaction_date' => now()->toDateString(),
            'source_type' => 'text',
            'is_cash_settled' => true,
        ]);

        Livewire::actingAs($this->user)
            ->test(Dashboard::class)
            ->assertStatus(200)
            ->assertSee('Total Kas Masuk')
            ->assertSee('Rp 1.500.000')
            ->assertSee('Total Kas Keluar')
            ->assertSee('Rp 500.000')
            ->assertSee('Arus Kas Bersih')
            ->assertSee('Rp 1.000.000')
            ->assertSee('Kas Surplus');
    }

    /**
     * Uji filter periode default dashboard wajib 'this_month' (DoD KV-041).
     */
    public function test_dashboard_period_filter_defaults_to_this_month(): void
    {
        Livewire::actingAs($this->user)
            ->test(Dashboard::class)
            ->assertSet('period', 'this_month')
            ->assertSee('Bulan Ini');
    }

    /**
     * Uji pergantian filter periode ke 'today' memperbarui kartu metrik secara reaktif.
     */
    public function test_changing_period_filter_to_today_updates_card_metrics(): void
    {
        // Transaksi kemarin (tidak boleh masuk hitungan Hari Ini)
        Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_in',
            'category' => 'penjualan',
            'amount' => 1000000,
            'transaction_date' => Carbon::today()->subDay()->toDateString(),
            'source_type' => 'text',
            'is_cash_settled' => true,
        ]);

        // Transaksi hari ini
        Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_in',
            'category' => 'penjualan',
            'amount' => 300000,
            'transaction_date' => Carbon::today()->toDateString(),
            'source_type' => 'text',
            'is_cash_settled' => true,
        ]);

        // Saat filter 'this_month', total adalah 1.300.000
        // Saat diganti ke 'today', total harus menjadi 300.000
        Livewire::actingAs($this->user)
            ->test(Dashboard::class)
            ->assertSee('Rp 1.300.000')
            ->call('setPeriod', 'today')
            ->assertSet('period', 'today')
            ->assertSee('Rp 300.000')
            ->assertDontSee('Rp 1.300.000');
    }

    /**
     * Uji pergantian filter periode ke '7_days' memperbarui kartu metrik secara reaktif.
     */
    public function test_changing_period_filter_to_seven_days_updates_card_metrics(): void
    {
        // Transaksi 4 hari lalu (masuk 7 hari)
        Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_out',
            'category' => 'operasional',
            'amount' => 200000,
            'transaction_date' => now()->subDays(4)->toDateString(),
            'source_type' => 'text',
            'is_cash_settled' => true,
        ]);

        // Transaksi 20 hari lalu (di luar 7 hari, tapi masih di bulan ini jika awal bulan)
        Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_out',
            'category' => 'operasional',
            'amount' => 450000,
            'transaction_date' => now()->subDays(20)->toDateString(),
            'source_type' => 'text',
            'is_cash_settled' => true,
        ]);

        Livewire::actingAs($this->user)
            ->test(Dashboard::class)
            ->call('setPeriod', '7_days')
            ->assertSet('period', '7_days')
            ->assertSee('Rp 200.000');
    }

    /**
     * Uji penanda arus kas negatif tanpa menyebut rugi otomatis (DoD KV-043).
     */
    public function test_dashboard_displays_negative_cash_flow_without_calling_it_loss(): void
    {
        // Kas masuk 100.000
        Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_in',
            'category' => 'penjualan',
            'amount' => 100000,
            'transaction_date' => now()->toDateString(),
            'source_type' => 'text',
            'is_cash_settled' => true,
        ]);

        // Kas keluar 600.000 (belanja stok)
        Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_out',
            'category' => 'bahan_baku',
            'amount' => 600000,
            'transaction_date' => now()->toDateString(),
            'source_type' => 'text',
            'is_cash_settled' => true,
        ]);

        $test = Livewire::actingAs($this->user)
            ->test(Dashboard::class)
            ->assertStatus(200)
            ->assertSee('-Rp 500.000')
            ->assertSee('Kas Keluar Lebih Besar');

        // Pastikan tidak ada kata "rugi" atau "laba rugi" otomatis
        $content = $test->html();
        $this->assertStringNotContainsString('Rugi Otomatis', $content);
        $this->assertStringNotContainsString('rugi bersih', strtolower($content));
    }

    /**
     * Uji empty state saat tidak ada transaksi pada periode terpilih (DoD KV-043).
     */
    public function test_dashboard_displays_empty_state_when_no_transactions(): void
    {
        Livewire::actingAs($this->user)
            ->test(Dashboard::class)
            ->assertStatus(200)
            ->assertSee('Rp 0')
            ->assertSee('Belum Ada Catatan Kas di Periode Ini')
            ->assertSee('Catat Penjualan Sekarang')
            ->assertSee('Catat Belanja Bahan');
    }

    /**
     * Uji isolasi multi-tenant: Transaksi bisnis lain tidak muncul di dashboard pengguna.
     */
    public function test_dashboard_maintains_strict_multitenant_isolation(): void
    {
        $otherUser = User::factory()->create();
        $otherBusiness = Business::create([
            'user_id' => $otherUser->id,
            'name' => 'Warung Lain',
            'business_type' => 'food_beverage',
        ]);

        // Transaksi milik bisnis sendiri
        Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_in',
            'category' => 'penjualan',
            'amount' => 750000,
            'transaction_date' => now()->toDateString(),
            'source_type' => 'text',
            'is_cash_settled' => true,
        ]);

        // Transaksi milik bisnis tetangga bernilai besar
        Transaction::create([
            'business_id' => $otherBusiness->id,
            'type' => 'cash_in',
            'category' => 'penjualan',
            'amount' => 99000000,
            'transaction_date' => now()->toDateString(),
            'source_type' => 'text',
            'is_cash_settled' => true,
        ]);

        Livewire::actingAs($this->user)
            ->test(Dashboard::class)
            ->assertSee('Rp 750.000')
            ->assertDontSee('Rp 99.000.000');
    }
}
