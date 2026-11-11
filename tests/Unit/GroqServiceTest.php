<?php

namespace Tests\Unit;

use App\Models\Business;
use App\Models\User;
use App\Services\Ai\GroqService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class GroqServiceTest extends TestCase
{
    use RefreshDatabase;

    protected GroqService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new GroqService(
            apiKey: 'gsk_test_mock_groq_api_key_1234567890',
            baseUrl: 'https://api.groq.com/openai/v1',
            textModel: 'llama-3.3-70b-versatile',
            visionModel: 'llama-3.2-11b-vision-preview',
            whisperModel: 'whisper-large-v3-turbo',
            timeout: 5,
            retryAttempts: 1
        );
    }

    public function test_throws_exception_if_api_key_is_missing(): void
    {
        $emptyKeyService = new GroqService(apiKey: '');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('GROQ_API_KEY belum dikonfigurasi');

        $emptyKeyService->chat([['role' => 'user', 'content' => 'Halo']]);
    }

    public function test_chat_completion_success_with_json_mode(): void
    {
        Http::fake([
            'https://api.groq.com/openai/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'type' => 'cash_out',
                                'total_amount' => 50000,
                                'category' => 'bahan_baku',
                            ]),
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->service->chat([
            ['role' => 'user', 'content' => 'Beli telur 50rb'],
        ]);

        $this->assertIsArray($response);
        $this->assertEquals('cash_out', $response['type']);
        $this->assertEquals(50000, $response['total_amount']);
    }

    public function test_extract_from_text_parses_and_normalizes(): void
    {
        Http::fake([
            'https://api.groq.com/openai/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'type' => 'cash_out',
                                'category' => 'bahan_baku',
                                'total_amount' => 75000,
                                'transaction_date' => '2026-11-11',
                                'merchant_name' => 'Toko Berkah',
                                'notes' => 'Beli beras 5kg',
                                'confidence_score' => 0.95,
                                'needs_review' => false,
                                'items' => [
                                    [
                                        'item_name' => 'Beras 5kg',
                                        'quantity' => 1,
                                        'unit' => 'karung',
                                        'price_per_unit' => 75000,
                                        'subtotal' => 75000,
                                    ],
                                ],
                            ]),
                        ],
                    ],
                ],
            ], 200),
        ]);

        $result = $this->service->extractFromText('Tadi beli beras 5kg seharga 75rb di Toko Berkah', businessType: 'Warung Nasi');

        $this->assertEquals('cash_out', $result['type']);
        $this->assertEquals('bahan_baku', $result['category']);
        $this->assertEquals(75000.0, $result['total_amount']);
        $this->assertEquals('Toko Berkah', $result['merchant_name']);
        $this->assertFalse($result['needs_review']);
        $this->assertCount(1, $result['items']);
    }

    public function test_extract_from_image_sends_base64_multimodal_payload(): void
    {
        Http::fake([
            'https://api.groq.com/openai/v1/chat/completions' => function (\Illuminate\Http\Client\Request $request) {
                $payload = $request->data();

                $this->assertEquals('llama-3.2-11b-vision-preview', $payload['model']);
                $content = $payload['messages'][1]['content'];
                $this->assertIsArray($content);
                $this->assertEquals('image_url', $content[1]['type']);
                $this->assertStringStartsWith('data:image/jpeg;base64,', $content[1]['image_url']['url']);

                return Http::response([
                    'choices' => [
                        [
                            'message' => [
                                'content' => json_encode([
                                    'type' => 'cash_out',
                                    'category' => 'bahan_baku',
                                    'total_amount' => 125000,
                                    'transaction_date' => '2026-11-11',
                                    'merchant_name' => 'Supermarket F&B',
                                    'confidence_score' => 0.98,
                                    'needs_review' => false,
                                    'items' => [
                                        [
                                            'item_name' => 'Minyak Goreng 2L',
                                            'quantity' => 2,
                                            'price_per_unit' => 35000,
                                            'subtotal' => 70000,
                                        ],
                                        [
                                            'item_name' => 'Tepung Terigu 1kg',
                                            'quantity' => 5,
                                            'price_per_unit' => 11000,
                                            'subtotal' => 55000,
                                        ],
                                    ],
                                ]),
                            ],
                        ],
                    ],
                ], 200);
            },
        ]);

        $mockBase64 = base64_encode('fake-receipt-image-content');
        $result = $this->service->extractFromImage($mockBase64, mimeType: 'image/jpeg');

        $this->assertEquals(125000.0, $result['total_amount']);
        $this->assertEquals('Supermarket F&B', $result['merchant_name']);
        $this->assertCount(2, $result['items']);
    }

    public function test_transcribe_audio_sends_multipart_file_to_whisper(): void
    {
        Http::fake([
            'https://api.groq.com/openai/v1/audio/transcriptions' => Http::response([
                'text' => 'Beli telur ayam dua kilo lima puluh enam ribu di warung madura',
            ], 200),
        ]);

        $tmpAudio = tempnam(sys_get_temp_dir(), 'kasivo_test_') . '.wav';
        file_put_contents($tmpAudio, 'fake-wav-header-and-sound-data');

        try {
            $transcript = $this->service->transcribeAudio($tmpAudio);
            $this->assertEquals('Beli telur ayam dua kilo lima puluh enam ribu di warung madura', $transcript);
        } finally {
            if (file_exists($tmpAudio)) {
                unlink($tmpAudio);
            }
        }
    }

    public function test_logs_processing_to_ai_processing_logs_table(): void
    {
        $user = User::factory()->create();
        $business = Business::create([
            'user_id' => $user->id,
            'name' => 'Kedai Kopi Test',
            'business_type' => 'F&B Kedai Kopi',
        ]);

        Http::fake([
            'https://api.groq.com/openai/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'type' => 'cash_out',
                                'category' => 'operasional',
                                'total_amount' => 100000,
                                'notes' => 'Beli token listrik',
                                'confidence_score' => 0.99,
                                'needs_review' => false,
                                'items' => [],
                            ]),
                        ],
                    ],
                ],
            ], 200),
        ]);

        $this->service->extractFromText(
            text: 'Beli token listrik seratus ribu',
            businessId: $business->id,
            userId: $user->id
        );

        $this->assertDatabaseHas('ai_processing_logs', [
            'business_id' => $business->id,
            'user_id' => $user->id,
            'raw_input_type' => 'text',
            'model_used' => 'llama-3.3-70b-versatile',
            'status' => 'success',
        ]);
    }

    public function test_handles_groq_api_error_gracefully(): void
    {
        Http::fake([
            'https://api.groq.com/openai/v1/chat/completions' => Http::response([
                'error' => [
                    'message' => 'Rate limit reached for model',
                    'type' => 'tokens',
                ],
            ], 429),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Groq API request failed [429]: Rate limit reached for model');

        $this->service->chat([['role' => 'user', 'content' => 'Halo']]);
    }
}
