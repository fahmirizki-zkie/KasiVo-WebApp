<?php

namespace App\Services\Ai;

use App\Exceptions\AiQuotaExceededException;
use App\Models\AiProcessingLog;
use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class GeminiService
{
    protected string $apiKey;
    protected string $baseUrl;
    protected string $model;
    protected int $timeout;
    protected int $retryAttempts;

    public function __construct(
        ?string $apiKey = null,
        ?string $baseUrl = null,
        ?string $model = null,
        ?int $timeout = null,
        ?int $retryAttempts = null
    ) {
        $this->apiKey = $apiKey ?? (string) config('services.gemini.api_key', '');
        $this->baseUrl = rtrim($baseUrl ?? (string) config('services.gemini.base_url', 'https://generativelanguage.googleapis.com/v1beta'), '/');
        $this->model = $model ?? (string) config('services.gemini.model', 'gemini-1.5-flash');
        $this->timeout = $timeout ?? (int) config('services.gemini.timeout', 30);
        $this->retryAttempts = $retryAttempts ?? (int) config('services.gemini.retry_attempts', 2);
    }

    /**
     * Cek apakah API key Gemini telah dikonfigurasi.
     */
    public function isConfigured(): bool
    {
        return !empty(trim($this->apiKey));
    }

    /**
     * Ekstraksi data transaksi terstruktur dari foto struk/nota belanja menggunakan Google Gemini Multimodal Vision.
     *
     * @return array<string, mixed>
     * @throws RuntimeException|AiQuotaExceededException
     */
    public function extractFromImage(
        string $base64Image,
        string $mimeType = 'image/jpeg',
        ?string $additionalContext = null,
        ?string $businessType = null,
        ?int $businessId = null,
        ?int $userId = null
    ): array {
        $startTime = hrtime(true);
        $inputType = 'image';
        $model = "gemini:{$this->model}";
        $status = 'success';
        $errorMessage = null;
        $normalized = [];

        try {
            $this->ensureApiKey();

            $systemPrompt = TransactionJsonSchema::getSystemPrompt($businessType);

            // Bersihkan prefix jika berupa data URL (data:image/...;base64,...)
            if (str_contains($base64Image, ';base64,')) {
                $parts = explode(';base64,', $base64Image);
                $base64Image = end($parts);
            }

            $userPromptText = "Ekstrak semua informasi transaksi dari foto struk/nota ini ke dalam format JSON sesuai aturan.";
            if ($additionalContext) {
                $userPromptText .= "\nKonteks tambahan dari pengguna: {$additionalContext}";
            }

            $payload = [
                'system_instruction' => [
                    'parts' => [
                        ['text' => $systemPrompt],
                    ],
                ],
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            ['text' => $userPromptText],
                            [
                                'inline_data' => [
                                    'mime_type' => $mimeType,
                                    'data' => $base64Image,
                                ],
                            ],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'response_mime_type' => 'application/json',
                    'temperature' => 0.1,
                ],
            ];

            $endpoint = "{$this->baseUrl}/models/{$this->model}:generateContent?key={$this->apiKey}";

            $response = Http::timeout($this->timeout)
                ->retry($this->retryAttempts, 500, throw: false)
                ->post($endpoint, $payload);

            if (!$response->successful()) {
                $err = $this->extractErrorMessage($response);

                if ($this->isQuotaExceededError($response->status(), $err)) {
                    Log::warning('Gemini API Quota / Rate Limit Exceeded', [
                        'status' => $response->status(),
                        'error' => $err,
                        'model' => $this->model,
                    ]);
                    throw new AiQuotaExceededException('Kuota AI Gemini Anda sudah penuh atau token AI Anda sudah habis.');
                }

                Log::error('Gemini Vision Request Failed', [
                    'status' => $response->status(),
                    'error' => $err,
                    'model' => $this->model,
                ]);
                throw new RuntimeException("Gemini API request failed [{$response->status()}]: {$err}");
            }

            $responseData = $response->json();
            $rawContent = $responseData['candidates'][0]['content']['parts'][0]['text'] ?? '';

            if (empty(trim($rawContent))) {
                throw new RuntimeException("Gemini API mengembalikan konten kosong.");
            }

            $cleaned = TransactionJsonSchema::cleanJsonString($rawContent);
            $decoded = json_decode($cleaned, true);

            if (!is_array($decoded)) {
                Log::error('Gemini JSON Decode Failed', [
                    'raw_content' => $rawContent,
                    'cleaned' => $cleaned,
                    'json_last_error' => json_last_error_msg(),
                ]);
                throw new RuntimeException("Gagal mem-parsing format JSON dari respon Gemini: " . json_last_error_msg());
            }

            $normalized = TransactionJsonSchema::validateAndNormalize($decoded);

            if (!empty($normalized['needs_review'])) {
                $status = 'needs_review';
            }

            return $normalized;
        } catch (\Throwable $e) {
            $status = 'failed';
            $errorMessage = $e->getMessage();
            throw $e;
        } finally {
            $durationMs = (int) round((hrtime(true) - $startTime) / 1e6);

            if ($businessId && $userId) {
                $confidenceFlags = [
                    'confidence_score' => $normalized['confidence_score'] ?? 0.0,
                    'needs_review' => $normalized['needs_review'] ?? false,
                    'unclear_fields' => $normalized['unclear_fields'] ?? [],
                    'review_reason' => $normalized['review_reason'] ?? null,
                ];

                $this->logProcessing(
                    businessId: $businessId,
                    userId: $userId,
                    inputType: $inputType,
                    model: $model,
                    rawPayload: ['image_mime' => $mimeType, 'base64_length' => strlen($base64Image)],
                    parsedJson: $normalized ?: null,
                    confidenceFlags: $confidenceFlags,
                    latencyMs: $durationMs,
                    status: $status,
                    errorMessage: $errorMessage
                );
            }
        }
    }

    /**
     * Ekstraksi transaksi dari teks bebas menggunakan Gemini API.
     *
     * @return array<string, mixed>
     */
    public function extractFromText(
        string $text,
        ?string $businessType = null,
        ?int $businessId = null,
        ?int $userId = null
    ): array {
        $startTime = hrtime(true);
        $inputType = 'text';
        $model = "gemini:{$this->model}";
        $status = 'success';
        $errorMessage = null;
        $normalized = [];

        try {
            $this->ensureApiKey();

            $systemPrompt = TransactionJsonSchema::getSystemPrompt($businessType);

            $payload = [
                'system_instruction' => [
                    'parts' => [
                        ['text' => $systemPrompt],
                    ],
                ],
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            ['text' => $text],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'response_mime_type' => 'application/json',
                    'temperature' => 0.1,
                ],
            ];

            $endpoint = "{$this->baseUrl}/models/{$this->model}:generateContent?key={$this->apiKey}";

            $response = Http::timeout($this->timeout)
                ->retry($this->retryAttempts, 500, throw: false)
                ->post($endpoint, $payload);

            if (!$response->successful()) {
                $err = $this->extractErrorMessage($response);

                if ($this->isQuotaExceededError($response->status(), $err)) {
                    throw new AiQuotaExceededException('Kuota AI Gemini Anda sudah penuh atau token AI Anda sudah habis.');
                }

                throw new RuntimeException("Gemini API request failed [{$response->status()}]: {$err}");
            }

            $responseData = $response->json();
            $rawContent = $responseData['candidates'][0]['content']['parts'][0]['text'] ?? '';
            $cleaned = TransactionJsonSchema::cleanJsonString($rawContent);
            $decoded = json_decode($cleaned, true);

            if (!is_array($decoded)) {
                throw new RuntimeException("Gagal mem-parsing format JSON dari respon Gemini: " . json_last_error_msg());
            }

            $normalized = TransactionJsonSchema::validateAndNormalize($decoded);

            if (!empty($normalized['needs_review'])) {
                $status = 'needs_review';
            }

            return $normalized;
        } catch (\Throwable $e) {
            $status = 'failed';
            $errorMessage = $e->getMessage();
            throw $e;
        } finally {
            $durationMs = (int) round((hrtime(true) - $startTime) / 1e6);

            if ($businessId && $userId) {
                $confidenceFlags = [
                    'confidence_score' => $normalized['confidence_score'] ?? 0.0,
                    'needs_review' => $normalized['needs_review'] ?? false,
                    'unclear_fields' => $normalized['unclear_fields'] ?? [],
                    'review_reason' => $normalized['review_reason'] ?? null,
                ];

                $this->logProcessing(
                    businessId: $businessId,
                    userId: $userId,
                    inputType: $inputType,
                    model: $model,
                    rawPayload: ['text' => $text],
                    parsedJson: $normalized ?: null,
                    confidenceFlags: $confidenceFlags,
                    latencyMs: $durationMs,
                    status: $status,
                    errorMessage: $errorMessage
                );
            }
        }
    }

    /**
     * Catat log telemetri AI ke database.
     *
     * @param array<string, mixed>|null $rawPayload
     * @param array<string, mixed>|null $parsedJson
     * @param array<string, mixed>|null $confidenceFlags
     */
    public function logProcessing(
        int $businessId,
        int $userId,
        string $inputType,
        string $model,
        ?array $rawPayload,
        ?array $parsedJson,
        ?array $confidenceFlags,
        int $latencyMs,
        string $status = 'success',
        ?string $errorMessage = null
    ): ?AiProcessingLog {
        try {
            return AiProcessingLog::create([
                'business_id' => $businessId,
                'user_id' => $userId,
                'raw_input_type' => $inputType,
                'model_used' => $model,
                'raw_payload' => $rawPayload ? json_encode($rawPayload, JSON_UNESCAPED_UNICODE) : null,
                'parsed_json' => $parsedJson,
                'confidence_flags' => $confidenceFlags,
                'status' => $status,
                'processing_time_ms' => $latencyMs,
                'error_message' => $errorMessage,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed to write ai_processing_log for Gemini: ' . $e->getMessage(), [
                'business_id' => $businessId,
                'user_id' => $userId,
            ]);
            return null;
        }
    }

    /**
     * Pastikan GEMINI_API_KEY telah dikonfigurasi.
     *
     * @throws RuntimeException
     */
    protected function ensureApiKey(): void
    {
        if (empty(trim($this->apiKey))) {
            throw new RuntimeException("GEMINI_API_KEY belum dikonfigurasi pada file .env atau services.gemini.api_key.");
        }
    }

    /**
     * Ekstrak pesan kesalahan dari respon HTTP Gemini.
     */
    protected function extractErrorMessage(Response $response): string
    {
        $json = $response->json();
        return $json['error']['message'] ?? $json['message'] ?? $response->body() ?: 'Unknown Gemini API error';
    }

    /**
     * Deteksi apakah respon HTTP mengindikasikan Kuota / Rate Limit / Token AI Gemini telah habis.
     */
    public function isQuotaExceededError(int $status, string $message): bool
    {
        if ($status === 429) {
            return true;
        }

        $lower = strtolower($message);

        return str_contains($lower, 'rate_limit')
            || str_contains($lower, 'quota')
            || str_contains($lower, 'resource_exhausted')
            || str_contains($lower, 'limit exceeded');
    }
}
