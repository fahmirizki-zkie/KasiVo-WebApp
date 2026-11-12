<?php

namespace Tests\Unit;

use App\Exceptions\AiQuotaExceededException;
use App\Services\Ai\GroqService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GroqServiceFailoverTest extends TestCase
{
    public function test_it_throws_quota_exceeded_exception_on_429_rate_limit(): void
    {
        Http::fake([
            'api.groq.com/openai/v1/chat/completions' => Http::response([
                'error' => [
                    'message' => 'Rate limit reached for model qwen/qwen3.8-27b on tokens per minute (TPM).',
                    'type' => 'tokens',
                    'code' => 'rate_limit_exceeded',
                ],
            ], 429),
        ]);

        $service = new GroqService(apiKey: 'gsk_test_mock_key');

        $this->expectException(AiQuotaExceededException::class);
        $this->expectExceptionMessage('Kuota AI Anda sudah penuh atau token AI Anda sudah habis.');

        $service->chat([
            ['role' => 'user', 'content' => 'Beli beras 50rb'],
        ]);
    }

    public function test_it_retries_with_failover_model_when_primary_model_fails(): void
    {
        Http::fake([
            // Panggilan pertama (model primary qwen/qwen3.8-27b) gagal 500
            'api.groq.com/openai/v1/chat/completions' => Http::sequence()
                ->push([
                    'error' => ['message' => 'Internal Groq Model Server Error'],
                ], 500)
                // Panggilan kedua (failover model llama-3.3-70b-versatile) berhasil
                ->push([
                    'choices' => [
                        [
                            'message' => [
                                'content' => json_encode([
                                    'type' => 'cash_out',
                                    'category' => 'bahan_baku',
                                    'total_amount' => 50000,
                                    'confidence_score' => 0.95,
                                    'items' => [],
                                ]),
                            ],
                        ],
                    ],
                ], 200),
        ]);

        $service = new GroqService(
            apiKey: 'gsk_test_mock_key',
            textModel: 'qwen/qwen3.8-27b',
            textFailoverModel: 'llama-3.3-70b-versatile'
        );

        $result = $service->extractFromText('Beli beras 50rb');

        $this->assertEquals(50000, (float) $result['total_amount']);
        $this->assertEquals('cash_out', $result['type']);

        // Pastikan dipanggil 2 kali (primary lalu failover)
        Http::assertSentCount(2);
    }
}

