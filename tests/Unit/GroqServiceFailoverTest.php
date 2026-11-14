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

    public function test_vision_retries_with_failover_model_when_primary_vision_fails(): void
    {
        Http::fake([
            // Panggilan pertama (model primary vision qwen/qwen3.8-27b) gagal 500
            'api.groq.com/openai/v1/chat/completions' => Http::sequence()
                ->push([
                    'error' => ['message' => 'Groq Vision Model Error'],
                ], 500)
                // Panggilan kedua (model vision cadangan) berhasil 200
                ->push([
                    'choices' => [
                        [
                            'message' => [
                                'content' => json_encode([
                                    'type' => 'cash_out',
                                    'category' => 'bahan_baku',
                                    'total_amount' => 120000,
                                    'confidence_score' => 0.92,
                                    'merchant_name' => 'Pasar Tradisional',
                                    'items' => [
                                        [
                                            'item_name' => 'Ayam Potong',
                                            'quantity' => 2,
                                            'unit' => 'kg',
                                            'price_per_unit' => 60000,
                                            'subtotal' => 120000,
                                        ],
                                    ],
                                ]),
                            ],
                        ],
                    ],
                ], 200),
        ]);

        $service = new GroqService(
            apiKey: 'gsk_test_mock_key',
            visionModel: 'qwen/qwen3.8-27b',
            visionFailoverModel: 'llama-3.2-11b-vision-preview',
            retryAttempts: 1
        );

        $fakeBase64 = base64_encode('fake-receipt-image-content');
        $result = $service->extractFromImage($fakeBase64, 'image/jpeg');

        $this->assertEquals(120000, (float) $result['total_amount']);
        $this->assertEquals('Pasar Tradisional', $result['merchant_name']);
        $this->assertCount(1, $result['items']);

        // Verifikasi dipanggil 2 kali (primary lalu failover)
        Http::assertSentCount(2);
    }

    public function test_vision_throws_quota_exceeded_on_429_without_retrying_secondary(): void
    {
        Http::fake([
            'api.groq.com/openai/v1/chat/completions' => Http::response([
                'error' => [
                    'message' => 'Rate limit exceeded: quota exhausted',
                    'type' => 'tokens',
                    'code' => 'rate_limit_exceeded',
                ],
            ], 429),
        ]);

        $service = new GroqService(
            apiKey: 'gsk_test_mock_key',
            visionModel: 'qwen/qwen3.8-27b',
            visionFailoverModel: 'llama-3.2-11b-vision-preview',
            retryAttempts: 1
        );

        $this->expectException(AiQuotaExceededException::class);
        $this->expectExceptionMessage('Kuota AI Anda sudah penuh atau token AI Anda sudah habis.');

        $fakeBase64 = base64_encode('fake-receipt-image-content');
        try {
            $service->extractFromImage($fakeBase64, 'image/jpeg');
        } finally {
            // Memastikan tidak mencoba model kedua jika error adalah kuota/rate limit
            Http::assertSentCount(1);
        }
    }

    public function test_vision_throws_exception_when_both_vision_models_fail(): void
    {
        Http::fake([
            'api.groq.com/openai/v1/chat/completions' => Http::sequence()
                ->push([
                    'error' => ['message' => 'Primary Vision Unavailable'],
                ], 500)
                ->push([
                    'error' => ['message' => 'Secondary Vision Unavailable'],
                ], 500),
        ]);

        $service = new GroqService(
            apiKey: 'gsk_test_mock_key',
            visionModel: 'qwen/qwen3.8-27b',
            visionFailoverModel: 'llama-3.2-11b-vision-preview',
            retryAttempts: 1
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Secondary Vision Unavailable');

        $fakeBase64 = base64_encode('fake-receipt-image-content');
        try {
            $service->extractFromImage($fakeBase64, 'image/jpeg');
        } finally {
            Http::assertSentCount(2);
        }
    }
}

