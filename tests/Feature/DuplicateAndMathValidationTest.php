<?php

namespace Tests\Feature;

use App\Livewire\CreateExpenseTextTransaction;
use App\Models\Business;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DuplicateAndMathValidationTest extends TestCase
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

    public function test_it_intercepts_duplicate_transaction_and_shows_warning_modal(): void
    {
        // 1. Buat transaksi pertama 2 menit yang lalu
        $tx = Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_out',
            'category' => 'bahan_baku',
            'amount' => 85000,
            'transaction_date' => now()->toDateString(),
            'source_type' => 'text',
            'notes' => 'Beli Biji Kopi',
            'is_cash_settled' => true,
        ]);
        $tx->created_at = now()->subMinutes(2);
        $tx->saveQuietly();

        $this->assertEquals(1, Transaction::where('business_id', $this->business->id)->count());

        // 2. Coba simpan transaksi yang sama persis
        Livewire::actingAs($this->user)
            ->test(CreateExpenseTextTransaction::class)
            ->set('step', 'review')
            ->set('draft', [
                'type' => 'cash_out',
                'category' => 'bahan_baku',
                'amount' => 85000,
                'transaction_date' => now()->toDateString(),
                'merchant_name' => 'Toko Kopi',
                'notes' => 'Beli Biji Kopi Lagi',
                'items' => [],
            ])
            ->call('saveTransaction')
            ->assertSet('showDuplicateModal', true)
            ->assertSet('duplicateConfirmed', false)
            ->assertSee('Tunggu Sebentar, Cek Dulu!')
            ->assertSee('Batalkan, Tadi Saya Lupa Sudah Catat');

        // 3. Pastikan database TIDAK bertambah (tetap 1 baris)
        $this->assertEquals(1, Transaction::where('business_id', $this->business->id)->count());
    }

    public function test_user_can_dismiss_duplicate_modal_without_saving(): void
    {
        $tx = Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_out',
            'category' => 'bahan_baku',
            'amount' => 85000,
            'transaction_date' => now()->toDateString(),
            'source_type' => 'text',
            'is_cash_settled' => true,
        ]);
        $tx->created_at = now()->subMinutes(3);
        $tx->saveQuietly();

        Livewire::actingAs($this->user)
            ->test(CreateExpenseTextTransaction::class)
            ->set('step', 'review')
            ->set('draft', [
                'type' => 'cash_out',
                'category' => 'bahan_baku',
                'amount' => 85000,
                'transaction_date' => now()->toDateString(),
                'notes' => 'Duplikat',
                'items' => [],
            ])
            ->call('saveTransaction')
            ->assertSet('showDuplicateModal', true)
            ->call('dismissDuplicateModal')
            ->assertSet('showDuplicateModal', false);

        // Data di DB tetap 1
        $this->assertEquals(1, Transaction::where('business_id', $this->business->id)->count());
    }

    public function test_user_can_confirm_legitimate_duplicate_and_save(): void
    {
        $tx = Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_out',
            'category' => 'bahan_baku',
            'amount' => 50000,
            'transaction_date' => now()->toDateString(),
            'source_type' => 'text',
            'is_cash_settled' => true,
        ]);
        $tx->created_at = now()->subMinutes(1);
        $tx->saveQuietly();

        Livewire::actingAs($this->user)
            ->test(CreateExpenseTextTransaction::class)
            ->set('step', 'review')
            ->set('draft', [
                'type' => 'cash_out',
                'category' => 'bahan_baku',
                'amount' => 50000,
                'transaction_date' => now()->toDateString(),
                'notes' => 'Beli Telur Tambahan',
                'items' => [],
            ])
            ->call('saveTransaction')
            ->assertSet('showDuplicateModal', true)
            ->call('confirmDuplicateAndSave')
            ->assertRedirect(route('transactions.index'));

        // Data di DB bertambah menjadi 2 transaksi
        $this->assertEquals(2, Transaction::where('business_id', $this->business->id)->count());
    }

    public function test_sync_total_with_items_sum_updates_amount_correctly(): void
    {
        Livewire::actingAs($this->user)
            ->test(CreateExpenseTextTransaction::class)
            ->set('step', 'review')
            ->set('draft', [
                'type' => 'cash_out',
                'category' => 'bahan_baku',
                'amount' => 40000, // Selisih dengan total item 60.000
                'transaction_date' => now()->toDateString(),
                'notes' => 'Catatan Susu & Sirup',
                'items' => [
                    ['item_name' => 'Susu Segar', 'quantity' => 2, 'unit_price' => 20000, 'subtotal' => 40000],
                    ['item_name' => 'Sirup Karamel', 'quantity' => 1, 'unit_price' => 20000, 'subtotal' => 20000],
                ],
            ])
            ->call('recalculateMathReconciliation')
            ->assertSee('Total rincian barang berbeda dengan Total Transaksi')
            ->call('syncTotalWithItemsSum')
            ->assertSet('draft.amount', 60000)
            ->assertDontSee('Total rincian barang berbeda dengan Total Transaksi');
    }

    public function test_payload_capping_rejects_more_than_50_items(): void
    {
        $tooManyItems = [];
        for ($i = 1; $i <= 55; $i++) {
            $tooManyItems[] = [
                'item_name' => "Barang {$i}",
                'quantity' => 1,
                'unit_price' => 1000,
                'subtotal' => 1000,
            ];
        }

        Livewire::actingAs($this->user)
            ->test(CreateExpenseTextTransaction::class)
            ->set('draft', [
                'type' => 'cash_out',
                'category' => 'bahan_baku',
                'amount' => 55000,
                'transaction_date' => now()->toDateString(),
                'notes' => 'Payload Bombing Attempt',
                'items' => $tooManyItems,
            ])
            ->call('saveTransaction')
            ->assertHasErrors(['draft.items' => 'max']);

        $this->assertEquals(0, Transaction::where('business_id', $this->business->id)->count());
    }
}
