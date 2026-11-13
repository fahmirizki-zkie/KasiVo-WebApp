<?php

namespace Tests\Unit;

use App\Exceptions\AiQuotaExceededException;
use App\Models\Business;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use App\Services\Ai\GroqService;
use App\Services\RestockService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class RestockAiExplanationTest extends TestCase
{
    use RefreshDatabase;

    private RestockService $service;
    private Business $business;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new RestockService();

        $this->user = User::factory()->create();
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
     * Uji pemanggilan Groq LLM sukses menghasilkan narasi penjelasan ramah UMKM (DoD KV-048).
     */
    public function test_ai_explains_restock_recommendations_successfully(): void
    {
        // Setup data transaksi: Beras dibeli tiap 4 hari (1 Nov, 5 Nov, 9 Nov)
        $this->createRawMaterialTransaction('2026-11-01', 'Beras Rojolele', 5, 'kg', 15000);
        $this->createRawMaterialTransaction('2026-11-05', 'Beras Rojolele', 5, 'kg', 15000);
        $this->createRawMaterialTransaction('2026-11-09', 'Beras Rojolele', 5, 'kg', 15000);

        // Reference date = 13 Nov 2026 (Due today!)
        $refDate = Carbon::parse('2026-11-13');
        Carbon::setTestNow($refDate);

        // Mock GroqService
        $mockGroq = $this->createMock(GroqService::class);
        $mockGroq->expects($this->once())
            ->method('chat')
            ->willReturn([
                'summary_headline' => 'Waktunya Belanja Beras Rojolele Hari Ini',
                'ai_explanation' => 'Halo Waroeng Kopi Sedap! Berdasarkan kebiasaan belanja setiap 4 hari sekali, hari ini Beras Rojolele sudah waktunya dibeli kembali sekitar 5 kg dengan estimasi dana Rp 75.000 agar racikan dapur tetap aman.',
                'actionable_advice' => 'Siapkan dana operasional Rp 75.000 untuk belanja bahan hari ini.',
                'data_limitations' => 'Prediksi didasarkan pada siklus interval belanja 13 hari terakhir dan bukan hasil hitung sisa fisik beras di dapur.',
            ]);

        $result = $this->service->explainRestockWithAi($this->business, $refDate, $mockGroq);

        $this->assertTrue($result['is_sufficient']);
        $this->assertTrue($result['is_ai_generated']);
        $this->assertEquals('groq_llm', $result['source']);
        $this->assertEquals('Waktunya Belanja Beras Rojolele Hari Ini', $result['summary_headline']);
        $this->assertStringContainsString('Halo Waroeng Kopi Sedap!', $result['ai_explanation']);
        $this->assertStringContainsString('bukan hasil hitung sisa fisik beras', $result['data_limitations']);
        $this->assertEquals(RestockService::DISCLAIMER, $result['disclaimer']);
        $this->assertArrayHasKey('recommendations_data', $result);
    }

    /**
     * Uji ketahanan sistem (resilience): saat Groq API error / kuota habis, fallback deterministik aktif tanpa crash.
     */
    public function test_graceful_fallback_when_ai_fails_or_quota_exceeded(): void
    {
        $this->createRawMaterialTransaction('2026-11-01', 'Gula Pasir', 5, 'kg', 14000);
        $this->createRawMaterialTransaction('2026-11-05', 'Gula Pasir', 5, 'kg', 14000);
        $this->createRawMaterialTransaction('2026-11-09', 'Gula Pasir', 5, 'kg', 14000);

        $refDate = Carbon::parse('2026-11-13');
        Carbon::setTestNow($refDate);

        // Simulasi Kuota AI Habis (HTTP 429)
        $mockGroq = $this->createMock(GroqService::class);
        $mockGroq->expects($this->once())
            ->method('chat')
            ->willThrowException(new AiQuotaExceededException('Kuota AI penuh.'));

        // Panggilan method TIDAK boleh crash
        $result = $this->service->explainRestockWithAi($this->business, $refDate, $mockGroq);

        $this->assertTrue($result['is_sufficient']);
        $this->assertFalse($result['is_ai_generated']);
        $this->assertEquals('rule_engine_fallback', $result['source']);
        $this->assertStringContainsString('Ada 1 Bahan Baku yang Disarankan untuk Restock', $result['summary_headline']);
        $this->assertStringContainsString('Gula Pasir', $result['ai_explanation']);
        $this->assertStringContainsString('70.000', $result['ai_explanation']);
        $this->assertStringContainsString('BUKAN merupakan pelacakan sisa stok fisik riil', $result['data_limitations']);
    }

    /**
     * Uji ketahanan terhadap generic RuntimeException dari AI network failure.
     */
    public function test_graceful_fallback_when_network_runtime_exception_occurs(): void
    {
        $this->createRawMaterialTransaction('2026-11-01', 'Biji Kopi', 2, 'kg', 90000);
        $this->createRawMaterialTransaction('2026-11-05', 'Biji Kopi', 2, 'kg', 90000);
        $this->createRawMaterialTransaction('2026-11-09', 'Biji Kopi', 2, 'kg', 90000);

        $refDate = Carbon::parse('2026-11-13');
        Carbon::setTestNow($refDate);

        $mockGroq = $this->createMock(GroqService::class);
        $mockGroq->expects($this->once())
            ->method('chat')
            ->willThrowException(new RuntimeException('Connection timed out'));

        $result = $this->service->explainRestockWithAi($this->business, $refDate, $mockGroq);

        $this->assertTrue($result['is_sufficient']);
        $this->assertFalse($result['is_ai_generated']);
        $this->assertEquals('rule_engine_fallback', $result['source']);
        $this->assertNotEmpty($result['ai_explanation']);
    }

    /**
     * Uji alasan saran dan keterbatasan data disebutkan secara transparan (DoD KV-048).
     */
    public function test_explanation_clearly_mentions_reasons_and_data_limitations(): void
    {
        $this->createRawMaterialTransaction('2026-11-01', 'Minyak Goreng', 2, 'liter', 20000);
        $this->createRawMaterialTransaction('2026-11-05', 'Minyak Goreng', 2, 'liter', 20000);
        $this->createRawMaterialTransaction('2026-11-09', 'Minyak Goreng', 2, 'liter', 20000);

        $refDate = Carbon::parse('2026-11-13');
        Carbon::setTestNow($refDate);

        // Uji fallback rule engine secara langsung
        $recommendationData = $this->service->generateRestockRecommendations($this->business, $refDate);
        $fallback = $this->service->buildFallbackRestockExplanation($recommendationData);

        // 1. Alasan saran jelas
        $this->assertStringContainsString('riwayat belanja', $fallback['ai_explanation']);
        $this->assertStringContainsString('Minyak Goreng', $fallback['ai_explanation']);
        $this->assertStringContainsString('anggaran belanja', $fallback['ai_explanation']);

        // 2. Keterbatasan data jelas (tidak mengklaim stok fisik)
        $this->assertStringContainsString('BUKAN merupakan pelacakan sisa stok fisik riil', $fallback['data_limitations']);
        $this->assertStringContainsString('interval riwayat belanja', $fallback['data_limitations']);
    }

    /**
     * Uji keadaan ketika seluruh bahan masih dalam kondisi aman (belum ada yang perlu dibeli).
     */
    public function test_explains_all_safe_state_gracefully(): void
    {
        // Interval 5 hari (1 Nov, 6 Nov, 11 Nov). Terakhir beli 11 Nov.
        // Jadwal belanja berikutnya: 16 Nov.
        $this->createRawMaterialTransaction('2026-11-01', 'Sirup Frambozen', 2, 'botol', 30000);
        $this->createRawMaterialTransaction('2026-11-06', 'Sirup Frambozen', 2, 'botol', 30000);
        $this->createRawMaterialTransaction('2026-11-11', 'Sirup Frambozen', 2, 'botol', 30000);

        // Hari ini = 12 Nov (masih 4 hari lagi menuju 16 Nov -> Safe!)
        $refDate = Carbon::parse('2026-11-12');
        Carbon::setTestNow($refDate);

        $result = $this->service->explainRestockWithAi($this->business, $refDate);

        $this->assertTrue($result['is_sufficient']);
        $this->assertFalse($result['is_ai_generated']);
        $this->assertEquals('rule_engine', $result['source']);
        $this->assertEquals('Semua Bahan Baku Masih Dalam Siklus Aman', $result['summary_headline']);
        $this->assertStringContainsString('masih dalam jadwal aman', $result['ai_explanation']);
        $this->assertStringContainsString('Tidak ada belanja bahan mendesak hari ini', $result['actionable_advice']);
    }

    /**
     * Uji keadaan ketika histori belanja belum mencapai 7 hari (< 7 hari).
     */
    public function test_explains_insufficient_data_state_gracefully(): void
    {
        // Baru belanja 2 hari (1 Nov s.d. 3 Nov)
        $this->createRawMaterialTransaction('2026-11-01', 'Beras Rojolele', 5, 'kg', 15000);
        $this->createRawMaterialTransaction('2026-11-03', 'Beras Rojolele', 5, 'kg', 15000);

        $refDate = Carbon::parse('2026-11-03');
        Carbon::setTestNow($refDate);

        $result = $this->service->explainRestockWithAi($this->business, $refDate);

        $this->assertFalse($result['is_sufficient']);
        $this->assertEquals('rule_engine', $result['source']);
        $this->assertEquals('Pencatatan Belanja Belum Mencapai 7 Hari', $result['summary_headline']);
        $this->assertStringContainsString('sisa 4 hari lagi', $result['ai_explanation']);
        $this->assertStringContainsString('minimal 7 hari kalender', $result['data_limitations']);
    }

    /**
     * Helper membuat transaksi belanja bahan baku.
     */
    private function createRawMaterialTransaction(
        string $date,
        string $itemName,
        float $quantity,
        string $unit,
        float $unitPrice
    ): Transaction {
        $subtotal = $quantity * $unitPrice;

        $tx = Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_out',
            'category' => 'bahan_baku',
            'amount' => $subtotal,
            'transaction_date' => $date,
            'source_type' => 'text',
            'is_cash_settled' => true,
        ]);

        TransactionItem::create([
            'transaction_id' => $tx->id,
            'item_name' => $itemName,
            'quantity' => $quantity,
            'unit' => $unit,
            'unit_price' => $unitPrice,
            'subtotal' => $subtotal,
        ]);

        return $tx;
    }
}

