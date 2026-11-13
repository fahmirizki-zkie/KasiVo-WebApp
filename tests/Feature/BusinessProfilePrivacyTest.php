<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use App\Services\BusinessReportService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessProfilePrivacyTest extends TestCase
{
    use RefreshDatabase;

    private BusinessReportService $reportService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reportService = app(BusinessReportService::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /**
     * Uji isolasi data multi-tenant ketat: Data transaksi Bisnis A tidak boleh bocor ke Bisnis B atau C (DoD KV-053).
     */
    public function test_multi_tenant_isolation_strictly_excludes_other_businesses_data(): void
    {
        Carbon::setTestNow('2026-11-14 10:00:00');

        // Bisnis A: Kopi Kenangan Senja
        $userA = User::factory()->create(['name' => 'Fahmi Founder', 'email' => 'fahmi@senja.test']);
        $businessA = Business::create([
            'user_id' => $userA->id,
            'name' => 'Kopi Kenangan Senja',
            'business_type' => 'food_beverage',
        ]);
        Transaction::create([
            'business_id' => $businessA->id,
            'type' => 'cash_in',
            'category' => 'penjualan',
            'amount' => 5000000,
            'transaction_date' => '2026-11-10',
            'source_type' => 'text',
            'is_cash_settled' => true,
        ]);
        $txOutA = Transaction::create([
            'business_id' => $businessA->id,
            'type' => 'cash_out',
            'category' => 'bahan_baku',
            'amount' => 2000000,
            'transaction_date' => '2026-11-11',
            'source_type' => 'text',
            'is_cash_settled' => true,
        ]);
        TransactionItem::create([
            'transaction_id' => $txOutA->id,
            'item_name' => 'Biji Kopi Gayo',
            'quantity' => 10,
            'unit' => 'kg',
            'unit_price' => 200000,
            'subtotal' => 2000000,
        ]);

        // Bisnis B: Bakso Granat Mercon
        $userB = User::factory()->create(['name' => 'Budi Santoso', 'email' => 'budi@bakso.test']);
        $businessB = Business::create([
            'user_id' => $userB->id,
            'name' => 'Bakso Granat Mercon',
            'business_type' => 'food_beverage',
        ]);
        Transaction::create([
            'business_id' => $businessB->id,
            'type' => 'cash_in',
            'category' => 'penjualan',
            'amount' => 15000000,
            'transaction_date' => '2026-11-10',
            'source_type' => 'text',
            'is_cash_settled' => true,
        ]);
        $txOutB = Transaction::create([
            'business_id' => $businessB->id,
            'type' => 'cash_out',
            'category' => 'bahan_baku',
            'amount' => 8000000,
            'transaction_date' => '2026-11-11',
            'source_type' => 'text',
            'is_cash_settled' => true,
        ]);
        TransactionItem::create([
            'transaction_id' => $txOutB->id,
            'item_name' => 'Daging Sapi Segar',
            'quantity' => 40,
            'unit' => 'kg',
            'unit_price' => 200000,
            'subtotal' => 8000000,
        ]);

        // Bisnis C: Martabak Manis Legit
        $userC = User::factory()->create(['name' => 'Citra Lestari', 'email' => 'citra@martabak.test']);
        $businessC = Business::create([
            'user_id' => $userC->id,
            'name' => 'Martabak Manis Legit',
            'business_type' => 'food_beverage',
        ]);
        Transaction::create([
            'business_id' => $businessC->id,
            'type' => 'cash_in',
            'category' => 'penjualan',
            'amount' => 25000000,
            'transaction_date' => '2026-11-10',
            'source_type' => 'text',
            'is_cash_settled' => true,
        ]);

        // 1. Verifikasi Unduhan PDF User A
        $responseA = $this->actingAs($userA)->get(route('business-profile.download', ['period' => 'this_month']));
        $responseA->assertOk();
        $this->assertStringContainsString('kopi-kenangan-senja', $responseA->headers->get('Content-Disposition'));

        $dataA = $this->reportService->getProfileReportData($businessA, 'this_month');
        $this->assertEquals(5000000.0, $dataA['cash_flow']['total_cash_in']);
        $this->assertEquals(2000000.0, $dataA['cash_flow']['total_cash_out']);
        $this->assertEquals(3000000.0, $dataA['cash_flow']['net_cash_flow']);

        $renderedHtmlA = view('pdf.business-profile', $this->reportService->getCompleteProfileReportWithAi($businessA, 'this_month'))->render();
        $this->assertStringContainsString('Kopi Kenangan Senja', $renderedHtmlA);
        $this->assertStringContainsString('Rp 5.000.000', $renderedHtmlA);
        $this->assertStringContainsString('Rp 2.000.000', $renderedHtmlA);
        $this->assertStringContainsString('Rp 3.000.000', $renderedHtmlA);

        // Pastikan TIDAK ADA data Bisnis B atau C sama sekali di dokumen Bisnis A
        $this->assertStringNotContainsString('Bakso Granat Mercon', $renderedHtmlA);
        $this->assertStringNotContainsString('15.000.000', $renderedHtmlA);
        $this->assertStringNotContainsString('8.000.000', $renderedHtmlA);
        $this->assertStringNotContainsString('Budi Santoso', $renderedHtmlA);
        $this->assertStringNotContainsString('budi@bakso.test', $renderedHtmlA);
        $this->assertStringNotContainsString('Martabak Manis Legit', $renderedHtmlA);
        $this->assertStringNotContainsString('25.000.000', $renderedHtmlA);

        // 2. Verifikasi Unduhan PDF User B
        $responseB = $this->actingAs($userB)->get(route('business-profile.download', ['period' => 'this_month']));
        $responseB->assertOk();
        $this->assertStringContainsString('bakso-granat-mercon', $responseB->headers->get('Content-Disposition'));

        $dataB = $this->reportService->getProfileReportData($businessB, 'this_month');
        $this->assertEquals(15000000.0, $dataB['cash_flow']['total_cash_in']);
        $this->assertEquals(8000000.0, $dataB['cash_flow']['total_cash_out']);
        $this->assertEquals(7000000.0, $dataB['cash_flow']['net_cash_flow']);

        $renderedHtmlB = view('pdf.business-profile', $this->reportService->getCompleteProfileReportWithAi($businessB, 'this_month'))->render();
        $this->assertStringContainsString('Bakso Granat Mercon', $renderedHtmlB);
        $this->assertStringContainsString('Rp 15.000.000', $renderedHtmlB);
        $this->assertStringContainsString('Rp 8.000.000', $renderedHtmlB);

        // Pastikan TIDAK ADA data Bisnis A atau C sama sekali di dokumen Bisnis B
        $this->assertStringNotContainsString('Kopi Kenangan Senja', $renderedHtmlB);
        $this->assertStringNotContainsString('Rp 5.000.000', $renderedHtmlB);
        $this->assertStringNotContainsString('Fahmi Founder', $renderedHtmlB);
        $this->assertStringNotContainsString('fahmi@senja.test', $renderedHtmlB);
    }

    /**
     * Uji pencegahan celah IDOR: Pengguna tidak bisa memanipulasi kueri untuk mengunduh bisnis milik orang lain.
     */
    public function test_idor_prevention_user_cannot_access_or_inject_other_business_id(): void
    {
        Carbon::setTestNow('2026-11-14 10:00:00');

        $userVictim = User::factory()->create(['name' => 'Korban IDOR']);
        $businessVictim = Business::create([
            'user_id' => $userVictim->id,
            'name' => 'Usaha Rahasia Korban',
            'business_type' => 'food_beverage',
        ]);
        Transaction::create([
            'business_id' => $businessVictim->id,
            'type' => 'cash_in',
            'category' => 'penjualan',
            'amount' => 77000000,
            'transaction_date' => '2026-11-10',
            'source_type' => 'text',
            'is_cash_settled' => true,
        ]);

        $userAttacker = User::factory()->create(['name' => 'Penyerang IDOR']);
        $businessAttacker = Business::create([
            'user_id' => $userAttacker->id,
            'name' => 'Usaha Milik Penyerang',
            'business_type' => 'retail',
        ]);
        Transaction::create([
            'business_id' => $businessAttacker->id,
            'type' => 'cash_in',
            'category' => 'penjualan',
            'amount' => 100000,
            'transaction_date' => '2026-11-10',
            'source_type' => 'text',
            'is_cash_settled' => true,
        ]);

        // Penyerang mencoba menginjeksi business_id milik korban
        $response = $this->actingAs($userAttacker)->get(route('business-profile.download', [
            'period' => 'this_month',
            'business_id' => $businessVictim->id,
        ]));

        $response->assertOk();
        // File yang diunduh HARUS tetap milik penyerang sendiri, BUKAN milik korban
        $this->assertStringContainsString('usaha-milik-penyerang', $response->headers->get('Content-Disposition'));
        $this->assertStringNotContainsString('usaha-rahasia-korban', $response->headers->get('Content-Disposition'));

        // Cek data internal penyerang
        $reportData = $this->reportService->getProfileReportData($businessAttacker, 'this_month');
        $this->assertEquals(100000.0, $reportData['cash_flow']['total_cash_in']);
        $this->assertNotEquals(77000000.0, $reportData['cash_flow']['total_cash_in']);
    }

    /**
     * Uji atribut sensitif sistem (password hash, remember token) tidak pernah terpapar ke dalam dokumen PDF.
     */
    public function test_sensitive_attributes_are_never_exposed_in_pdf_report(): void
    {
        Carbon::setTestNow('2026-11-14 10:00:00');

        $user = User::factory()->create([
            'name' => 'Fahmi Founder',
            'email' => 'fahmi@waroeng.test',
            'password' => bcrypt('SuperSecretPassword123!'),
            'remember_token' => 'SecretRememberToken999',
        ]);

        $business = Business::create([
            'user_id' => $user->id,
            'name' => 'Waroeng Bersih Rahasia',
            'business_type' => 'food_beverage',
        ]);

        $reportData = $this->reportService->getProfileReportData($business, 'this_month');

        // Pastikan array business hanya berisi data publik yang diizinkan
        $this->assertArrayNotHasKey('password', $reportData['business']);
        $this->assertArrayNotHasKey('remember_token', $reportData['business']);
        $this->assertArrayNotHasKey('two_factor_secret', $reportData['business']);

        $renderedHtml = view('pdf.business-profile', $this->reportService->getCompleteProfileReportWithAi($business, 'this_month'))->render();

        // Pastikan password hash dan remember token TIDAK PERNAH tercetak di HTML / PDF
        $this->assertStringNotContainsString($user->password, $renderedHtml);
        $this->assertStringNotContainsString('SecretRememberToken999', $renderedHtml);
        $this->assertStringNotContainsString('SuperSecretPassword123!', $renderedHtml);
    }

    /**
     * Uji penegakan penafian kredit: Laporan wajib menyertakan klausul disclaimer resmi (DoD KV-050 & KV-053).
     */
    public function test_mandatory_compliance_disclaimer_is_strictly_rendered_on_every_pdf(): void
    {
        Carbon::setTestNow('2026-11-14 10:00:00');

        $user = User::factory()->create();
        $business = Business::create([
            'user_id' => $user->id,
            'name' => 'Kedai Kopi Uji Kepatuhan',
            'business_type' => 'food_beverage',
        ]);

        $reportWithAi = $this->reportService->getCompleteProfileReportWithAi($business, 'this_month');
        $renderedHtml = view('pdf.business-profile', $reportWithAi)->render();

        // Verifikasi disclaimer hadir utuh
        $this->assertStringContainsString(BusinessReportService::DISCLAIMER, $renderedHtml);
        $this->assertStringContainsString('BUKAN merupakan surat jaminan, rekomendasi pinjaman, atau bukti kelayakan kredit', $renderedHtml);

        // Verifikasi tidak ada frasa spekulatif kelayakan kredit
        $this->assertStringNotContainsString('layak menerima pinjaman modal bank', $renderedHtml);
        $this->assertStringNotContainsString('rekomendasi kredit disetujui', $renderedHtml);
    }

    /**
     * Uji isolasi rentang tanggal periode: Transaksi di luar rentang tanggal tidak boleh bocor ke laporan.
     */
    public function test_period_date_boundaries_do_not_leak_future_or_past_transactions(): void
    {
        Carbon::setTestNow('2026-11-14 10:00:00');

        $user = User::factory()->create();
        $business = Business::create([
            'user_id' => $user->id,
            'name' => 'Waroeng Batas Tanggal',
            'business_type' => 'food_beverage',
        ]);

        // Transaksi Bulan Lalu (Oktober 2026): Rp 10.000.000
        Transaction::create([
            'business_id' => $business->id,
            'type' => 'cash_in',
            'category' => 'penjualan',
            'amount' => 10000000,
            'transaction_date' => '2026-10-15',
            'source_type' => 'text',
            'is_cash_settled' => true,
        ]);

        // Transaksi Bulan Ini (November 2026): Rp 2.000.000
        Transaction::create([
            'business_id' => $business->id,
            'type' => 'cash_in',
            'category' => 'penjualan',
            'amount' => 2000000,
            'transaction_date' => '2026-11-10',
            'source_type' => 'text',
            'is_cash_settled' => true,
        ]);

        // Transaksi Masa Depan (Desember 2026): Rp 50.000.000
        Transaction::create([
            'business_id' => $business->id,
            'type' => 'cash_in',
            'category' => 'penjualan',
            'amount' => 50000000,
            'transaction_date' => '2026-12-01',
            'source_type' => 'text',
            'is_cash_settled' => true,
        ]);

        // Ambil laporan periode "this_month" (November 2026)
        $reportData = $this->reportService->getProfileReportData($business, 'this_month');

        // Total kas masuk HARUS murni Rp 2.000.000 (tidak ada kebocoran transaksi Oktober maupun Desember)
        $this->assertEquals(2000000.0, $reportData['cash_flow']['total_cash_in']);
        $this->assertEquals(1, $reportData['cash_flow']['cash_in_count']);

        $renderedHtml = view('pdf.business-profile', $this->reportService->getCompleteProfileReportWithAi($business, 'this_month'))->render();
        $this->assertStringContainsString('Rp 2.000.000', $renderedHtml);
        $this->assertStringNotContainsString('Rp 10.000.000', $renderedHtml);
        $this->assertStringNotContainsString('Rp 50.000.000', $renderedHtml);
    }
}

