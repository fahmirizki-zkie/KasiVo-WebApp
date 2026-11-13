<?php

namespace Tests\Unit;

use App\Exceptions\AiQuotaExceededException;
use App\Models\Business;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use App\Services\Ai\GroqService;
use App\Services\BusinessReportService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class BusinessReportAiNarrativeTest extends TestCase
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

    private function createSampleTransactions(): void
    {
        // 1. Kas Masuk Penjualan Rp 2.500.000 (10 Nov 2026)
        Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_in',
            'category' => 'penjualan',
            'amount' => 2500000,
            'transaction_date' => '2026-11-10',
            'source_type' => 'text',
            'is_cash_settled' => true,
        ]);

        // 2. Kas Keluar Bahan Baku Rp 750.000 (11 Nov 2026)
        $txOut1 = Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_out',
            'category' => 'bahan_baku',
            'amount' => 750000,
            'transaction_date' => '2026-11-11',
            'source_type' => 'text',
            'is_cash_settled' => true,
        ]);
        TransactionItem::create([
            'transaction_id' => $txOut1->id,
            'item_name' => 'Biji Kopi Robusta',
            'quantity' => 10,
            'unit' => 'kg',
            'unit_price' => 75000,
            'subtotal' => 750000,
        ]);

        // 3. Kas Keluar Operasional Rp 250.000 (12 Nov 2026)
        Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_out',
            'category' => 'operasional',
            'amount' => 250000,
            'transaction_date' => '2026-11-12',
            'source_type' => 'text',
            'is_cash_settled' => true,
        ]);
    }

    /**
     * Uji pemanggilan AI berhasil menghasilkan narasi ringkas berbasis data riil (DoD KV-051).
     */
    public function test_ai_generates_concise_narrative_based_on_actual_data(): void
    {
        Carbon::setTestNow('2026-11-14 10:00:00');
        $this->createSampleTransactions();

        $mockGroq = $this->createMock(GroqService::class);
        $mockGroq->expects($this->once())
            ->method('chat')
            ->willReturn([
                'headline' => 'Ikhtisar Kinerja Operasional & Arus Kas',
                'narrative' => 'Selama bulan November 2026, Waroeng Kopi Sedap mencatatkan total perputaran kas masuk sebesar Rp 2.500.000 dengan total pengeluaran operasional Rp 1.000.000. Posisi arus kas bersih berada dalam kondisi surplus Rp 1.500.000 dengan porsi pengeluaran terbesar dialokasikan untuk belanja bahan baku.',
                'highlights' => [
                    'Kas masuk tercatat Rp 2.500.000 dari 1 transaksi penjualan',
                    'Alokasi pengeluaran utama pada pos bahan baku sebesar Rp 750.000',
                    'Surplus arus kas bersih tercatat Rp 1.500.000',
                ],
                'compliance_note' => 'Ringkasan narasi ini disusun secara deskriptif berdasarkan pencatatan transaksi aktual di platform KasiVo, bukan merupakan evaluasi atau keputusan kelayakan kredit pembiayaan.',
            ]);

        $result = $this->service->generateAiNarrative(
            $this->business,
            'this_month',
            customGroq: $mockGroq
        );

        $this->assertTrue($result['is_ai_generated']);
        $this->assertEquals('groq_llm', $result['source']);
        $this->assertEquals('Ikhtisar Kinerja Operasional & Arus Kas', $result['headline']);
        $this->assertStringContainsString('Rp 2.500.000', $result['narrative']);
        $this->assertStringContainsString('surplus Rp 1.500.000', $result['narrative']);
        $this->assertCount(3, $result['highlights']);
        $this->assertStringContainsString('bukan merupakan evaluasi atau keputusan kelayakan kredit', $result['compliance_note']);
    }

    /**
     * Uji kepatuhan mutlak DoD KV-051: Mencegah halusinasi keputusan kredit dan klaim pembiayaan.
     */
    public function test_ai_strictly_does_not_hallucinate_financing_decisions(): void
    {
        Carbon::setTestNow('2026-11-14 10:00:00');
        $this->createSampleTransactions();

        // Simulasi jika AI mencoba menghasilkan klaim kelayakan kredit / rekomendasi pinjaman bank
        $mockGroq = $this->createMock(GroqService::class);
        $mockGroq->expects($this->once())
            ->method('chat')
            ->willReturn([
                'headline' => 'Rekomendasi Pembiayaan Usaha',
                'narrative' => 'Waroeng Kopi Sedap sangat sehat dan layak kredit untuk pengajuan pinjaman modal kerja Rp 50.000.000 dari bank.',
                'highlights' => ['Layak pembiayaan perbankan'],
                'compliance_note' => 'Disetujui untuk pembiayaan.',
            ]);

        $result = $this->service->generateAiNarrative(
            $this->business,
            'this_month',
            customGroq: $mockGroq
        );

        // Sanitize filter wajib menolak narasi tersebut dan menggantinya dengan fallback deterministik yang aman
        $this->assertFalse($result['is_ai_generated']);
        $this->assertEquals('rule_engine_fallback', $result['source']);
        $this->assertStringNotContainsString('layak kredit', mb_strtolower($result['narrative']));
        $this->assertStringNotContainsString('pinjaman modal', mb_strtolower($result['narrative']));
        $this->assertStringContainsString('bukan merupakan evaluasi atau keputusan kelayakan kredit', $result['compliance_note']);
    }

    /**
     * Uji ketahanan fallback deterministik saat kuota AI habis atau API error (DoD KV-051).
     */
    public function test_fallback_narrative_generates_accurate_summary_when_ai_fails(): void
    {
        Carbon::setTestNow('2026-11-14 10:00:00');
        $this->createSampleTransactions();

        $mockGroq = $this->createMock(GroqService::class);
        $mockGroq->expects($this->once())
            ->method('chat')
            ->willThrowException(new AiQuotaExceededException('Groq Rate Limit Exceeded'));

        $result = $this->service->generateAiNarrative(
            $this->business,
            'this_month',
            customGroq: $mockGroq
        );

        // Zero crash, deterministic fallback matches actual figures
        $this->assertFalse($result['is_ai_generated']);
        $this->assertEquals('rule_engine_fallback', $result['source']);
        $this->assertEquals('Ikhtisar Kinerja Operasional & Arus Kas', $result['headline']);
        $this->assertStringContainsString('Rp 2.500.000', $result['narrative']);
        $this->assertStringContainsString('Rp 1.000.000', $result['narrative']);
        $this->assertStringContainsString('Rp 1.500.000', $result['narrative']);
        $this->assertStringContainsString('Bahan Baku', $result['narrative']);
        $this->assertCount(3, $result['highlights']);
    }

    /**
     * Uji empty state saat usaha belum memiliki transaksi di periode terpilih.
     */
    public function test_zero_transaction_empty_state_generates_graceful_narrative(): void
    {
        Carbon::setTestNow('2026-11-14 10:00:00');
        // Tidak ada transaksi sama sekali

        $mockGroq = $this->createMock(GroqService::class);
        // Groq tidak boleh dipanggil saat transaksi = 0 untuk menghemat kuota token
        $mockGroq->expects($this->never())->method('chat');

        $result = $this->service->generateAiNarrative(
            $this->business,
            'this_month',
            customGroq: $mockGroq
        );

        $this->assertFalse($result['is_ai_generated']);
        $this->assertEquals('rule_engine_fallback', $result['source']);
        $this->assertStringContainsString('belum mencatatkan transaksi kas masuk maupun kas keluar', $result['narrative']);
        $this->assertStringContainsString('Belum ada transaksi tercatat', $result['highlights'][0]);
    }

    /**
     * Uji integrasi getCompleteProfileReportWithAi menyertakan seluruh payload laporan dan AI narrative.
     */
    public function test_get_complete_profile_report_with_ai_integrates_seamlessly(): void
    {
        Carbon::setTestNow('2026-11-14 10:00:00');
        $this->createSampleTransactions();

        $mockGroq = $this->createMock(GroqService::class);
        $mockGroq->expects($this->once())
            ->method('chat')
            ->willReturn([
                'headline' => 'Ikhtisar Kinerja Waroeng Kopi Sedap',
                'narrative' => 'Kinerja operasional positif dengan surplus kas bersih Rp 1.500.000.',
                'highlights' => ['Surplus kas Rp 1.500.000'],
                'compliance_note' => 'Hanya untuk pencatatan internal.',
            ]);

        $report = $this->service->getCompleteProfileReportWithAi(
            $this->business,
            'this_month',
            customGroq: $mockGroq
        );

        $this->assertArrayHasKey('document', $report);
        $this->assertArrayHasKey('business', $report);
        $this->assertArrayHasKey('period', $report);
        $this->assertArrayHasKey('cash_flow', $report);
        $this->assertArrayHasKey('categories', $report);
        $this->assertArrayHasKey('ai_narrative', $report);
        $this->assertArrayHasKey('disclaimer', $report);

        $this->assertEquals('Waroeng Kopi Sedap', $report['business']['name']);
        $this->assertEquals(2500000.0, $report['cash_flow']['total_cash_in']);
        $this->assertEquals(1000000.0, $report['cash_flow']['total_cash_out']);
        $this->assertEquals(1500000.0, $report['cash_flow']['net_cash_flow']);

        $this->assertNotNull($report['ai_narrative']);
        $this->assertTrue($report['ai_narrative']['is_ai_generated']);
        $this->assertEquals('Ikhtisar Kinerja Waroeng Kopi Sedap', $report['ai_narrative']['headline']);
    }
}
