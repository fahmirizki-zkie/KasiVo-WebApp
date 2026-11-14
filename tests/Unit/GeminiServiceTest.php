<?php

namespace Tests\Unit;

use App\Exceptions\AiQuotaExceededException;
use App\Models\Business;
use App\Models\User;
use App\Services\Ai\GeminiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class GeminiServiceTest extends TestCase
{
    use RefreshDatabase;

    protected GeminiService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new GeminiService(
            apiKey: 'AIzaSyMockTestKeyForGemini123456789',
            baseUrl: 'https://generativelanguage.googleapis.com/v1beta',
            model: 'gemini-1.5-flash',
            timeout: 5,
            retryAttempts: 1
        );
    }

    public function test_is_configured_returns_false_when_key_is_empty(): void
    {
        $service = new GeminiService(apiKey: '');
        $this->assertFalse($service->isConfigured());

        $serviceWithSpaces = new GeminiService(apiKey: '   ');
        $this->assertFalse($serviceWithSpaces->isConfigured());
    }

    public function test_is_configured_returns_true_when_key_is_present(): void
    {
        $this->assertTrue($this->service->isConfigured());
    }

    public function test_throws_exception_if_api_key_is_missing(): void
    {
        $emptyKeyService = new GeminiService(apiKey: '');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('GEMINI_API_KEY belum dikonfigurasi');

        $emptyKeyService->extractFromImage('fake-base64');
    }

    public function test_extract_from_image_parses_gemini_response_successfully(): void
    {
        $mockPayload = [
            'type' => 'cash_out',
            'category' => 'bahan_baku',
            'total_amount' => 175000,
            'transaction_date' => '2026-11-14',
            'merchant_name' => 'Toko Sayur Mayur Berkah',
            'notes' => 'Belanja bumbu dan sayuran segar',
            'confidence_score' => 0.96,
            'needs_review' => false,
            'items' => [
                [
                    'item_name' => 'Bawang Merah 1kg',
                    'quantity' => 1,
                    'unit' => 'kg',
                    'price_per_unit' => 35000,
                    'subtotal' => 35000,
                ],
                [
                    'item_name' => 'Ayam Potong 4kg',
                    'quantity' => 4,
                    'unit' => 'kg',
                    'price_per_unit' => 35000,
                    'subtotal' => 140000,
                ],
            ],
        ];

        Http::fake([
            'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => json_encode($mockPayload)],
                            ],
                            'role' => 'model',
                        ],
                        'finishReason' => 'STOP',
                    ],
                ],
            ], 200),
        ]);

        $user = User::factory()->create();
        $business = Business::create([
            'user_id' => $user->id,
            'name' => 'Warung Nasi Enak',
            'business_type' => 'F&B Rumah Makan',
        ]);

        $mockBase64 = base64_encode('fake-receipt-image-content');
        $result = $this->service->extractFromImage(
            base64Image: $mockBase64,
            mimeType: 'image/jpeg',
            businessType: 'F&B Rumah Makan',
            businessId: $business->id,
            userId: $user->id
        );

        $this->assertEquals(175000.0, $result['total_amount']);
        $this->assertEquals('Toko Sayur Mayur Berkah', $result['merchant_name']);
        $this->assertEquals('bahan_baku', $result['category']);
        $this->assertCount(2, $result['items']);
        $this->assertEquals('Bawang Merah 1kg', $result['items'][0]['item_name']);
        $this->assertFalse($result['needs_review']);

        // Assert logged to database
        $this->assertDatabaseHas('ai_processing_logs', [
            'business_id' => $business->id,
            'user_id' => $user->id,
            'raw_input_type' => 'image',
            'model_used' => 'gemini:gemini-1.5-flash',
            'status' => 'success',
        ]);
    }

    public function test_extract_from_image_throws_ai_quota_exceeded_on_429(): void
    {
        Http::fake([
            'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent*' => Http::response([
                'error' => [
                    'code' => 429,
                    'message' => 'Resource has been exhausted (e.g. check quota).',
                    'status' => 'RESOURCE_EXHAUSTED',
                ],
            ], 429),
        ]);

        $this->expectException(AiQuotaExceededException::class);
        $this->expectExceptionMessage('Kuota AI Gemini Anda sudah penuh');

        $this->service->extractFromImage('fake-base64');
    }

    public function test_extract_from_image_throws_exception_on_malformed_json(): void
    {
        Http::fake([
            'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => 'Ini teks biasa bukan json sama sekali'],
                            ],
                            'role' => 'model',
                        ],
                    ],
                ],
            ], 200),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Gagal mem-parsing format JSON');

        $this->service->extractFromImage('fake-base64');
    }

    public function test_extract_from_text_parses_successfully(): void
    {
        $mockPayload = [
            'type' => 'cash_out',
            'category' => 'operasional',
            'total_amount' => 22000,
            'transaction_date' => '2026-11-14',
            'merchant_name' => 'Pangkalan Gas Barokah',
            'notes' => 'Beli tabung gas LPG 3kg',
            'confidence_score' => 0.95,
            'needs_review' => false,
            'items' => [
                [
                    'item_name' => 'Gas LPG 3kg',
                    'quantity' => 1,
                    'unit' => 'tabung',
                    'price_per_unit' => 22000,
                    'subtotal' => 22000,
                ],
            ],
        ];

        Http::fake([
            'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => json_encode($mockPayload)],
                            ],
                            'role' => 'model',
                        ],
                    ],
                ],
            ], 200),
        ]);

        $result = $this->service->extractFromText('beli gas 3kg 22rb di pangkalan barokah');

        $this->assertEquals(22000.0, $result['total_amount']);
        $this->assertEquals('operasional', $result['category']);
        $this->assertEquals('Pangkalan Gas Barokah', $result['merchant_name']);
    }
}
