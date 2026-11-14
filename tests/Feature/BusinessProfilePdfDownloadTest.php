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

class BusinessProfilePdfDownloadTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Business $business;
    private BusinessReportService $reportService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->reportService = app(BusinessReportService::class);

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

    private function createSampleTransactions(): void
    {
        // 1. Kas Masuk Penjualan Rp 3.000.000 (10 Nov 2026)
        Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_in',
            'category' => 'penjualan',
            'amount' => 3000000,
            'transaction_date' => '2026-11-10',
            'source_type' => 'text',
            'is_cash_settled' => true,
        ]);

        // 2. Kas Keluar Bahan Baku Rp 1.000.000 (11 Nov 2026)
        $txOut1 = Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_out',
            'category' => 'bahan_baku',
            'amount' => 1000000,
            'transaction_date' => '2026-11-11',
            'source_type' => 'text',
            'is_cash_settled' => true,
        ]);
        TransactionItem::create([
            'transaction_id' => $txOut1->id,
            'item_name' => 'Biji Kopi Arabika',
            'quantity' => 10,
            'unit' => 'kg',
            'unit_price' => 100000,
            'subtotal' => 1000000,
        ]);

        // 3. Kas Keluar Operasional Rp 200.000 (12 Nov 2026)
        Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_out',
            'category' => 'operasional',
            'amount' => 200000,
            'transaction_date' => '2026-11-12',
            'source_type' => 'text',
            'is_cash_settled' => true,
        ]);
    }

    /**
     * Uji pengguna yang belum terautentikasi dialihkan ke login.
     */
    public function test_unauthenticated_user_cannot_download_profile_pdf(): void
    {
        $response = $this->get(route('business-profile.download'));
        $response->assertRedirect(route('login'));

        $streamResponse = $this->get(route('business-profile.pdf'));
        $streamResponse->assertRedirect(route('login'));
    }

    /**
     * Uji pengguna yang belum melengkapi profil usaha diarahkan ke halaman onboarding.
     */
    public function test_user_without_business_is_redirected_to_onboarding(): void
    {
        $userWithoutBusiness = User::factory()->create();

        $response = $this->actingAs($userWithoutBusiness)->get(route('business-profile.download'));
        $response->assertRedirect(route('onboarding'));
        $response->assertSessionHas('warning');
    }

    /**
     * Uji unduh PDF berhasil dengan status HTTP 200, header application/pdf, dan attachment filename (DoD KV-052).
     */
    public function test_user_can_download_profile_pdf_with_accurate_headers_and_filename(): void
    {
        Carbon::setTestNow('2026-11-14 10:00:00');
        $this->createSampleTransactions();

        $response = $this->actingAs($this->user)->get(route('business-profile.download', ['period' => 'this_month']));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment;', $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('KasiVo-Profil-Usaha-waroeng-kopi-sedap-this_month-20261114.pdf', $response->headers->get('Content-Disposition'));

        // Memastikan isi respon adalah berkas biner PDF standar
        $content = $response->getContent();
        $this->assertStringStartsWith('%PDF-', $content);
    }

    /**
     * Uji pratinjau browser (inline stream) PDF dengan query parameter stream=1.
     */
    public function test_user_can_stream_profile_pdf_for_inline_preview(): void
    {
        Carbon::setTestNow('2026-11-14 10:00:00');
        $this->createSampleTransactions();

        $response = $this->actingAs($this->user)->get(route('business-profile.pdf', [
            'period' => 'this_month',
            'stream' => 1,
        ]));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('inline;', $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('KasiVo-Profil-Usaha-waroeng-kopi-sedap-this_month-20261114.pdf', $response->headers->get('Content-Disposition'));

        $content = $response->getContent();
        $this->assertStringStartsWith('%PDF-', $content);
    }

    /**
     * Uji nilai angka pada PDF 100% identik dengan hasil perhitungan periode dashboard (DoD KV-052).
     */
    public function test_pdf_content_matches_dashboard_cash_flow_figures_100_percent(): void
    {
        Carbon::setTestNow('2026-11-14 10:00:00');
        $this->createSampleTransactions();

        // 1. Data kalkulasi dashboard
        $reportData = $this->reportService->getProfileReportData($this->business, 'this_month');

        $this->assertEquals(3000000.0, $reportData['cash_flow']['total_cash_in']);
        $this->assertEquals(1200000.0, $reportData['cash_flow']['total_cash_out']);
        $this->assertEquals(1800000.0, $reportData['cash_flow']['net_cash_flow']);
        $this->assertEquals('Rp 3.000.000', $reportData['cash_flow']['formatted_cash_in']);
        $this->assertEquals('Rp 1.200.000', $reportData['cash_flow']['formatted_cash_out']);
        $this->assertEquals('Rp 1.800.000', $reportData['cash_flow']['formatted_net']);

        // 2. Render DomPDF dokumen A4 Portrait
        $pdf = $this->reportService->renderPdf($this->business, 'this_month');
        $rawPdfOutput = $pdf->output();

        $this->assertNotEmpty($rawPdfOutput);
        $this->assertStringStartsWith('%PDF-', $rawPdfOutput);

        // 3. Render HTML view untuk memverifikasi data Blade
        $renderedHtml = view('pdf.business-profile', $this->reportService->getCompleteProfileReportWithAi($this->business, 'this_month'))->render();

        $this->assertStringContainsString('Waroeng Kopi Sedap', $renderedHtml);
        $this->assertStringContainsString('Rp 3.000.000', $renderedHtml);
        $this->assertStringContainsString('Rp 1.200.000', $renderedHtml);
        $this->assertStringContainsString('Rp 1.800.000', $renderedHtml);
        $this->assertStringContainsString('Bahan Baku', $renderedHtml);
        $this->assertStringContainsString('Operasional', $renderedHtml);
    }

    /**
     * Uji dokumen memuat narasi usaha dan disclaimer kepatuhan resmi (DoD KV-050 & KV-051).
     */
    public function test_pdf_contains_narrative_and_mandatory_disclaimer(): void
    {
        Carbon::setTestNow('2026-11-14 10:00:00');
        $this->createSampleTransactions();

        $reportWithAi = $this->reportService->getCompleteProfileReportWithAi($this->business, 'this_month');
        $renderedHtml = view('pdf.business-profile', $reportWithAi)->render();

        // 1. Verifikasi narasi ada di HTML template
        $this->assertStringContainsString(e($reportWithAi['ai_narrative']['headline']), $renderedHtml);
        $this->assertStringContainsString(e($reportWithAi['ai_narrative']['narrative']), $renderedHtml);

        // 2. Verifikasi disclaimer resmi tercetak di HTML template
        $this->assertStringContainsString(BusinessReportService::DISCLAIMER, $renderedHtml);
        $this->assertStringContainsString('BUKAN merupakan surat jaminan, rekomendasi pinjaman, atau bukti kelayakan kredit', $renderedHtml);
    }

    /**
     * Uji isolasi data multi-tenant: User A tidak boleh melihat transaksi User B di PDF (DoD KV-053).
     */
    public function test_multi_tenant_isolation_prevents_data_leakage(): void
    {
        Carbon::setTestNow('2026-11-14 10:00:00');

        // Transaksi User A (Waroeng Kopi Sedap): Rp 3.000.000
        $this->createSampleTransactions();

        // Buat User B dan Business B (Bakso Mantap)
        $userB = User::factory()->create(['name' => 'Budi Santoso', 'email' => 'budi@bakso.test']);
        $businessB = Business::create([
            'user_id' => $userB->id,
            'name' => 'Bakso Mantap Solo',
            'business_type' => 'food_beverage',
        ]);

        // Transaksi User B: Rp 99.000.000
        Transaction::create([
            'business_id' => $businessB->id,
            'type' => 'cash_in',
            'category' => 'penjualan',
            'amount' => 99000000,
            'transaction_date' => '2026-11-10',
            'source_type' => 'text',
            'is_cash_settled' => true,
        ]);

        // 1. User A mengunduh PDF-nya
        $responseA = $this->actingAs($this->user)->get(route('business-profile.download', ['period' => 'this_month']));
        $responseA->assertOk();
        $this->assertStringContainsString('waroeng-kopi-sedap', $responseA->headers->get('Content-Disposition'));

        $reportA = $this->reportService->getCompleteProfileReportWithAi($this->business, 'this_month');
        $renderedHtmlA = view('pdf.business-profile', $reportA)->render();

        // Memastikan data User A murni milik Waroeng Kopi Sedap
        $this->assertStringContainsString('Waroeng Kopi Sedap', $renderedHtmlA);
        $this->assertStringContainsString('Rp 3.000.000', $renderedHtmlA);

        // Memastikan SAMA SEKALI TIDAK ADA kebocoran nama atau angka User B
        $this->assertStringNotContainsString('Bakso Mantap Solo', $renderedHtmlA);
        $this->assertStringNotContainsString('99.000.000', $renderedHtmlA);
        $this->assertStringNotContainsString('Budi Santoso', $renderedHtmlA);
    }
}
