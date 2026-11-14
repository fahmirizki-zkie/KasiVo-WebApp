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
      "unit": "kg" | "liter" | "pcs" | "ikat" | "porsi" | "pack" | "dus" | "tabung" | "bungkus" | "botol" | "cup",
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
   - Wajib bernilai numerik murni (misal: "50rb" -> 50000, "25k" -> 25000, "15.000" -> 15000, "1.5jt" -> 1500000, "3 rebu" -> 3000). Jangan sertakan simbol "Rp" atau titik koma.
4. "transaction_date":
   - Format wajib "YYYY-MM-DD".
   - Jika teks menyebut "kemarin", hitung H-1 dari referensi {$today}. Jika "hari ini" / "tadi sore" / tanggal tidak tertera di struk, gunakan {$today}.
5. "items":
   - Pisahkan setiap barang ke dalam array `items`.
   - Konversi kuantitas pecahan bahasa pasar: "setengah kilo" -> quantity: 0.5, unit: "kg"; "seperempat" -> quantity: 0.25.
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
     * Generate system prompt specifically for Digital Sales & Settlement screens (GoFood, GrabFood, Shopee, QRIS, POS).
     */
    public static function getDigitalSalesPrompt(?string $platformHint = null, ?string $currentDate = null): string
    {
        $today = $currentDate ?? Carbon::now()->toDateString();
        $hint = ($platformHint && $platformHint !== 'auto') 
            ? "Petunjuk platform yang dipilih pengguna: {$platformHint}." 
            : "Identifikasi platform secara otomatis dari logo, nama aplikasi, atau format tampilan struk/layar.";

        return <<<PROMPT
Anda adalah asisten AI akuntansi pintar untuk KasiVo, aplikasi pembukuan kas UMKM F&B di Indonesia.
Tugas khusus Anda: Menganalisis tangkapan layar (screenshot) ringkasan penjualan harian atau mutasi dari aplikasi merchant (seperti GoBiz / GoFood, GrabMerchant / GrabFood, Shopee Partner / ShopeeFood, QRIS MPM/CPM, mutasi bank settlement, atau aplikasi kasir POS).

{$hint}
Tanggal hari ini sebagai referensi: {$today}.

Tugas Anda: Ekstrak data penjualan digital ke dalam STRICT JSON persis sesuai skema di bawah. JANGAN menyertakan teks penjelasan apa pun di luar JSON.

SKEMA JSON RESMI PENJUALAN DIGITAL:
{
  "platform": "gofood" | "grabfood" | "shopeefood" | "qris" | "pos" | "other",
  "transaction_date": "YYYY-MM-DD",
  "total_amount": 0,
  "gross_amount": 0,
  "fee_amount": 0,
  "net_amount": 0,
  "order_count": 0,
  "merchant_name": "Nama Toko / Resto / Merchant di layar atau null",
  "notes": "Ringkasan penjualan digital singkat (cth: 'Rekap Penjualan GoFood 13 Nov 2026 - 15 Pesanan')",
  "confidence_score": 0.95,
  "needs_review": false,
  "unclear_fields": [],
  "review_reason": null,
  "items": [
    {
      "item_name": "Nama menu / pesanan jika tertera",
      "quantity": 1.0,
      "price_per_unit": 0,
      "subtotal": 0
    }
  ]
}

ATURAN EKSTRAKSI PENTING:
1. "platform":
   - "gofood": jika tertera GoBiz, GoFood, GoPay, Super Indo / Mitra Gojek.
   - "grabfood": jika tertera Grab, GrabMerchant, OVO Merchant, GrabFood.
   - "shopeefood": jika tertera Shopee Partner, ShopeePay, ShopeeFood.
   - "qris": jika tertera QRIS, settlement QRIS, QRIS MPM, BCA Merchant, Mandiri QRIS, dsb.
   - "pos": jika berasal dari aplikasi kasir seperti Moka, Majoo, Pawoon, Olsera, Kasir Pintar.
   - "other": jika tidak dikenali atau dari rekening bank umum / aplikasi lain.
2. "total_amount" & "net_amount":
   - Nilai uang bersih (net) yang diterima atau dicairkan ke rekening/saldo pedagang.
   - Wajib bernilai numerik murni tanpa simbol 'Rp' atau titik/koma pemisah ribuan.
3. "gross_amount" & "fee_amount":
   - "gross_amount": Total omzet kotor pesanan sebelum dipotong komisi/biaya platform. Jika tidak tertera rincian potongan, samakan dengan "total_amount".
   - "fee_amount": Potongan komisi platform, MDR QRIS, biaya layanan aplikasi, atau diskon merchant. Jika tidak tertera, isi 0.
   - Pastikan jika gross dan fee tertera: total_amount = gross_amount - fee_amount.
4. "transaction_date":
   - Format wajib "YYYY-MM-DD".
   - Gunakan tanggal laporan/rekap transaksi yang tertera di screenshot. Jika tertulis 'Hari Ini' gunakan {$today}.
5. "order_count":
   - Jumlah transaksi / pesanan selesai yang berhasil jika tertera di layar (misal '15 pesanan selesai' -> 15). Jika tidak ada isi 0.
6. "confidence_score" & "needs_review":
   - Jika gambar buram, terpotong, atau angka nominal ambigu, berikan confidence_score < 0.70 dan set needs_review = true, serta jelaskan di review_reason.
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
     * Validate and normalize parsed Digital Sales payload.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public static function validateAndNormalizeDigitalSales(array $data, ?string $currentDate = null): array
    {
        $today = $currentDate ?? Carbon::now()->toDateString();

        // Platform normalization
        $allowedPlatforms = ['gofood', 'grabfood', 'shopeefood', 'qris', 'pos', 'other'];
        $rawPlatform = strtolower(trim((string) ($data['platform'] ?? 'other')));
        $platform = in_array($rawPlatform, $allowedPlatforms, true) ? $rawPlatform : 'other';

        // Transaction date
        $date = trim((string) ($data['transaction_date'] ?? ''));
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $date = $today;
        }

        // Amounts
        $totalAmount = self::sanitizeNumeric($data['total_amount'] ?? 0);
        $grossAmount = self::sanitizeNumeric($data['gross_amount'] ?? 0);
        $feeAmount = self::sanitizeNumeric($data['fee_amount'] ?? 0);
        $netAmount = self::sanitizeNumeric($data['net_amount'] ?? 0);

        // Harmonize amounts
        if ($totalAmount <= 0 && $netAmount > 0) {
            $totalAmount = $netAmount;
        } elseif ($totalAmount <= 0 && $grossAmount > 0) {
            $totalAmount = max(0.0, $grossAmount - $feeAmount);
        }

        if ($grossAmount <= 0 && $totalAmount > 0) {
            $grossAmount = $totalAmount + $feeAmount;
        }

        if ($netAmount <= 0 && $totalAmount > 0) {
            $netAmount = $totalAmount;
        }

        // Order count
        $orderCount = (int) max(0, $data['order_count'] ?? 0);

        // Merchant name
        $merchantName = !empty($data['merchant_name']) ? trim((string) $data['merchant_name']) : null;

        // Notes
        $platformLabels = [
            'gofood' => 'GoFood',
            'grabfood' => 'GrabFood',
            'shopeefood' => 'ShopeeFood',
            'qris' => 'QRIS',
            'pos' => 'Kasir POS',
            'other' => 'Penjualan Digital',
        ];
        $platformLabel = $platformLabels[$platform] ?? 'Penjualan Digital';
        $defaultNotes = "Rekap {$platformLabel} {$date}" . ($orderCount > 0 ? " ({$orderCount} pesanan)" : '');
        $notes = !empty($data['notes']) ? trim((string) $data['notes']) : $defaultNotes;

        // Confidence score and review flags
        $confidenceScore = isset($data['confidence_score']) ? (float) $data['confidence_score'] : 0.95;
        $confidenceScore = max(0.0, min(1.0, $confidenceScore));

        $needsReview = (bool) ($data['needs_review'] ?? false);
        $unclearFields = is_array($data['unclear_fields'] ?? null) ? $data['unclear_fields'] : [];
        $reviewReason = !empty($data['review_reason']) ? trim((string) $data['review_reason']) : null;

        if ($totalAmount <= 0) {
            $needsReview = true;
            $unclearFields[] = 'total_amount';
            if (!$reviewReason) {
                $reviewReason = 'Nominal total penjualan tidak terdeteksi atau bernilai 0.';
            }
        }

        if ($confidenceScore < 0.70) {
            $needsReview = true;
            if (!$reviewReason) {
                $reviewReason = 'Tingkat keyakinan AI rendah (' . round($confidenceScore * 100) . '%). Harap periksa ulang.';
            }
        }

        // Items breakdown
        $items = is_array($data['items'] ?? null) ? $data['items'] : [];
        $normalizedItems = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $itemName = trim((string) ($item['item_name'] ?? ''));
            if (!$itemName) {
                continue;
            }
            $qty = (float) max(0.1, (float) ($item['quantity'] ?? 1.0));
            $subtotal = self::sanitizeNumeric($item['subtotal'] ?? 0);
            $pricePerUnit = self::sanitizeNumeric($item['price_per_unit'] ?? ($qty > 0 ? $subtotal / $qty : 0));
            $normalizedItems[] = [
                'item_name' => $itemName,
                'quantity' => $qty,
                'unit' => 'porsi',
                'price_per_unit' => $pricePerUnit,
                'subtotal' => $subtotal ?: ($qty * $pricePerUnit),
            ];
        }

        return [
            'type' => self::TYPE_CASH_IN,
            'category' => self::CATEGORY_PENJUALAN,
            'platform' => $platform,
            'transaction_date' => $date,
            'total_amount' => (float) $totalAmount,
            'gross_amount' => (float) $grossAmount,
            'fee_amount' => (float) $feeAmount,
            'net_amount' => (float) $netAmount,
            'order_count' => $orderCount,
            'merchant_name' => $merchantName,
            'notes' => $notes,
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
        } elseif (str_contains($str, 'rb') || str_contains($str, 'k') || str_contains($str, 'ribu') || str_contains($str, 'rebu')) {
            $multiplier = 1000.0;
            $str = str_replace(['ribu', 'rebu', 'rb', 'k'], '', $str);
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
