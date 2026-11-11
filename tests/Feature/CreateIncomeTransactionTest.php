<?php

namespace Tests\Feature;

use App\Livewire\CreateIncomeTransaction;
use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CreateIncomeTransactionTest extends TestCase
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

    public function test_authenticated_user_can_access_create_income_page(): void
    {
        $response = $this->actingAs($this->user)->get(route('transactions.income'));

        $response->assertStatus(200);
        $response->assertSee('Catat Penjualan Harian');
        $response->assertSee('Total Nominal Penjualan');
    }

    public function test_can_record_daily_sales_successfully(): void
    {
        Livewire::actingAs($this->user)
            ->test(CreateIncomeTransaction::class)
            ->set('amount', 750000)
            ->set('category', 'penjualan_tunai')
            ->set('transaction_date', now()->toDateString())
            ->set('notes', 'Omzet ramai hari minggu')
            ->call('save')
            ->assertRedirect(route('transactions.index'));

        $this->assertDatabaseHas('transactions', [
            'business_id' => $this->business->id,
            'type' => 'cash_in',
            'category' => 'penjualan_tunai',
            'amount' => 750000,
            'notes' => 'Omzet ramai hari minggu',
            'is_cash_settled' => true,
        ]);
    }

    public function test_validation_rejects_empty_or_negative_amount(): void
    {
        Livewire::actingAs($this->user)
            ->test(CreateIncomeTransaction::class)
            ->set('amount', 0)
            ->call('save')
            ->assertHasErrors(['amount' => 'min']);

        Livewire::actingAs($this->user)
            ->test(CreateIncomeTransaction::class)
            ->set('amount', -50000)
            ->call('save')
            ->assertHasErrors(['amount' => 'min']);
    }

    public function test_validation_rejects_future_transaction_date(): void
    {
        $tomorrow = now()->addDays(2)->toDateString();

        Livewire::actingAs($this->user)
            ->test(CreateIncomeTransaction::class)
            ->set('amount', 250000)
            ->set('transaction_date', $tomorrow)
            ->call('save')
            ->assertHasErrors(['transaction_date' => 'before_or_equal']);
    }

    public function test_validation_rejects_invalid_category(): void
    {
        Livewire::actingAs($this->user)
            ->test(CreateIncomeTransaction::class)
            ->set('amount', 250000)
            ->set('category', 'kategori_palsu')
            ->call('save')
            ->assertHasErrors(['category' => 'in']);
    }

    public function test_quick_add_amount_pills_work(): void
    {
        Livewire::actingAs($this->user)
            ->test(CreateIncomeTransaction::class)
            ->call('addAmount', 50000)
            ->assertSet('amount', 50000)
            ->call('addAmount', 100000)
            ->assertSet('amount', 150000)
            ->call('setAmount', 0)
            ->assertSet('amount', 0);
    }
}

