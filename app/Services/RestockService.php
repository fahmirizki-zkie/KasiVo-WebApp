<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Carbon\Carbon;

class RestockService
{
    /**
     * Ambang batas minimal rentang hari histori untuk analisis restock.
     */
    public const THRESHOLD_DAYS = 7;

    /**
     * Klausul transparansi resmi KasiVo (DoD KV-046: Tidak mengklaim stok aktual).
     */
    public const DISCLAIMER = 'Saran restock didasarkan pada analisis pola interval pembelian rutin (kebiasaan belanja berulang), BUKAN melacak atau mengklaim jumlah fisik sisa stok aktual di tempat usaha Anda.';

    /**
     * Mengagregasi histori pembelian bahan baku per item untuk suatu bisnis.
     * Sesuai DoD KV-045: Menghitung frekuensi pembelian, total kuantitas, unit dominan,
     * serta ringkasan tanggal pertama & terakhir pembelian.
     */
    public function getItemPurchaseHistory(Business $business, ?Carbon $since = null, ?string $category = null): array
    {
        $query = TransactionItem::query()
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->where('transactions.business_id', $business->id)
            ->where('transactions.type', 'cash_out')
            ->where('transactions.is_cash_settled', true)
            ->when($category, fn($q) => $q->where('transactions.category', $category))
            ->when($since, fn($q) => $q->whereDate('transactions.transaction_date', '>=', $since))
            ->select([
                'transaction_items.*',
                'transactions.transaction_date',
                'transactions.category as transaction_category',
            ])
            ->orderBy('transactions.transaction_date', 'asc');

        $items = $query->get();

        if ($items->isEmpty()) {
            return [];
        }

        $grouped = [];

        foreach ($items as $item) {
            $rawName = trim((string) $item->item_name);
            if ($rawName === '') {
                continue;
            }

            $canonicalKey = mb_strtolower($rawName);

            if (!isset($grouped[$canonicalKey])) {
                $grouped[$canonicalKey] = [
                    'raw_name' => $rawName,
                    'canonical_name' => $canonicalKey,
                    'display_name' => $this->normalizeItemName($rawName),
                    'units' => [],
                    'quantities' => [],
                    'subtotals' => [],
                    'unit_prices' => [],
                    'dates' => [],
                    'transaction_ids' => [],
                ];
            }

            $dateStr = Carbon::parse($item->transaction_date)->toDateString();
            $grouped[$canonicalKey]['dates'][] = $dateStr;
            $grouped[$canonicalKey]['transaction_ids'][] = $item->transaction_id;

            $qty = (float) $item->quantity;
            if ($qty <= 0) {
                $qty = 1.0;
            }
            $grouped[$canonicalKey]['quantities'][] = $qty;

            $unit = trim((string) $item->unit);
            if ($unit !== '') {
                $grouped[$canonicalKey]['units'][] = mb_strtolower($unit);
            }

            $subtotal = (float) $item->subtotal;
            if ($subtotal <= 0 && (float) $item->unit_price > 0) {
                $subtotal = (float) $item->unit_price * $qty;
            }
            $grouped[$canonicalKey]['subtotals'][] = $subtotal;

            if ((float) $item->unit_price > 0) {
                $grouped[$canonicalKey]['unit_prices'][] = (float) $item->unit_price;
            }
        }

        $aggregated = [];
        $today = Carbon::today();

        foreach ($grouped as $key => $data) {
            // Urutan tanggal unik kronologis
            $uniqueDates = array_values(array_unique($data['dates']));
            sort($uniqueDates);

            $purchaseCount = count($data['transaction_ids']);
            $uniqueDaysCount = count($uniqueDates);

            $totalQuantity = round(array_sum($data['quantities']), 2);

            // Unit dominan (modus satuan belanja)
            $dominantUnit = 'item';
            if (!empty($data['units'])) {
                $unitCounts = array_count_values($data['units']);
                arsort($unitCounts);
                $dominantUnit = array_key_first($unitCounts);
            }

            $totalSpent = round(array_sum($data['subtotals']), 2);

            $avgUnitPrice = 0.0;
            if ($totalQuantity > 0 && $totalSpent > 0) {
                $avgUnitPrice = round($totalSpent / $totalQuantity, 2);
            } elseif (!empty($data['unit_prices'])) {
                $avgUnitPrice = round(array_sum($data['unit_prices']) / count($data['unit_prices']), 2);
            }

            $firstDate = $uniqueDates[0];
            $lastDate = end($uniqueDates);
            $lastCarbon = Carbon::parse($lastDate);
            $daysSinceLast = max(0, $today->diffInDays($lastCarbon, false) * -1);

            // Hitung interval hari antar pembelian berurutan (untuk analisis pola KV-047)
            $intervals = [];
            for ($i = 1; $i < count($uniqueDates); $i++) {
                $d1 = Carbon::parse($uniqueDates[$i - 1]);
                $d2 = Carbon::parse($uniqueDates[$i]);
                $diff = $d1->diffInDays($d2);
                if ($diff > 0) {
                    $intervals[] = $diff;
                }
            }

            $avgIntervalDays = null;
            if (!empty($intervals)) {
                $avgIntervalDays = round(array_sum($intervals) / count($intervals), 1);
            }

            $aggregated[] = [
                'item_name' => $data['display_name'],
                'canonical_name' => $key,
                'purchase_count' => $purchaseCount,
                'unique_purchase_days' => $uniqueDaysCount,
                'total_quantity' => $totalQuantity,
                'unit' => $dominantUnit,
                'total_spent' => $totalSpent,
                'formatted_total_spent' => 'Rp ' . number_format($totalSpent, 0, ',', '.'),
                'average_unit_price' => $avgUnitPrice,
                'formatted_average_price' => 'Rp ' . number_format($avgUnitPrice, 0, ',', '.'),
                'first_purchase_date' => $firstDate,
                'last_purchase_date' => $lastDate,
                'days_since_last_purchase' => (int) $daysSinceLast,
                'purchase_dates' => $uniqueDates,
                'purchase_intervals_days' => $intervals,
                'average_interval_days' => $avgIntervalDays,
            ];
        }

        // Urutkan berdasarkan frekuensi pembelian terbanyak, kemudian total belanja terbesar
        usort($aggregated, function ($a, $b) {
            if ($b['purchase_count'] !== $a['purchase_count']) {
                return $b['purchase_count'] <=> $a['purchase_count'];
            }
            return $b['total_spent'] <=> $a['total_spent'];
        });

        return $aggregated;
    }

