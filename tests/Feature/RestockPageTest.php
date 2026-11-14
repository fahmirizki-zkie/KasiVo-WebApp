<?php

namespace Tests\Feature;

use App\Livewire\RestockSuggestion;
use App\Models\Business;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use App\Services\RestockService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RestockPageTest extends TestCase
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
            'name' => 'Waroeng Kopi Sedap KasiVo',
            'business_type' => 'food_beverage',
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /**
     * Uji proteksi autentikasi: Tamu tidak terautentikasi diarahkan ke login.
     */
    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get(route('restock'));
        $response->assertRedirect(route('login'));

        $responseIndex = $this->get(route('restock.index'));
        $responseIndex->assertRedirect(route('login'));
    }

    /**
     * Uji halaman saran restock berhasil diakses oleh pengguna yang login (HTTP 200).
     */
    public function test_restock_page_renders_successfully_for_authenticated_user(): void
    {
        $response = $this->actingAs($this->user)->get(route('restock'));
        $response->assertStatus(200);
        $response->assertSee('Saran Restock Bahan Baku');
        $response->assertSee('Waroeng Kopi Sedap KasiVo');
    }

    /**
     * Uji tampilan empty state edukatif jika riwayat belanja belum mencapai 7 hari (DoD KV-046 & KV-049).
     */
    public function test_restock_page_displays_insufficient_data_empty_state_when_under_seven_days(): void
    {
        Carbon::setTestNow('2026-11-03 10:00:00');

        // Belanja baru 3 hari (1 Nov s.d. 3 Nov)
        $this->createRawMaterialTransaction('2026-11-01', 'Beras Rojolele', 5, 'kg', 15000);
        $this->createRawMaterialTransaction('2026-11-03', 'Beras Rojolele', 5, 'kg', 15000);

        Livewire::actingAs($this->user)
            ->test(RestockSuggestion::class)
            ->assertStatus(200)
            ->assertSee('Data Belum Cukup')
            ->assertSee('Sisa 4 Hari Lagi')
            ->assertSee('Pencatatan Belanja Bahan Masih Membangun Pola')
            ->assertSee('Catat Pembelian Bahan')
            ->assertSee('Foto Nota Belanja')
            ->assertDontSee('Perlu Restock Hari Ini');
    }

    /**
     * Uji halaman menampilkan kartu bahan yang disarankan restock & narasi AI saat data cukup (DoD KV-047, KV-048, KV-049).
     */
    public function test_restock_page_displays_recommended_items_and_ai_explanation_when_sufficient(): void
    {
        Carbon::setTestNow('2026-11-13 10:00:00');

        // Beras dibeli tiap 4 hari (1 Nov, 5 Nov, 9 Nov). Terakhir 9 Nov -> Jadwal: 13 Nov (Hari ini!)
        $this->createRawMaterialTransaction('2026-11-01', 'Beras Rojolele', 5, 'kg', 15000);
        $this->createRawMaterialTransaction('2026-11-05', 'Beras Rojolele', 5, 'kg', 15000);
        $this->createRawMaterialTransaction('2026-11-09', 'Beras Rojolele', 5, 'kg', 15000);

        Livewire::actingAs($this->user)
            ->test(RestockSuggestion::class)
            ->assertStatus(200)
            ->assertSee('Beras Rojolele')
            ->assertSee('Perlu Restock Hari Ini')
            ->assertSee('5 kg')
            ->assertSee('75.000')
            ->assertSee('Tiap 4 hari')
            ->assertSee('Bahan Perlu Tindakan')
            ->assertSee('Catat Belanja Beras Rojolele');
    }

    /**
     * Uji klausul transparansi resmi dan tanggal diperbarui selalu tampil di halaman (DoD SPS).
     */
    public function test_restock_page_displays_official_disclaimer_and_updated_date(): void
    {
        Carbon::setTestNow('2026-11-14 15:30:00');

        Livewire::actingAs($this->user)
            ->test(RestockSuggestion::class)
            ->assertStatus(200)
            ->assertSee(RestockService::DISCLAIMER)
            ->assertSee('14 November 2026, 15:30 WIB')
            ->assertSee('Klausul Transparansi Sistem:');
    }

    /**
     * Uji penanganan saat seluruh bahan dalam siklus aman (> 2 hari lagi).
     */
    public function test_restock_page_handles_all_safe_items_gracefully(): void
    {
        Carbon::setTestNow('2026-11-12 10:00:00');

        // Sirup dibeli tiap 5 hari (1 Nov, 6 Nov, 11 Nov). Terakhir 11 Nov.
        // Jadwal: 16 Nov (masih 4 hari lagi dari 12 Nov -> Safe!)
        $this->createRawMaterialTransaction('2026-11-01', 'Sirup Vanilla', 2, 'botol', 30000);
        $this->createRawMaterialTransaction('2026-11-06', 'Sirup Vanilla', 2, 'botol', 30000);
        $this->createRawMaterialTransaction('2026-11-11', 'Sirup Vanilla', 2, 'botol', 30000);

        Livewire::actingAs($this->user)
            ->test(RestockSuggestion::class)
            ->assertStatus(200)
            ->assertSee('Semua Bahan Masih Dalam Siklus Aman')
            ->assertSee('Sirup Vanilla')
            ->assertSee('Aman')
            ->assertSee('0') // Recommended count = 0
            ->assertDontSee('Perlu Segera');
    }

    /**
     * Uji aksi refreshData dapat dipanggil dengan lancar via Livewire.
     */
    public function test_refresh_data_action_works_via_livewire(): void
    {
        Livewire::actingAs($this->user)
            ->test(RestockSuggestion::class)
            ->call('refreshData')
            ->assertStatus(200);
    }

    /**
     * Helper membuat transaksi belanja bahan baku.
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
