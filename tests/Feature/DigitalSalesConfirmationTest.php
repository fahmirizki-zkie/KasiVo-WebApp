<?php

namespace Tests\Feature;

use App\Livewire\ImportDigitalSales;
use App\Models\Attachment;
use App\Models\Business;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class DigitalSalesConfirmationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Business $business;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->user = User::factory()->create();
        $this->business = Business::create([
            'user_id' => $this->user->id,
            'name' => 'Kedai Kopi Niskala',
            'business_type' => 'fnb',
            'has_completed_onboarding' => true,
        ]);
    }

    public function test_confirming_digital_sales_saves_cash_in_transaction(): void
    {
        $this->actingAs($this->user);

        $file = UploadedFile::fake()->image('gofood_daily.png', 600, 600);

        Livewire::test(ImportDigitalSales::class)
            ->set('uploadedFile', $file)
            ->set('fileType', 'screenshot')
            ->set('step', 'review')
            ->set('parsedData', [
                'type' => 'cash_in',
                'category' => 'penjualan',
                'platform' => 'gofood',
                'transaction_date' => '2026-11-13',
                'total_amount' => 350000.0,
                'gross_amount' => 420000.0,
                'fee_amount' => 70000.0,
                'net_amount' => 350000.0,
                'order_count' => 14,
                'notes' => 'Rekap Penjualan GoFood 13 Nov 2026',
                'rows' => [
                    [
                        'date' => '2026-11-13',
                        'amount' => 350000.0,
                        'gross_amount' => 420000.0,
                        'fee_amount' => 70000.0,
                        'platform' => 'gofood',
                        'notes' => 'Rekap Penjualan GoFood 13 Nov 2026',
                        'approved' => true,
                    ],
                ],
            ])
            ->call('saveTransaction')
            ->assertRedirect(route('transactions.index'))
            ->assertSessionHas('message');

        $this->assertDatabaseHas('transactions', [
            'business_id' => $this->business->id,
            'type' => 'cash_in',
            'category' => 'penjualan',
            'amount' => 350000.0,
            'source_type' => 'digital_import',
            'notes' => 'Rekap Penjualan GoFood 13 Nov 2026',
        ]);

        $tx = Transaction::first();
        $this->assertNotNull($tx);
        $this->assertEquals('2026-11-13', $tx->transaction_date->toDateString());
        $this->assertDatabaseHas('attachments', [
            'transaction_id' => $tx->id,
            'source' => 'digital_import',
        ]);
    }

    public function test_row_approval_filtering_only_saves_approved_rows(): void
    {
        $this->actingAs($this->user);

        $file = UploadedFile::fake()->create('rekap_multidate.csv', 500, 'text/csv');

        $component = Livewire::test(ImportDigitalSales::class)
            ->set('uploadedFile', $file)
            ->set('fileType', 'spreadsheet')
            ->set('step', 'review')
            ->set('parsedData', [
                'type' => 'cash_in',
                'category' => 'penjualan',
                'platform' => 'qris',
                'transaction_date' => '2026-11-13',
                'total_amount' => 450000.0,
                'notes' => 'Impor QRIS 3 Baris',
                'rows' => [
                    ['date' => '2026-11-11', 'amount' => 100000.0, 'approved' => true],
                    ['date' => '2026-11-12', 'amount' => 150000.0, 'approved' => true],
                    ['date' => '2026-11-13', 'amount' => 200000.0, 'approved' => true],
                ],
            ]);

        // Unapprove baris ke-2 (index 2: nominal 200.000)
        $component->call('toggleRowApproval', 2);

        // Total sekarang harusnya 250.000 (100.000 + 150.000)
        $component->assertSet('parsedData.total_amount', 250000.0);
        $component->assertSet('parsedData.order_count', 2);

        // Simpan
        $component->call('saveTransaction')
            ->assertRedirect(route('transactions.index'));

        // Cek database hanya menyimpan 250.000 sesuai baris yang disetujui (DoD KV-037)
        $this->assertDatabaseHas('transactions', [
            'business_id' => $this->business->id,
            'amount' => 250000.0,
            'type' => 'cash_in',
            'source_type' => 'digital_import',
        ]);
    }

    public function test_saving_with_no_approved_rows_fails_validation(): void
    {
        $this->actingAs($this->user);

        Livewire::test(ImportDigitalSales::class)
            ->set('step', 'review')
            ->set('parsedData', [
                'type' => 'cash_in',
                'category' => 'penjualan',
                'platform' => 'other',
                'transaction_date' => '2026-11-13',
                'total_amount' => 200000.0,
                'rows' => [
                    ['date' => '2026-11-13', 'amount' => 200000.0, 'approved' => true],
                ],
            ])
            ->call('toggleSelectAll') // Semua dibatalkan
            ->call('saveTransaction')
            ->assertSet('errorMessage', 'Tidak ada baris transaksi yang disetujui. Centang setidaknya satu baris transaksi untuk disimpan.');

        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_duplicate_transaction_within_10_minutes_triggers_warning_modal(): void
    {
        $this->actingAs($this->user);

        // Buat transaksi identik 3 menit lalu
        $this->business->transactions()->create([
            'type' => 'cash_in',
            'category' => 'penjualan',
            'amount' => 500000.0,
            'transaction_date' => now()->toDateString(),
            'source_type' => 'digital_import',
            'notes' => 'Impor QRIS Siang',
            'is_cash_settled' => true,
            'created_at' => now()->subMinutes(3),
        ]);

        Livewire::test(ImportDigitalSales::class)
            ->set('step', 'review')
            ->set('parsedData', [
                'type' => 'cash_in',
                'category' => 'penjualan',
                'platform' => 'qris',
                'transaction_date' => now()->toDateString(),
                'total_amount' => 500000.0,
                'notes' => 'Impor QRIS Siang',
            ])
            ->call('saveTransaction')
            ->assertSet('showDuplicateModal', true)
            ->assertSet('duplicateInfo.formatted_amount', 'Rp 500.000');

        // Transaksi tidak bertambah (masih 1)
        $this->assertDatabaseCount('transactions', 1);
    }

    public function test_confirming_duplicate_modal_forces_save(): void
    {
        $this->actingAs($this->user);

        $this->business->transactions()->create([
            'type' => 'cash_in',
            'category' => 'penjualan',
            'amount' => 500000.0,
            'transaction_date' => now()->toDateString(),
            'source_type' => 'digital_import',
            'notes' => 'Impor QRIS Siang',
            'is_cash_settled' => true,
            'created_at' => now()->subMinutes(3),
        ]);

        Livewire::test(ImportDigitalSales::class)
            ->set('step', 'review')
            ->set('parsedData', [
                'type' => 'cash_in',
                'category' => 'penjualan',
                'platform' => 'qris',
                'transaction_date' => now()->toDateString(),
                'total_amount' => 500000.0,
                'notes' => 'Impor QRIS Siang Dobel',
            ])
            ->call('saveTransaction')
            ->assertSet('showDuplicateModal', true)
            ->call('confirmDuplicateAndSave')
            ->assertRedirect(route('transactions.index'));

        // Sekarang bertambah jadi 2 karena dipaksa simpan
        $this->assertDatabaseCount('transactions', 2);
    }

    public function test_dismissing_duplicate_modal_cancels_save(): void
    {
        $this->actingAs($this->user);

        $this->business->transactions()->create([
            'type' => 'cash_in',
            'category' => 'penjualan',
            'amount' => 500000.0,
            'transaction_date' => now()->toDateString(),
            'source_type' => 'digital_import',
            'notes' => 'Impor QRIS Siang',
            'is_cash_settled' => true,
            'created_at' => now()->subMinutes(3),
        ]);

        Livewire::test(ImportDigitalSales::class)
            ->set('step', 'review')
            ->set('parsedData', [
                'type' => 'cash_in',
                'category' => 'penjualan',
                'platform' => 'qris',
                'transaction_date' => now()->toDateString(),
                'total_amount' => 500000.0,
                'notes' => 'Impor QRIS Siang Dobel',
            ])
            ->call('saveTransaction')
            ->assertSet('showDuplicateModal', true)
            ->call('dismissDuplicateModal')
            ->assertSet('showDuplicateModal', false);

        // Tetap hanya 1 transaksi
        $this->assertDatabaseCount('transactions', 1);
    }

    public function test_atomic_lock_prevents_concurrent_double_submission(): void
    {
        $this->actingAs($this->user);

        // Kunci lock sebelum dipanggil
        $lockKey = "import_digital_sales_lock_{$this->business->id}";
        $lock = Cache::lock($lockKey, 5);
        $lock->get();

        Livewire::test(ImportDigitalSales::class)
            ->set('step', 'review')
            ->set('parsedData', [
                'type' => 'cash_in',
                'category' => 'penjualan',
                'platform' => 'pos',
                'transaction_date' => now()->toDateString(),
                'total_amount' => 150000.0,
            ])
            ->call('saveTransaction');

        $this->assertDatabaseCount('transactions', 0);

        $lock->release();
    }
}