    /**
     * Menghitung rentang hari histori usaha (business history span).
     * Input langsung untuk evaluasi aturan kecukupan 7 hari (KV-046).
     */
    public function getBusinessHistorySpan(Business $business): array
    {
        $earliestTx = Transaction::where('business_id', $business->id)
            ->where('type', 'cash_out')
            ->where('is_cash_settled', true)
            ->orderBy('transaction_date', 'asc')
            ->first();

        $latestTx = Transaction::where('business_id', $business->id)
            ->where('type', 'cash_out')
            ->where('is_cash_settled', true)
            ->orderBy('transaction_date', 'desc')
            ->first();

        if (!$earliestTx || !$latestTx) {
            return [
                'has_history' => false,
                'earliest_date' => null,
                'latest_date' => null,
                'span_days' => 0,
                'unique_transaction_days' => 0,
                'is_sufficient_for_prediction' => false,
            ];
        }

        $earliestDate = Carbon::parse($earliestTx->transaction_date)->startOfDay();
        $latestDate = Carbon::parse($latestTx->transaction_date)->startOfDay();

        $today = Carbon::today();
        // Rentang hari dari transaksi pertama hingga hari ini
        $spanDays = max(1, $earliestDate->diffInDays($today) + 1);

        $uniqueDays = Transaction::where('business_id', $business->id)
            ->where('type', 'cash_out')
            ->where('is_cash_settled', true)
            ->distinct('transaction_date')
            ->count('transaction_date');

        return [
            'has_history' => true,
            'earliest_date' => $earliestDate->toDateString(),
            'latest_date' => $latestDate->toDateString(),
            'span_days' => (int) $spanDays,
            'unique_transaction_days' => (int) $uniqueDays,
            'is_sufficient_for_prediction' => $spanDays >= self::THRESHOLD_DAYS,
        ];
    }

