<?php

namespace App\Services\Ai;

use App\Models\AiProcessingLog;
use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use RuntimeException;

class GroqService
{
    protected string $apiKey;
    protected string $baseUrl;
    protected string $textModel;
    protected string $visionModel;
    protected string $whisperModel;
    protected int $timeout;
    protected int $retryAttempts;

    public function __construct(
        ?string $apiKey = null,
        ?string $baseUrl = null,
        ?string $textModel = null,
        ?string $visionModel = null,
        ?string $whisperModel = null,
        ?int $timeout = null,
        ?int $retryAttempts = null
    ) {
        $this->apiKey = $apiKey ?? (string) config('services.groq.api_key', '');
        $this->baseUrl = rtrim($baseUrl ?? (string) config('services.groq.base_url', 'https://api.groq.com/openai/v1'), '/');
        $this->textModel = $textModel ?? (string) config('services.groq.text_model', 'qwen/qwen3.8-27b');
        $this->visionModel = $visionModel ?? (string) config('services.groq.vision_model', 'qwen/qwen3.8-27b');
        $this->whisperModel = $whisperModel ?? (string) config('services.groq.whisper_model', 'whisper-large-v3-turbo');
        $this->timeout = $timeout ?? (int) config('services.groq.timeout', 30);
        $this->retryAttempts = $retryAttempts ?? (int) config('services.groq.retry_attempts', 2);
    }

    /**
     * Send a raw chat completion request to Groq API.
     *
     * @param array<int, array<string, mixed>> $messages
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     * @throws RuntimeException
     */
    public function chat(array $messages, ?string $model = null, bool $jsonMode = true, array $options = []): array
    {
        $this->ensureApiKey();

        $selectedModel = $model ?? $this->textModel;
        $payload = array_merge([
            'model' => $selectedModel,
            'messages' => $messages,
            'temperature' => 0.1,
        ], $options);

        if ($jsonMode) {
            $payload['response_format'] = ['type' => 'json_object'];
        }

        $response = Http::withToken($this->apiKey)
            ->timeout($this->timeout)
            ->retry($this->retryAttempts, 500, throw: false)
            ->post("{$this->baseUrl}/chat/completions", $payload);

        if (!$response->successful()) {
            $errorMessage = $this->extractErrorMessage($response);
            Log::error('Groq Chat Completion Failed', [
                'status' => $response->status(),
                'error' => $errorMessage,
                'model' => $selectedModel,
            ]);
            throw new RuntimeException("Groq API request failed [{$response->status()}]: {$errorMessage}");
        }

        $responseData = $response->json();
        $rawContent = $responseData['choices'][0]['message']['content'] ?? '';

        if ($jsonMode) {
            $cleaned = TransactionJsonSchema::cleanJsonString($rawContent);
            $decoded = json_decode($cleaned, true);

            if (!is_array($decoded)) {
                Log::error('Groq JSON Decode Failed', [
                    'raw_content' => $rawContent,
                    'cleaned' => $cleaned,
                    'json_last_error' => json_last_error_msg(),
                ]);
                throw new RuntimeException("Gagal mem-parsing format JSON dari respon AI: " . json_last_error_msg());
            }

            return $decoded;
        }

        return ['content' => $rawContent];
    }

