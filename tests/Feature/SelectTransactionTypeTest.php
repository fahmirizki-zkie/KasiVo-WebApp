<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SelectTransactionTypeTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        Business::create([
            'user_id' => $this->user->id,
            'name' => 'Warung Mas Joko',
            'business_type' => 'Warung Makan',
        ]);
    }

    public function test_authenticated_user_can_access_select_transaction_type_page(): void
    {
        $response = $this->actingAs($this->user)->get(route('transactions.create'));

        $response->assertStatus(200);
        $response->assertSee('Mau Catat Apa Hari Ini?');
        $response->assertSee('Catat Pengeluaran');
        $response->assertSee('Catat Penjualan');
        $response->assertSee(route('transactions.expense'));
        $response->assertSee(route('transactions.income'));
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('transactions.create'));

        $response->assertRedirect(route('login'));
    }
}

