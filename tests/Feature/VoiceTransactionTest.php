<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VoiceTransactionTest extends TestCase
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
            'name' => 'Kedai Kopi Uji Coba',
            'business_type' => 'Coffee Shop',
            'description' => 'Kedai kopi untuk pengujian suara KasiVo',
        ]);
    }

    public function test_authenticated_user_can_access_voice_transaction_page(): void
    {
        $response = $this->actingAs($this->user)->get(route('transactions.expense'));

        $response->assertStatus(200);
        $response->assertSee('Catat Pengeluaran via Suara');
        $response->assertSee('Rekam Suara Langsung');
        $response->assertSee('Unggah File Audio');
        $response->assertSee('Tekan untuk Mulai Merekam');
        $response->assertSee('Pilih Berkas Audio dari Perangkat');
        $response->assertSee('audioRecorder');
    }

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get(route('transactions.expense'));

        $response->assertRedirect(route('login'));
    }

    public function test_select_transaction_type_links_to_voice_expense_page(): void
    {
        $response = $this->actingAs($this->user)->get(route('transactions.create'));

        $response->assertStatus(200);
        $response->assertSee(route('transactions.expense'));
        $response->assertSee('Catat Pengeluaran');
    }
}

