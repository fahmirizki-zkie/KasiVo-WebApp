<?php

namespace Tests\Unit;

use App\Services\Ai\TransactionJsonSchema;
use PHPUnit\Framework\TestCase;

class TransactionJsonSchemaTest extends TestCase
{
    public function test_system_prompt_contains_guidelines_and_json_schema(): void
    {
        $prompt = TransactionJsonSchema::getSystemPrompt('Kedai Kopi', '2026-11-11');

        $this->assertStringContainsString('Kedai Kopi', $prompt);
        $this->assertStringContainsString('2026-11-11', $prompt);
        $this->assertStringContainsString('"type": "cash_out" | "cash_in"', $prompt);
        $this->assertStringContainsString('"category": "bahan_baku" | "operasional" | "penjualan"', $prompt);
        $this->assertStringContainsString('"items"', $prompt);
    }

    public function test_clean_json_string_removes_markdown_and_wrappers(): void
    {
        $markdownPayload = <<<JSON
```json
{
  "type": "cash_out",
  "category": "bahan_baku",
  "total_amount": 50000
}
```
JSON;

        $cleaned = TransactionJsonSchema::cleanJsonString($markdownPayload);
        $this->assertJson($cleaned);
        $this->assertEquals(['type' => 'cash_out', 'category' => 'bahan_baku', 'total_amount' => 50000], json_decode($cleaned, true));

        // Test with conversational preamble and suffix
        $chatPayload = 'Halo kak, berikut hasil ekstraksi notanya: {"type":"cash_out","total_amount":75000} Semoga membantu usahanya!';
        $cleanedChat = TransactionJsonSchema::cleanJsonString($chatPayload);
        $this->assertJson($cleanedChat);
        $this->assertEquals(['type' => 'cash_out', 'total_amount' => 75000], json_decode($cleanedChat, true));
    }

    public function test_sanitize_numeric_handles_indonesian_formats(): void
    {
        $this->assertEquals(50000.0, TransactionJsonSchema::sanitizeNumeric('50rb'));
        $this->assertEquals(25000.0, TransactionJsonSchema::sanitizeNumeric('25k'));
        $this->assertEquals(1500000.0, TransactionJsonSchema::sanitizeNumeric('1.5jt'));
        $this->assertEquals(75000.0, TransactionJsonSchema::sanitizeNumeric('75.000'));
        $this->assertEquals(120000.50, TransactionJsonSchema::sanitizeNumeric('Rp 120.000,50'));
        $this->assertEquals(10000.0, TransactionJsonSchema::sanitizeNumeric(10000));
        $this->assertEquals(0.0, TransactionJsonSchema::sanitizeNumeric('tidak ada'));
    }

    public function test_validate_and_normalize_valid_cash_out_data(): void
    {
        $input = [
            'type' => 'cash_out',
            'category' => 'bahan_baku',
            'total_amount' => '150.000',
            'transaction_date' => '2026-11-10',
            'merchant_name' => 'Toko Sembako Berkah',
            'notes' => 'Beli beras dan telur',
            'confidence_score' => 0.95,
            'needs_review' => false,
            'items' => [
                [
                    'item_name' => 'Beras Pandan Wangi 5kg',
                    'quantity' => 1,
                    'unit' => 'karung',
                    'price_per_unit' => 75000,
                    'subtotal' => 75000,
                ],
                [
                    'item_name' => 'Telur Ayam',
                    'quantity' => 2.5,
                    'unit' => 'kg',
                    'price_per_unit' => 30000,
                    'subtotal' => 75000,
                ],
            ],
        ];

        $result = TransactionJsonSchema::validateAndNormalize($input);

        $this->assertEquals('cash_out', $result['type']);
        $this->assertEquals('bahan_baku', $result['category']);
        $this->assertEquals(150000.0, $result['total_amount']);
        $this->assertEquals('2026-11-10', $result['transaction_date']);
        $this->assertEquals('Toko Sembako Berkah', $result['merchant_name']);
        $this->assertFalse($result['needs_review']);
        $this->assertEmpty($result['unclear_fields']);
        $this->assertCount(2, $result['items']);
        $this->assertEquals(75000.0, $result['items'][0]['subtotal']);
    }

    public function test_validate_and_normalize_reconciles_total_when_zero(): void
    {
        $input = [
            'type' => 'cash_out',
            'total_amount' => 0,
            'items' => [
                [
                    'item_name' => 'Gas Elpiji 3kg',
                    'quantity' => 2,
                    'unit' => 'tabung',
                    'price_per_unit' => 22000,
                    'subtotal' => 44000,
                ],
            ],
        ];

        $result = TransactionJsonSchema::validateAndNormalize($input);

        $this->assertEquals(44000.0, $result['total_amount']);
        $this->assertFalse($result['needs_review']);
    }

    public function test_validate_and_normalize_flags_needs_review_when_math_mismatch(): void
    {
        $input = [
            'type' => 'cash_out',
            'total_amount' => 200000, // Total 200k tapi subtotal cuma 50k
            'items' => [
                [
                    'item_name' => 'Kopi Arabika',
                    'quantity' => 1,
                    'price_per_unit' => 50000,
                    'subtotal' => 50000,
                ],
            ],
        ];

        $result = TransactionJsonSchema::validateAndNormalize($input);

        $this->assertTrue($result['needs_review']);
        $this->assertContains('total_amount', $result['unclear_fields']);
        $this->assertStringContainsString('tidak sesuai dengan total rincian barang', $result['review_reason']);
    }

    public function test_validate_and_normalize_handles_cash_in_defaults(): void
    {
        $input = [
            'type' => 'cash_in',
            'total_amount' => 500000,
            'notes' => 'Omzet jualan siang',
        ];

        $result = TransactionJsonSchema::validateAndNormalize($input);

        $this->assertEquals('cash_in', $result['type']);
        $this->assertEquals('penjualan', $result['category']);
        $this->assertEquals(500000.0, $result['total_amount']);
    }

    public function test_low_confidence_score_triggers_needs_review(): void
    {
        $input = [
            'type' => 'cash_out',
            'total_amount' => 50000,
            'confidence_score' => 0.65,
        ];

        $result = TransactionJsonSchema::validateAndNormalize($input);

        $this->assertTrue($result['needs_review']);
        $this->assertStringContainsString('Tingkat keyakinan AI rendah', $result['review_reason']);
    }
}

