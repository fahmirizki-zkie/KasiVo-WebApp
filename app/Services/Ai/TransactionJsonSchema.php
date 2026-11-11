<?php

namespace App\Services\Ai;

use Carbon\Carbon;
use InvalidArgumentException;

class TransactionJsonSchema
{
    /**
     * Supported transaction types.
     */
    public const TYPE_CASH_OUT = 'cash_out';
    public const TYPE_CASH_IN = 'cash_in';

    /**
     * Supported categories.
     */
    public const CATEGORY_BAHAN_BAKU = 'bahan_baku';
    public const CATEGORY_OPERASIONAL = 'operasional';
    public const CATEGORY_PENJUALAN = 'penjualan';

    /**
     * Generate standard system prompt for LLM parsing.
     */
    public static function getSystemPrompt(?string $businessType = null, ?string $currentDate = null): string
    {
        $today = $currentDate ?? Carbon::now()->toDateString();
        $bizContext = $businessType ? "Jenis usaha pengguna: {$businessType}." : "Usaha mikro kuliner (F&B UMKM: warung makan, kedai kopi, gerobak, bakery, dsb).";

        return <<<PROMPT
Anda adalah asisten AI akuntansi pintar untuk KasiVo, aplikasi pembukuan kas UMKM F&B di Indonesia.
{$bizContext}
Tanggal hari ini sebagai referensi: {$today}.

Tugas Anda: Ekstrak data transaksi dari teks/foto/transkrip suara pengguna dan kembalikan STRICT JSON persis sesuai skema di bawah. JANGAN menyertakan penjelasan tambahan atau format di luar JSON murni.

SKEMA JSON RESMI:
{
  "type": "cash_out" | "cash_in",
  "category": "bahan_baku" | "operasional" | "penjualan",
  "total_amount": 0,
  "transaction_date": "YYYY-MM-DD",
  "merchant_name": "Nama Toko / Supplier / null",
  "notes": "Ringkasan transaksi singkat dan jelas dalam Bahasa Indonesia",
  "confidence_score": 0.95,
  "needs_review": false,
  "unclear_fields": [],
  "review_reason": null,
  "items": [
    {
      "item_name": "Nama Item",
      "quantity": 1.0,
      "unit": "kg" | "liter" | "pcs" | "ikat" | "porsi" | "pack" | "dus",
      "price_per_unit": 0,
      "subtotal": 0
    }
  ]
}

ATURAN KLASIFIKASI & PARSING:
1. "type":
   - "cash_out" (PENGELUARAN): belanja bahan, beli sembako, bayar gas/listrik, beli cup/plastik, es batu, dll. (Default untuk nota belanjaan).
   - "cash_in" (PEMASUKAN): omzet penjualan harian, pesanan katering, terima uang jualan.
2. "category":
   - "bahan_baku": telur, ayam, daging, sayuran, beras, bumbu, tepung, minyak goreng, susu, biji kopi, packaging, cup plastik, sedotan, kantong kresek, mika makanan.
   - "operasional": tabung gas LPG, token listrik PLN, air galon, es balok/kristal, sabun cuci, sapu/lap, bensin/transportasi, sewa tempat, perbaikan kompor.
   - "penjualan": pendapatan jualan harian/omzet.
3. "total_amount":
   - Wajib bernilai numerik murni (misal: "50rb" -> 50000, "15.000" -> 15000, "2jt" -> 2000000). Jangan sertakan simbol "Rp" atau titik koma.
4. "transaction_date":
   - Format wajib "YYYY-MM-DD".
   - Jika teks menyebut "kemarin", hitung H-1 dari referensi {$today}. Jika "hari ini" / "tadi sore" / tanggal tidak tertera di struk, gunakan {$today}.
5. "items":
   - Pisahkan setiap barang ke dalam array `items`.
   - Pastikan subtotal = quantity * price_per_unit.
   - Jika rincian item tidak ada di nota (misal hanya total bayar), buat 1 item umum dengan nama transaksi tersebut.
6. "confidence_score" & "needs_review":
   - Nilai 0.0 hingga 1.0.
   - Beri needs_review = true dan cantumkan kolom yang diragukan di unclear_fields jika:
     a) Foto struk buram / robek / angka total tidak terbaca jelas.
     b) Rincian subtotal item tidak cocok dengan total akhir nota.
     c) Teks tidak jelas / ambigu.
