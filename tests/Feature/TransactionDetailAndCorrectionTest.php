<?php

namespace Tests\Feature;

use App\Livewire\TransactionHistory;
use App\Models\Business;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TransactionDetailAndCorrectionTest extends TestCase
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
            'name' => 'Warung Mas Joko',
            'business_type' => 'Warung Makan',
        ]);
    }

    public function test_owner_can_view_transaction_detail_with_items(): void
    {
        $transaction = Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_out',
            'category' => 'bahan_baku',
            'amount' => 150000,
            'transaction_date' => now()->toDateString(),
            'source_type' => 'photo',
            'notes' => 'Belanja Daging Ayam Segar',
            'is_cash_settled' => true,
        ]);

        TransactionItem::create([
            'transaction_id' => $transaction->id,
            'item_name' => 'Daging Ayam Broiler',
            'quantity' => 4,
            'unit' => 'kg',
            'price_per_unit' => 37500,
            'subtotal' => 150000,
        ]);

        Livewire::actingAs($this->user)
            ->test(TransactionHistory::class)
            ->call('showDetail', $transaction->id)
            ->assertSet('selectedTransactionId', $transaction->id)
            ->assertSee('Rincian Transaksi')
            ->assertSee('Belanja Daging Ayam Segar')
            ->assertSee('Daging Ayam Broiler')
            ->assertSee('Rp 150.000');
    }

    public function test_owner_can_correct_transaction_data(): void
    {
        $transaction = Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_in',
            'category' => 'penjualan_tunai',
            'amount' => 200000,
            'transaction_date' => now()->toDateString(),
            'source_type' => 'text',
            'notes' => 'Catatan awal',
            'is_cash_settled' => true,
        ]);

        Livewire::actingAs($this->user)
            ->test(TransactionHistory::class)
            ->call('showDetail', $transaction->id)
            ->call('startEditing')
            ->assertSet('isEditing', true)
            ->set('edit_amount', 350000)
            ->set('edit_category', 'penjualan_qris')
            ->set('edit_notes', 'Dikoreksi: omzet sebenarnya termasuk pesanan catering')
            ->call('updateTransaction')
            ->assertSet('isEditing', false)
            ->assertSee('Transaksi berhasil dikoreksi');

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'amount' => 350000,
            'category' => 'penjualan_qris',
            'notes' => 'Dikoreksi: omzet sebenarnya termasuk pesanan catering',
        ]);
    }

    public function test_correction_validation_rejects_invalid_amount_and_future_date(): void
    {
        $transaction = Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_out',
            'category' => 'operasional',
            'amount' => 50000,
            'transaction_date' => now()->toDateString(),
            'source_type' => 'text',
            'is_cash_settled' => true,
        ]);

        $tomorrow = now()->addDay()->toDateString();

        Livewire::actingAs($this->user)
            ->test(TransactionHistory::class)
            ->call('showDetail', $transaction->id)
            ->call('startEditing')
            ->set('edit_amount', -5000)
            ->set('edit_transaction_date', $tomorrow)
            ->call('updateTransaction')
            ->assertHasErrors(['edit_amount' => 'min', 'edit_transaction_date' => 'before_or_equal']);
    }

    public function test_user_cannot_view_or_correct_other_business_transaction(): void
    {
        $otherUser = User::factory()->create();
        $otherBusiness = Business::create([
            'user_id' => $otherUser->id,
            'name' => 'Warung Lain',
            'business_type' => 'Kedai Kopi',
        ]);

        $otherTransaction = Transaction::create([
            'business_id' => $otherBusiness->id,
            'type' => 'cash_in',
            'category' => 'penjualan_tunai',
            'amount' => 1000000,
            'transaction_date' => now()->toDateString(),
            'source_type' => 'text',
            'notes' => 'Rahasia Warung Lain',
            'is_cash_settled' => true,
        ]);

        $this->expectException(ModelNotFoundException::class);

        Livewire::actingAs($this->user)
            ->test(TransactionHistory::class)
            ->call('showDetail', $otherTransaction->id);
    }
}

