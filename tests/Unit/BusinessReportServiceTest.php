<?php

namespace Tests\Unit;

use App\Models\Business;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use App\Services\BusinessReportService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class BusinessReportServiceTest extends TestCase
{
    use RefreshDatabase;

    private BusinessReportService $service;
    private User $user;
    private Business $business;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new BusinessReportService();

        $this->user = User::factory()->create([
            'name' => 'Fahmi Founder',
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
     * Uji penyiapan data laporan profil usaha dan angka arus kas akurat (DoD KV-050).
     */
    public function test_prepares_business_profile_report_data_accurately(): void
    {
        Carbon::setTestNow('2026-11-14 10:00:00');

        // 1. Kas Masuk 1.500.000
        Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_in',
            'category' => 'penjualan',
            'amount' => 1500000,
            'transaction_date' => '2026-11-10',
            'source_type' => 'text',
            'is_cash_settled' => true,
        ]);

        // 2. Kas Keluar Bahan Baku 500.000
        $txOut1 = Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_out',
            'category' => 'bahan_baku',
            'amount' => 500000,
            'transaction_date' => '2026-11-11',
            'source_type' => 'text',
            'is_cash_settled' => true,
        ]);
        TransactionItem::create([
            'transaction_id' => $txOut1->id,
            'item_name' => 'Biji Kopi Arabika',
            'quantity' => 5,
            'unit' => 'kg',
            'unit_price' => 100000,
            'subtotal' => 500000,
        ]);

        // 3. Kas Keluar Operasional 200.000
        Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_out',
            'category' => 'operasional',
            'amount' => 200000,
            'transaction_date' => '2026-11-12',
            'source_type' => 'text',
            'is_cash_settled' => true,
        ]);

        $data = $this->service->getProfileReportData($this->business, 'this_month');

        // 1. Identitas Usaha & Pemilik
        $this->assertEquals('Waroeng Kopi Sedap', $data['business']['name']);
        $this->assertEquals('Fahmi Founder', $data['business']['owner_name']);
        $this->assertEquals('fahmi@waroeng.test', $data['business']['owner_email']);
        $this->assertStringContainsString('Kuliner / F&B', $data['business']['business_type_label']);

        // 2. Angka Arus Kas Sesuai CashFlowService
        $this->assertEquals(1500000.0, $data['cash_flow']['total_cash_in']);
        $this->assertEquals(700000.0, $data['cash_flow']['total_cash_out']);
        $this->assertEquals(800000.0, $data['cash_flow']['net_cash_flow']);
        $this->assertFalse($data['cash_flow']['is_negative']);
        $this->assertEquals(1, $data['cash_flow']['cash_in_count']);
        $this->assertEquals(2, $data['cash_flow']['cash_out_count']);
        $this->assertEquals(3, $data['cash_flow']['total_transactions']);

        // 3. Rincian Kategori
        $this->assertNotEmpty($data['categories']['cash_in']);
        $this->assertNotEmpty($data['categories']['cash_out']);

        // 4. Disclaimer Kepatuhan
        $this->assertEquals(BusinessReportService::DISCLAIMER, $data['disclaimer']);
    }

    /**
     * Uji kepatuhan mutlak DoD KV-050: Tidak menyebut laporan sebagai bukti kelayakan kredit.
     */
    public function test_disclaimer_strictly_does_not_claim_credit_worthiness(): void
    {
        // Assert klausul disclaimer secara eksplisit menolak klaim kelayakan kredit
        $this->assertStringContainsString('BUKAN merupakan surat jaminan, rekomendasi pinjaman, atau bukti kelayakan kredit', BusinessReportService::DISCLAIMER);
        $this->assertStringContainsString('pencatatan transaksi mandiri oleh pemilik usaha', BusinessReportService::DISCLAIMER);

        $data = $this->service->getProfileReportData($this->business, 'this_month');
        $this->assertStringContainsString('BUKAN merupakan surat jaminan', $data['disclaimer']);
    }

    /**
     * Uji view Blade template PDF (resources/views/pdf/business-profile.blade.php) berhasil dirender.
     */
    public function test_blade_template_renders_cleanly_without_errors(): void
    {
        Carbon::setTestNow('2026-11-14 10:00:00');

        Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_in',
            'category' => 'penjualan',
            'amount' => 1000000,
            'transaction_date' => '2026-11-10',
            'source_type' => 'text',
            'is_cash_settled' => true,
        ]);

        Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_out',
            'category' => 'bahan_baku',
            'amount' => 400000,
            'transaction_date' => '2026-11-11',
            'source_type' => 'text',
            'is_cash_settled' => true,
        ]);

        $data = $this->service->getProfileReportData($this->business, 'this_month');

        $renderedHtml = View::make('pdf.business-profile', $data)->render();

        $this->assertStringContainsString('Waroeng Kopi Sedap', $renderedHtml);
        $this->assertStringContainsString('Fahmi Founder', $renderedHtml);
        $this->assertStringContainsString('Rp 1.000.000', $renderedHtml);
        $this->assertStringContainsString('Rp 400.000', $renderedHtml);
        $this->assertStringContainsString('Rp 600.000', $renderedHtml);
        $this->assertStringContainsString('Pernyataan Kepatuhan & Batasan Dokumen', $renderedHtml);
        $this->assertStringContainsString('BUKAN merupakan surat jaminan, rekomendasi pinjaman, atau bukti kelayakan kredit', $renderedHtml);
    }

    /**
     * Uji isolasi data antar usaha (Multi-Tenant Isolation).
     */
    public function test_multi_tenant_isolation_in_report_data(): void
    {
        $otherUser = User::factory()->create();
        $otherBusiness = Business::create([
            'user_id' => $otherUser->id,
            'name' => 'Kedai Kopi Lain',
            'business_type' => 'food_beverage',
        ]);

        // Transaksi usaha lain (10.000.000)
        Transaction::create([
            'business_id' => $otherBusiness->id,
            'type' => 'cash_in',
            'category' => 'penjualan',
            'amount' => 10000000,
            'transaction_date' => '2026-11-10',
            'source_type' => 'text',
            'is_cash_settled' => true,
        ]);

        // Transaksi usaha milik Fahmi (500.000)
        Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_in',
            'category' => 'penjualan',
            'amount' => 500000,
            'transaction_date' => '2026-11-10',
            'source_type' => 'text',
            'is_cash_settled' => true,
        ]);

        $data = $this->service->getProfileReportData($this->business, 'this_month');

        $this->assertEquals(500000.0, $data['cash_flow']['total_cash_in']);
        $this->assertNotEquals(10500000.0, $data['cash_flow']['total_cash_in']);
        $this->assertNotEquals(10000000.0, $data['cash_flow']['total_cash_in']);
    }

    /**
     * Uji keadaan arus kas negatif dilabeli secara objektif (Defisit Arus Kas).
     */
    public function test_negative_cash_flow_state_is_labeled_without_derogatory_language(): void
    {
        Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_out',
            'category' => 'bahan_baku',
            'amount' => 500000,
            'transaction_date' => '2026-11-10',
            'source_type' => 'text',
            'is_cash_settled' => true,
        ]);

        $data = $this->service->getProfileReportData($this->business, 'this_month');

        $this->assertTrue($data['cash_flow']['is_negative']);
        $this->assertEquals(-500000.0, $data['cash_flow']['net_cash_flow']);
        $this->assertStringContainsString('Defisit Arus Kas', $data['cash_flow']['status_label']);
    }
}