    /**
     * Extract structured transaction data from free-form text.
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
        $model = $this->textModel;
        $status = 'success';
        $errorMessage = null;
        $normalized = [];

        try {
            $systemPrompt = TransactionJsonSchema::getSystemPrompt($businessType);
            $messages = [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $text],
            ];

            $rawParsed = $this->chat($messages, $model, jsonMode: true);
            $normalized = TransactionJsonSchema::validateAndNormalize($rawParsed);

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
     * Extract structured transaction data from a receipt image (base64).
     *
     * @return array<string, mixed>
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
        $model = $this->visionModel;
        $status = 'success';
        $errorMessage = null;
        $normalized = [];

        try {
            $systemPrompt = TransactionJsonSchema::getSystemPrompt($businessType);

            // Strip prefix if already in data:image/... format
            if (str_contains($base64Image, ';base64,')) {
                $parts = explode(';base64,', $base64Image);
                $base64Image = end($parts);
            }

            $userPromptText = "Ekstrak semua informasi transaksi dari foto struk/nota ini ke dalam format JSON sesuai aturan.";
            if ($additionalContext) {
                $userPromptText .= "\nKonteks tambahan dari pengguna: {$additionalContext}";
            }

            $messages = [
                ['role' => 'system', 'content' => $systemPrompt],
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => $userPromptText,
                        ],
                        [
                            'type' => 'image_url',
                            'image_url' => [
                                'url' => "data:{$mimeType};base64,{$base64Image}",
                            ],
                        ],
                    ],
                ],
            ];

            $rawParsed = $this->chat($messages, $model, jsonMode: true);
            $normalized = TransactionJsonSchema::validateAndNormalize($rawParsed);

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
                    rawPayload: [
                        'mime_type' => $mimeType,
                        'image_size_approx' => strlen($base64Image),
                        'context' => $additionalContext,
                    ],
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
     * Transcribe speech audio to Indonesian text using Groq Whisper.
     */
    public function transcribeAudio(
        string $audioFilePath,
        string $language = 'id',
        ?int $businessId = null,
        ?int $userId = null
    ): string {
        $this->ensureApiKey();

        if (!file_exists($audioFilePath) || !is_readable($audioFilePath)) {
            throw new InvalidArgumentException("File audio tidak ditemukan atau tidak dapat dibaca: {$audioFilePath}");
        }

        $startTime = hrtime(true);
        $inputType = 'audio';
        $model = $this->whisperModel;
        $status = 'success';
        $errorMessage = null;
        $transcribedText = '';

        try {
            $fileContent = file_get_contents($audioFilePath);
            $fileName = basename($audioFilePath);

            $response = Http::withToken($this->apiKey)
                ->timeout($this->timeout)
                ->retry($this->retryAttempts, 500, throw: false)
                ->attach('file', $fileContent, $fileName)
                ->post("{$this->baseUrl}/audio/transcriptions", [
                    'model' => $model,
                    'language' => $language,
                    'response_format' => 'json',
                    'temperature' => 0.0,
                ]);

            if (!$response->successful()) {
                $err = $this->extractErrorMessage($response);
                throw new RuntimeException("Groq Whisper STT failed [{$response->status()}]: {$err}");
            }

            $result = $response->json();
            $transcribedText = trim((string) ($result['text'] ?? ''));

            return $transcribedText;
        } catch (\Throwable $e) {
            $status = 'failed';
            $errorMessage = $e->getMessage();
            throw $e;
        } finally {
            $durationMs = (int) round((hrtime(true) - $startTime) / 1e6);

            if ($businessId && $userId) {
                $this->logProcessing(
                    businessId: $businessId,
                    userId: $userId,
                    inputType: $inputType,
                    model: $model,
                    rawPayload: [
                        'file_name' => basename($audioFilePath),
                        'file_size' => file_exists($audioFilePath) ? filesize($audioFilePath) : 0,
                    ],
                    parsedJson: $transcribedText ? ['transcription' => $transcribedText] : null,
                    confidenceFlags: ['language' => $language],
                    latencyMs: $durationMs,
                    status: $status,
                    errorMessage: $errorMessage
                );
            }
        }
    }

    /**
     * Record execution metrics and audit log into ai_processing_logs table.
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
            Log::warning('Failed to write ai_processing_log: ' . $e->getMessage(), [
                'business_id' => $businessId,
                'user_id' => $userId,
            ]);
            return null;
        }
    }

    /**
     * Ensure GROQ_API_KEY is configured.
     *
     * @throws RuntimeException
     */
    protected function ensureApiKey(): void
    {
        if (empty($this->apiKey)) {
            throw new RuntimeException("GROQ_API_KEY belum dikonfigurasi pada file .env atau services.groq.api_key.");
        }
    }

    /**
     * Extract human-readable error from HTTP response.
     */
    protected function extractErrorMessage(Response $response): string
    {
        $json = $response->json();
        return $json['error']['message'] ?? $json['message'] ?? $response->body() ?: 'Unknown API error';
    }
}