PROMPT;
    }

    /**
     * Clean raw string returned by LLM to isolate JSON payload.
     */
    public static function cleanJsonString(string $rawContent): string
    {
        $content = trim($rawContent);

        // Remove markdown ```json ... ``` blocks
        if (preg_match('/^```(?:json)?\s*(.*?)\s*```$/is', $content, $matches)) {
            $content = trim($matches[1]);
        }

        // If there's still text surrounding the outermost JSON object
        $firstBrace = strpos($content, '{');
        $lastBrace = strrpos($content, '}');

        if ($firstBrace !== false && $lastBrace !== false && $lastBrace > $firstBrace) {
            $content = substr($content, $firstBrace, $lastBrace - $firstBrace + 1);
        }

        return trim($content);
    }

    /**
     * Validate and normalize parsed JSON structure.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     * @throws InvalidArgumentException
     */
    public static function validateAndNormalize(array $data, ?string $referenceDate = null): array
    {
        $today = $referenceDate ?? Carbon::now()->toDateString();

        // 1. Normalize Type
        $rawType = strtolower((string) ($data['type'] ?? ''));
        $type = in_array($rawType, [self::TYPE_CASH_IN, self::TYPE_CASH_OUT], true)
            ? $rawType
            : self::TYPE_CASH_OUT;

        // 2. Normalize Category
        $rawCategory = strtolower((string) ($data['category'] ?? ''));
        if ($type === self::TYPE_CASH_IN) {
            $category = self::CATEGORY_PENJUALAN;
        } else {
            $category = in_array($rawCategory, [self::CATEGORY_BAHAN_BAKU, self::CATEGORY_OPERASIONAL], true)
                ? $rawCategory
                : self::CATEGORY_BAHAN_BAKU;
        }

        // 3. Normalize Date
        $rawDate = (string) ($data['transaction_date'] ?? '');
        try {
            $date = Carbon::parse($rawDate)->toDateString();
        } catch (\Throwable $e) {
            $date = $today;
        }

        // 4. Normalize Total Amount
        $rawAmount = $data['total_amount'] ?? 0;
        $totalAmount = self::sanitizeNumeric($rawAmount);

        // 5. Merchant & Notes
        $merchantName = !empty($data['merchant_name']) ? trim((string) $data['merchant_name']) : null;
        $notes = !empty($data['notes']) ? trim((string) $data['notes']) : null;

        // 6. Normalize Items
        $rawItems = is_array($data['items'] ?? null) ? $data['items'] : [];
        $normalizedItems = [];
        $sumSubtotals = 0.0;

        foreach ($rawItems as $item) {
            if (!is_array($item)) {
                continue;
            }

            $name = trim((string) ($item['item_name'] ?? 'Item'));
            if ($name === '') {
                $name = 'Item';
            }

            $qty = max(0.01, self::sanitizeNumeric($item['quantity'] ?? 1.0));
            $unit = trim((string) ($item['unit'] ?? 'pcs')) ?: 'pcs';
            $pricePerUnit = self::sanitizeNumeric($item['price_per_unit'] ?? 0);
            $subtotal = self::sanitizeNumeric($item['subtotal'] ?? ($qty * $pricePerUnit));

            if ($subtotal === 0.0 && $pricePerUnit > 0) {
                $subtotal = $qty * $pricePerUnit;
            } elseif ($pricePerUnit === 0.0 && $qty > 0 && $subtotal > 0) {
                $pricePerUnit = round($subtotal / $qty, 2);
            }

            $sumSubtotals += $subtotal;

            $normalizedItems[] = [
                'item_name' => $name,
                'quantity' => (float) $qty,
                'unit' => $unit,
                'price_per_unit' => (float) $pricePerUnit,
                'subtotal' => (float) $subtotal,
            ];
        }

        // Mathematical cross-check & reconciliation
        $unclearFields = is_array($data['unclear_fields'] ?? null) ? $data['unclear_fields'] : [];
        $needsReview = (bool) ($data['needs_review'] ?? false);
        $reviewReason = !empty($data['review_reason']) ? (string) $data['review_reason'] : null;

        // If total_amount is 0 but item subtotals exist, adopt the sum of subtotals
        if ($totalAmount <= 0.0 && $sumSubtotals > 0.0) {
            $totalAmount = $sumSubtotals;
        }

        // If total_amount > 0 and items exist, verify matching totals
        if ($totalAmount > 0.0 && count($normalizedItems) > 0 && $sumSubtotals > 0.0) {
            $discrepancy = abs($totalAmount - $sumSubtotals);
            // Tolerate small rounding discrepancies up to Rp 100
            if ($discrepancy > 100) {
                $needsReview = true;
                if (!in_array('total_amount', $unclearFields, true)) {
                    $unclearFields[] = 'total_amount';
                }
                $discrepancyMsg = "Total transaksi (Rp " . number_format($totalAmount, 0, ',', '.') . ") tidak sesuai dengan total rincian barang (Rp " . number_format($sumSubtotals, 0, ',', '.') . ").";
                $reviewReason = $reviewReason ? "{$reviewReason} | {$discrepancyMsg}" : $discrepancyMsg;
            }
        }

        // If total_amount is still 0, definitely needs review
        if ($totalAmount <= 0.0) {
            $needsReview = true;
            if (!in_array('total_amount', $unclearFields, true)) {
                $unclearFields[] = 'total_amount';
            }
            $zeroMsg = "Nominal transaksi tidak terdeteksi atau bernilai 0.";
            $reviewReason = $reviewReason ? "{$reviewReason} | {$zeroMsg}" : $zeroMsg;
        }

        // Confidence score
        $rawConfidence = $data['confidence_score'] ?? 0.85;
        $confidenceScore = is_numeric($rawConfidence) ? max(0.0, min(1.0, (float) $rawConfidence)) : 0.85;

        if ($confidenceScore < 0.75) {
            $needsReview = true;
            if (!$reviewReason) {
                $reviewReason = "Tingkat keyakinan AI rendah (" . round($confidenceScore * 100) . "%). Harap periksa ulang.";
            }
        }

        return [
            'type' => $type,
            'category' => $category,
            'total_amount' => (float) $totalAmount,
            'transaction_date' => $date,
            'merchant_name' => $merchantName,
            'notes' => $notes ?: ($type === self::TYPE_CASH_IN ? 'Penerimaan Penjualan' : 'Pengeluaran Usaha'),
            'confidence_score' => $confidenceScore,
            'needs_review' => $needsReview,
            'unclear_fields' => array_values(array_unique($unclearFields)),
            'review_reason' => $reviewReason,
            'items' => $normalizedItems,
        ];
    }

    /**
     * Clean numeric representations commonly found in Indonesian text ("50rb", "25.000,50", "Rp 15000").
     */
    public static function sanitizeNumeric(mixed $value): float
    {
        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        if (!is_string($value)) {
            return 0.0;
        }

        $str = strtolower(trim($value));

        // Multipliers
        $multiplier = 1.0;
        if (str_contains($str, 'jt') || str_contains($str, 'juta')) {
            $multiplier = 1000000.0;
            $str = str_replace(['juta', 'jt'], '', $str);
        } elseif (str_contains($str, 'rb') || str_contains($str, 'k') || str_contains($str, 'ribu')) {
            $multiplier = 1000.0;
            $str = str_replace(['ribu', 'rb', 'k'], '', $str);
        }

        // Remove currency prefixes and non-numeric chars except separators
        $cleaned = preg_replace('/[^\d.,]/', '', $str);
        if (!$cleaned) {
            return 0.0;
        }

        // Handle cases:
        // 1. Both dot and comma present (e.g. 120.000,50 or 120,000.50)
        if (str_contains($cleaned, '.') && str_contains($cleaned, ',')) {
            $lastDot = strrpos($cleaned, '.');
            $lastComma = strrpos($cleaned, ',');
            if ($lastComma > $lastDot) {
                // Indonesian format: 120.000,50 -> dot is thousand, comma is decimal
                $cleaned = str_replace('.', '', $cleaned);
                $cleaned = str_replace(',', '.', $cleaned);
            } else {
                // Standard US format: 120,000.50 -> comma is thousand, dot is decimal
                $cleaned = str_replace(',', '', $cleaned);
            }
        } elseif (str_contains($cleaned, '.')) {
            // Only dot present
            // If matches thousand separator pattern (e.g. 75.000 or 1.500.000)
            if (preg_match('/(?:\.\d{3})+$/', $cleaned)) {
                $cleaned = str_replace('.', '', $cleaned);
            }
        } elseif (str_contains($cleaned, ',')) {
            // Only comma present
            if (preg_match('/(?:,\d{3})+$/', $cleaned)) {
                $cleaned = str_replace(',', '', $cleaned);
            } else {
                // Comma decimal (e.g. 2,5)
                $cleaned = str_replace(',', '.', $cleaned);
            }
        }

        return is_numeric($cleaned) ? ((float) $cleaned * $multiplier) : 0.0;
    }
}
