<?php

namespace Tests\Feature;

use App\Livewire\TransactionHistory;
use App\Models\Business;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TransactionHistoryTest extends TestCase
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
            'name' => 'Warung Uji Coba',
            'business_type' => 'Warung Makan',
            'description' => 'Warung untuk automated testing',
        ]);
    }

    public function test_authenticated_user_can_access_transaction_history_page(): void
    {
        Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_in',
            'source_type' => 'text',
            'amount' => 500000,
            'category' => 'penjualan_tunai',
            'notes' => 'Penjualan Siang Hari',
            'transaction_date' => now()->toDateString(),
            'is_cash_settled' => true,
        ]);

        $response = $this->actingAs($this->user)->get(route('transactions.index'));

        $response->assertStatus(200);
        $response->assertSee('Riwayat Transaksi');
        $response->assertSee('Penjualan Siang Hari');
        $response->assertSee('Rp 500.000');
    }

    public function test_user_only_sees_transactions_belonging_to_their_business(): void
    {
        // Transaksi milik bisnis user pertama
        Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_in',
            'source_type' => 'text',
            'amount' => 150000,
            'category' => 'penjualan_tunai',
            'notes' => 'Transaksi Rahasia Warung A',
            'transaction_date' => now()->toDateString(),
            'is_cash_settled' => true,
        ]);

        // Bisnis & user lain
        $otherUser = User::factory()->create();
        $otherBusiness = Business::create([
            'user_id' => $otherUser->id,
            'name' => 'Warung Kompetitor',
            'business_type' => 'Kedai Kopi',
        ]);

        Transaction::create([
            'business_id' => $otherBusiness->id,
            'type' => 'cash_out',
            'source_type' => 'text',
            'amount' => 999000,
            'category' => 'bahan_baku',
            'notes' => 'Belanja Rahasia Warung B',
            'transaction_date' => now()->toDateString(),
            'is_cash_settled' => true,
        ]);

        // User A membuka riwayatnya
        Livewire::actingAs($this->user)
            ->test(TransactionHistory::class)
            ->assertSee('Transaksi Rahasia Warung A')
            ->assertDontSee('Belanja Rahasia Warung B');
    }

    public function test_can_filter_transactions_by_type(): void
    {
        Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_in',
            'source_type' => 'text',
            'amount' => 300000,
            'category' => 'penjualan_tunai',
            'notes' => 'Uang Masuk Penjualan',
            'transaction_date' => now()->toDateString(),
            'is_cash_settled' => true,
        ]);

        Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_out',
            'source_type' => 'photo',
            'amount' => 75000,
            'category' => 'bahan_baku',
            'notes' => 'Uang Keluar Beli Telur',
            'transaction_date' => now()->toDateString(),
            'is_cash_settled' => true,
        ]);

        // Filter hanya cash_out
        Livewire::actingAs($this->user)
            ->test(TransactionHistory::class)
            ->set('type', 'cash_out')
            ->assertSee('Uang Keluar Beli Telur')
            ->assertDontSee('Uang Masuk Penjualan');
    }

    public function test_can_search_transactions_by_description(): void
    {
        Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_out',
            'source_type' => 'photo',
            'amount' => 120000,
            'category' => 'bahan_baku',
            'notes' => 'Beli Beras Rojo Lele 10kg',
            'transaction_date' => now()->toDateString(),
            'is_cash_settled' => true,
        ]);

        Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_out',
            'source_type' => 'photo',
            'amount' => 50000,
            'category' => 'bahan_baku',
            'notes' => 'Beli Minyak Goreng 2L',
            'transaction_date' => now()->toDateString(),
            'is_cash_settled' => true,
        ]);

        Livewire::actingAs($this->user)
            ->test(TransactionHistory::class)
            ->set('search', 'Rojo Lele')
            ->assertSee('Beli Beras Rojo Lele 10kg')
            ->assertDontSee('Beli Minyak Goreng 2L');
    }

    public function test_can_delete_transaction_with_confirmation(): void
    {
        $transaction = Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_out',
            'source_type' => 'text',
            'amount' => 60000,
            'category' => 'operasional',
            'notes' => 'Gas Elpiji 3kg',
            'transaction_date' => now()->toDateString(),
            'is_cash_settled' => true,
        ]);

        $this->assertDatabaseHas('transactions', ['id' => $transaction->id]);

        Livewire::actingAs($this->user)
            ->test(TransactionHistory::class)
            ->call('confirmDelete', $transaction->id)
            ->assertSet('confirmingDeletionId', $transaction->id)
            ->call('deleteTransaction')
            ->assertSet('confirmingDeletionId', null)
            ->assertSee('Transaksi berhasil dihapus');

        $this->assertDatabaseMissing('transactions', ['id' => $transaction->id]);
    }
}