    /**
     * Mengevaluasi aturan kecukupan data 7 hari tingkat bisnis (DoD KV-046).
     * Ambang batas 7 hari, tanggal update, dan disclaimer tidak mengklaim stok fisik.
     */
    public function evaluateDataSufficiency(Business $business): array
    {
        $span = $this->getBusinessHistorySpan($business);
        $threshold = self::THRESHOLD_DAYS;
        $now = Carbon::now();

        if (!$span['has_history']) {
            return [
                'is_sufficient' => false,
                'status' => 'insufficient_data',
                'status_label' => 'Data Belum Cukup',
                'threshold_days' => $threshold,
                'current_span_days' => 0,
                'remaining_days' => $threshold,
                'last_updated_at' => $now->toIso8601String(),
                'formatted_last_updated' => $now->translatedFormat('d F Y, H:i') . ' WIB',
                'last_purchase_date' => null,
                'guidance_message' => 'Belum ada riwayat pembelian bahan baku yang tercatat. Mulai catat belanja bahan Anda untuk mengaktifkan saran restock otomatis (minimal ' . $threshold . ' hari).',
                'disclaimer' => self::DISCLAIMER,
            ];
        }

        $currentSpan = (int) $span['span_days'];
        $isSufficient = $currentSpan >= $threshold;
        $remainingDays = max(0, $threshold - $currentSpan);

        $guidance = $isSufficient
            ? 'Riwayat belanja bahan baku sudah mencakup ' . $currentSpan . ' hari. Data memadai untuk memprediksi pola belanja berkala.'
            : 'Pencatatan belanja bahan baru berjalan ' . $currentSpan . ' hari. KasiVo membutuhkan minimal ' . $threshold . ' hari riwayat belanja (sisa ' . $remainingDays . ' hari lagi) untuk membaca pola restock usaha Anda.';

        return [
            'is_sufficient' => $isSufficient,
            'status' => $isSufficient ? 'sufficient' : 'insufficient_data',
            'status_label' => $isSufficient ? 'Data Cukup untuk Analisis' : 'Data Belum Cukup',
            'threshold_days' => $threshold,
            'current_span_days' => $currentSpan,
            'remaining_days' => $remainingDays,
            'last_updated_at' => $now->toIso8601String(),
            'formatted_last_updated' => $now->translatedFormat('d F Y, H:i') . ' WIB',
            'last_purchase_date' => $span['latest_date'],
            'guidance_message' => $guidance,
            'disclaimer' => self::DISCLAIMER,
        ];
    }

    /**
     * Mengevaluasi kecukupan data tingkat item bahan baku.
     * Item memerlukan minimal 2 kali pembelian pada tanggal berbeda untuk membaca interval pola.
     */
    public function evaluateItemSufficiency(array $itemData, array $businessSufficiency): array
    {
        if (!$businessSufficiency['is_sufficient']) {
            return [
                'is_sufficient' => false,
                'status' => 'insufficient_data',
                'status_label' => 'Data Belum Cukup',
                'reason' => 'Riwayat usaha belum mencapai ambang ' . self::THRESHOLD_DAYS . ' hari.',
            ];
        }

        $uniqueDays = $itemData['unique_purchase_days'] ?? count($itemData['purchase_dates'] ?? []);

        if ($uniqueDays < 2) {
            return [
                'is_sufficient' => false,
                'status' => 'insufficient_data',
                'status_label' => 'Perlu Riwayat Tambahan',
                'reason' => 'Item baru tercatat 1 kali pembelian. Membutuhkan minimal 2 kali pembelian pada tanggal berbeda untuk menghitung interval belanja rutin.',
            ];
        }

        return [
            'is_sufficient' => true,
            'status' => 'sufficient',
            'status_label' => 'Siap Dianalisis',
            'reason' => 'Data item memadai dengan ' . $uniqueDays . ' tanggal pembelian berbeda.',
        ];
    }

    /**
     * Mengambil ikhtisar lengkap saran restock (gabungan status kecukupan bisnis & item).
     */
    public function getRestockOverview(Business $business): array
    {
        $sufficiency = $this->evaluateDataSufficiency($business);
        $items = $this->getItemPurchaseHistory($business);

        $analyzedItems = [];
        $insufficientItems = [];

        foreach ($items as $item) {
            $itemSufficiency = $this->evaluateItemSufficiency($item, $sufficiency);
            $enriched = array_merge($item, [
                'sufficiency' => $itemSufficiency,
            ]);

            if ($itemSufficiency['is_sufficient']) {
                $analyzedItems[] = $enriched;
            } else {
                $insufficientItems[] = $enriched;
            }
        }

        return [
            'sufficiency' => $sufficiency,
            'items' => $items,
            'ready_items' => $analyzedItems,
            'pending_items' => $insufficientItems,
            'total_items_count' => count($items),
            'ready_items_count' => count($analyzedItems),
            'disclaimer' => self::DISCLAIMER,
        ];
    }

    /**
     * Normalisasi penulisan nama item (menghilangkan spasi ganda, Title Case).
     */
    public function normalizeItemName(string $name): string
    {
        $clean = preg_replace('/\s+/', ' ', trim($name));
        return mb_convert_case($clean, MB_CASE_TITLE, 'UTF-8');
    }
}
