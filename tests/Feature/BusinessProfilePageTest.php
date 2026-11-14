<?php

namespace Tests\Feature;

use App\Livewire\BusinessProfile;
use App\Models\Business;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use App\Services\BusinessReportService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BusinessProfilePageTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Business $business;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'name' => 'Fahmi Owner',
            'email' => 'fahmi@waroeng.test',
        ]);

        $this->business = Business::create([
            'user_id' => $this->user->id,
            'name' => 'Waroeng Kopi Sedap',
            'business_type' => 'food_beverage',
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /**
     * Tamu diarahkan ke login.
     */
    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get(route('business-profile'));
        $response->assertRedirect(route('login'));
    }

    /**
     * Pengguna tanpa profil usaha melihat notice untuk melengkapi profil.
     */
    public function test_user_without_business_sees_onboarding_notice(): void
    {
        $userWithoutBusiness = User::factory()->create();

        Livewire::actingAs($userWithoutBusiness)
            ->test(BusinessProfile::class)
            ->assertStatus(200)
            ->assertSee('Profil Usaha Belum Terdaftar')
            ->assertSee('Lengkapi Profil Usaha');
    }

    /**
     * Halaman menampilkan data usaha, ringkasan kas, tombol unduh & pratinjau PDF, serta disclaimer.
     */
    public function test_business_profile_page_renders_with_complete_data(): void
    {
        Carbon::setTestNow('2026-11-14 10:00:00');

        // Transaksi Masuk Rp 5.000.000
        Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_in',
            'category' => 'penjualan',
            'amount' => 5000000,
            'transaction_date' => '2026-11-10',
            'source_type' => 'text',
            'is_cash_settled' => true,
        ]);

        // Transaksi Keluar Rp 2.000.000
        $txOut = Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_out',
            'category' => 'bahan_baku',
            'amount' => 2000000,
            'transaction_date' => '2026-11-11',
            'source_type' => 'text',
            'is_cash_settled' => true,
        ]);

        TransactionItem::create([
            'transaction_id' => $txOut->id,
            'item_name' => 'Kopi Robusta',
            'quantity' => 20,
            'unit' => 'kg',
            'unit_price' => 100000,
            'subtotal' => 2000000,
        ]);

        Livewire::actingAs($this->user)
            ->test(BusinessProfile::class)
            ->assertStatus(200)
            ->assertSee('Waroeng Kopi Sedap')
            ->assertSee('Fahmi Owner')
            ->assertSee('Kuliner / F&B (Makanan & Minuman)')
            ->assertSee('Rp 5.000.000')
            ->assertSee('Rp 2.000.000')
            ->assertSee('Rp 3.000.000')
            ->assertSee('Unduh Laporan PDF')
            ->assertSee('Pratinjau PDF')
            ->assertSee(BusinessReportService::DISCLAIMER);
    }

    /**
     * Pengguna dapat mengubah periode kalkulasi finansial.
     */
    public function test_user_can_switch_periods_interactively(): void
    {
        Livewire::actingAs($this->user)
            ->test(BusinessProfile::class)
            ->call('setPeriod', 'today')
            ->assertSet('period', 'today')
            ->call('setPeriod', 'last_7_days')
            ->assertSet('period', 'last_7_days')
            ->call('setPeriod', 'all_time')
            ->assertSet('period', 'all_time')
            ->call('setPeriod', 'this_month')
            ->assertSet('period', 'this_month');
    }
}
