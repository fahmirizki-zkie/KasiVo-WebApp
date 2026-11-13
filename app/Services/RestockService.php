<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Services\Ai\GroqService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class RestockService
{
    protected ?GroqService $groqService;

    public function __construct(?GroqService $groqService = null)
    {
        $this->groqService = $groqService;
    }

    public function getGroqService(): GroqService
    {
        return $this->groqService ??= app(GroqService::class);
    }
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

            $avgQuantity = ($purchaseCount > 0) ? round($totalQuantity / $purchaseCount, 2) : 0.0;

            $aggregated[] = [
                'item_name' => $data['display_name'],
                'canonical_name' => $key,
                'purchase_count' => $purchaseCount,
                'total_purchases' => $purchaseCount,
                'unique_purchase_days' => $uniqueDaysCount,
                'total_quantity' => $totalQuantity,
                'unit' => $dominantUnit,
                'dominant_unit' => $dominantUnit,
                'average_quantity_per_purchase' => $avgQuantity,
                'avg_quantity_per_purchase' => $avgQuantity,
                'total_spent' => $totalSpent,
                'formatted_total_spent' => 'Rp ' . number_format($totalSpent, 0, ',', '.'),
                'average_unit_price' => $avgUnitPrice,
                'avg_unit_price' => $avgUnitPrice,
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
     * Menghitung selang hari antar-pembelian (purchase intervals) dari daftar tanggal yang berurutan.
     * Sesuai DoD KV-047: Menghitung delta hari, rata-rata interval, min, dan max.
     */
    public function calculateItemIntervals(array $purchaseDates): array
    {
        $dates = array_values(array_unique($purchaseDates));
        sort($dates);

        $count = count($dates);
        if ($count < 2) {
            return [
                'has_intervals' => false,
                'intervals' => [],
                'average_interval_days' => 0,
                'min_interval_days' => 0,
                'max_interval_days' => 0,
            ];
        }

        $intervals = [];
        for ($i = 1; $i < $count; $i++) {
            $prev = Carbon::parse($dates[$i - 1])->startOfDay();
            $curr = Carbon::parse($dates[$i])->startOfDay();
            $diff = (int) $prev->diffInDays($curr);
            $intervals[] = max(1, $diff);
        }

        $avg = (int) round(array_sum($intervals) / count($intervals));
        $avg = max(1, $avg);

        return [
            'has_intervals' => true,
            'intervals' => $intervals,
            'average_interval_days' => $avg,
            'min_interval_days' => min($intervals),
            'max_interval_days' => max($intervals),
        ];
    }

    /**
     * Menghasilkan saran restock berbasis pola siklus belanja untuk 1 item bahan baku.
     * Mengkategorikan urgensi: 'overdue', 'due_today', 'upcoming', 'safe'.
     * DoD KV-047: Saran didasarkan pola pembelian, bukan jumlah stok pasti.
     */
    public function generateItemRestockSuggestion(array $itemData, ?Carbon $referenceDate = null): array
    {
        $itemName = $itemData['item_name'] ?? 'Bahan Baku';
        $dates = $itemData['purchase_dates'] ?? [];
        $uniqueDays = $itemData['unique_purchase_days'] ?? count($dates);

        if ($uniqueDays < 2 || count($dates) < 2) {
            return [
                'item_name' => $itemName,
                'is_recommended' => false,
                'urgency' => 'insufficient_history',
                'urgency_label' => 'Riwayat Belum Cukup',
                'average_interval_days' => null,
                'days_since_last_purchase' => null,
                'days_until_restock' => null,
                'last_purchase_date' => $itemData['last_purchase_date'] ?? null,
                'estimated_next_purchase_date' => null,
                'suggested_quantity' => (float) ($itemData['avg_quantity_per_purchase'] ?? 0),
                'unit' => $itemData['dominant_unit'] ?? 'pcs',
                'avg_unit_price' => (float) ($itemData['avg_unit_price'] ?? 0),
                'estimated_cost' => (float) (($itemData['avg_quantity_per_purchase'] ?? 0) * ($itemData['avg_unit_price'] ?? 0)),
                'pattern_reason' => sprintf('Item %s belum memiliki riwayat tanggal pembelian yang cukup untuk membaca pola siklus belanja berkala.', $itemName),
            ];
        }

        $intervalInfo = $this->calculateItemIntervals($dates);
        $avgInterval = $intervalInfo['average_interval_days'];

        $lastPurchaseDate = Carbon::parse($itemData['last_purchase_date'])->startOfDay();
        $today = ($referenceDate ? $referenceDate->copy() : Carbon::today())->startOfDay();

        $estimatedNextDate = $lastPurchaseDate->copy()->addDays($avgInterval);
        $daysSinceLast = (int) $lastPurchaseDate->diffInDays($today, false);
        $daysUntilRestock = $avgInterval - $daysSinceLast;

        $suggestedQuantity = (float) round(
            $itemData['avg_quantity_per_purchase']
            ?? $itemData['average_quantity_per_purchase']
            ?? (($itemData['purchase_count'] ?? 0) > 0 ? $itemData['total_quantity'] / $itemData['purchase_count'] : 1),
            2
        );
        $avgUnitPrice = (float) ($itemData['average_unit_price'] ?? $itemData['avg_unit_price'] ?? 0);
        $estimatedCost = (float) round($suggestedQuantity * $avgUnitPrice, 2);
        $unit = $itemData['unit'] ?? $itemData['dominant_unit'] ?? 'pcs';
        $qtyDisplay = ($suggestedQuantity == (int) $suggestedQuantity) ? (string) (int) $suggestedQuantity : (string) $suggestedQuantity;

        if ($daysUntilRestock < 0) {
            $overdueDays = abs($daysUntilRestock);
            $urgency = 'overdue';
            $urgencyLabel = 'Perlu Segera (Lewat Siklus)';
            $isRecommended = true;
            $patternReason = sprintf(
                '%s biasanya dibeli setiap %d hari sekali, dan sudah %d hari belum dibeli (terakhir dibeli %s sebanyak %s %s). Pola menunjukkan waktu belanja rutin telah terlewat %d hari.',
                $itemName,
                $avgInterval,
                $daysSinceLast,
                $lastPurchaseDate->translatedFormat('d M Y'),
                $qtyDisplay,
                $unit,
                $overdueDays
            );
        } elseif ($daysUntilRestock === 0) {
            $urgency = 'due_today';
            $urgencyLabel = 'Perlu Restock Hari Ini';
            $isRecommended = true;
            $patternReason = sprintf(
                '%s rata-rata dibeli setiap %d hari sekali (terakhir dibeli %d hari lalu sebanyak %s %s). Berdasarkan siklus ini, estimasi waktu belanja rutin berikutnya adalah hari ini.',
                $itemName,
                $avgInterval,
                $daysSinceLast,
                $qtyDisplay,
                $unit
            );
        } elseif ($daysUntilRestock <= 2) {
            $urgency = 'upcoming';
            $urgencyLabel = sprintf('Perlu Restock %d Hari Lagi', $daysUntilRestock);
            $isRecommended = true;
            $patternReason = sprintf(
                '%s rata-rata dibeli setiap %d hari sekali (terakhir dibeli %d hari lalu). Berdasarkan siklus ini, waktu belanja rutin diperkirakan tiba dalam %d hari lagi.',
                $itemName,
                $avgInterval,
                $daysSinceLast,
                $daysUntilRestock
            );
        } else {
            $urgency = 'safe';
            $urgencyLabel = 'Stok Pola Masih Aman';
            $isRecommended = false;
            $patternReason = sprintf(
                '%s rata-rata dibeli setiap %d hari sekali (terakhir dibeli %d hari lalu). Berdasarkan kebiasaan belanja berkala, jadwal pembelian berikutnya masih berjarak %d hari.',
                $itemName,
                $avgInterval,
                $daysSinceLast,
                $daysUntilRestock
            );
        }

        return [
            'item_name' => $itemName,
            'is_recommended' => $isRecommended,
            'urgency' => $urgency,
            'urgency_label' => $urgencyLabel,
            'average_interval_days' => $avgInterval,
            'days_since_last_purchase' => $daysSinceLast,
            'days_until_restock' => $daysUntilRestock,
            'last_purchase_date' => $lastPurchaseDate->toDateString(),
            'estimated_next_purchase_date' => $estimatedNextDate->toDateString(),
            'suggested_quantity' => $suggestedQuantity,
            'unit' => $unit,
            'avg_unit_price' => $avgUnitPrice,
            'estimated_cost' => $estimatedCost,
            'pattern_reason' => $patternReason,
            'intervals_data' => $intervalInfo,
        ];
    }

    /**
     * Menghasilkan daftar rekomendasi restock lengkap untuk suatu bisnis (DoD KV-047).
     * Terurut berdasarkan prioritas urgensi: overdue -> due_today -> upcoming -> safe.
     */
    public function generateRestockRecommendations(Business $business, ?Carbon $referenceDate = null): array
    {
        $sufficiency = $this->evaluateDataSufficiency($business);

        if (!$sufficiency['is_sufficient']) {
            return [
                'is_sufficient' => false,
                'sufficiency' => $sufficiency,
                'recommendations' => [],
                'recommended_items' => [],
                'safe_items' => [],
                'recommended_count' => 0,
                'safe_count' => 0,
                'total_analyzed_count' => 0,
                'total_estimated_budget' => 0.0,
                'disclaimer' => self::DISCLAIMER,
            ];
        }

        $items = $this->getItemPurchaseHistory($business);
        $allRecommendations = [];

        foreach ($items as $item) {
            if (($item['unique_purchase_days'] ?? 0) >= 2) {
                $suggestion = $this->generateItemRestockSuggestion($item, $referenceDate);
                $allRecommendations[] = array_merge($item, $suggestion);
            }
        }

        // Urutkan berdasarkan prioritas urgensi: overdue (1), due_today (2), upcoming (3), safe (4)
        $priorityMap = [
            'overdue' => 1,
            'due_today' => 2,
            'upcoming' => 3,
            'safe' => 4,
        ];

        usort($allRecommendations, function ($a, $b) use ($priorityMap) {
            $pA = $priorityMap[$a['urgency']] ?? 99;
            $pB = $priorityMap[$b['urgency']] ?? 99;

            if ($pA !== $pB) {
                return $pA <=> $pB;
            }

            return ($a['days_until_restock'] ?? 0) <=> ($b['days_until_restock'] ?? 0);
        });

        $recommended = array_values(array_filter($allRecommendations, fn($r) => $r['is_recommended']));
        $safe = array_values(array_filter($allRecommendations, fn($r) => !$r['is_recommended']));
        $totalEstimatedBudget = (float) array_sum(array_column($recommended, 'estimated_cost'));

        return [
            'is_sufficient' => true,
            'sufficiency' => $sufficiency,
            'recommendations' => $allRecommendations,
            'recommended_items' => $recommended,
            'safe_items' => $safe,
            'recommended_count' => count($recommended),
            'safe_count' => count($safe),
            'total_analyzed_count' => count($allRecommendations),
            'total_estimated_budget' => $totalEstimatedBudget,
            'disclaimer' => self::DISCLAIMER,
        ];
    }

    /**
     * Mengambil ikhtisar lengkap saran restock (gabungan status kecukupan bisnis & item).
     */
    public function getRestockOverview(Business $business, ?Carbon $referenceDate = null): array
    {
        $recommendationData = $this->generateRestockRecommendations($business, $referenceDate);
        $sufficiency = $recommendationData['sufficiency'];
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
            'recommendations' => $recommendationData['recommendations'],
            'recommended_items' => $recommendationData['recommended_items'],
            'safe_items' => $recommendationData['safe_items'],
            'recommended_count' => $recommendationData['recommended_count'],
            'safe_count' => $recommendationData['safe_count'],
            'total_estimated_budget' => $recommendationData['total_estimated_budget'],
            'total_items_count' => count($items),
            'ready_items_count' => count($analyzedItems),
            'ai_explanation' => $this->explainRestockWithAi($business, $referenceDate),
            'disclaimer' => self::DISCLAIMER,
        ];
    }

    /**
     * Menjelaskan rekomendasi restock dalam kalimat sederhana dan ramah UMKM menggunakan Groq LLM (DoD KV-048).
     * Dilengkapi fallback deterministik anti-crash jika AI tidak dapat diakses atau kuota habis.
     */
    public function explainRestockWithAi(
        Business $business,
        ?Carbon $referenceDate = null,
        ?GroqService $customGroq = null
    ): array {
        $recommendationData = $this->generateRestockRecommendations($business, $referenceDate);
        $sufficiency = $recommendationData['sufficiency'] ?? [];

        // 1. Kasus histori data belum cukup (< 7 hari)
        if (!$recommendationData['is_sufficient']) {
            $currentSpan = (int) ($sufficiency['current_span_days'] ?? 0);
            $threshold = (int) ($sufficiency['threshold_days'] ?? self::THRESHOLD_DAYS);
            $remaining = (int) ($sufficiency['remaining_days'] ?? $threshold);

            return [
                'is_sufficient' => false,
                'is_ai_generated' => false,
                'source' => 'rule_engine',
                'summary_headline' => 'Pencatatan Belanja Belum Mencapai 7 Hari',
                'ai_explanation' => sprintf(
                    'Sistem KasiVo baru mencatat riwayat belanja bahan selama %d hari. Diperlukan minimal %d hari riwayat belanja (sisa %d hari lagi) agar pola siklus belanja rutin usaha %s dapat dianalisis.',
                    $currentSpan,
                    $threshold,
                    $remaining,
                    $business->name ?? 'Anda'
                ),
                'actionable_advice' => 'Terus catat setiap pembelian bahan baku harian Anda melalui nota foto, suara, atau teks agar rekomendasi otomatis segera aktif.',
                'data_limitations' => 'Analisis membutuhkan minimal 7 hari kalender transaksi belanja bahan baku untuk memastikan pola interval berulang yang valid.',
                'disclaimer' => self::DISCLAIMER,
                'recommendations_data' => $recommendationData,
            ];
        }

        $recommended = $recommendationData['recommended_items'] ?? [];
        $safe = $recommendationData['safe_items'] ?? [];
        $spanDays = (int) ($sufficiency['current_span_days'] ?? 0);

        // 2. Kasus seluruh bahan saat ini dalam kondisi aman (belum ada yang perlu restock)
        if (empty($recommended)) {
            return [
                'is_sufficient' => true,
                'is_ai_generated' => false,
                'source' => 'rule_engine',
                'summary_headline' => 'Semua Bahan Baku Masih Dalam Siklus Aman',
                'ai_explanation' => sprintf(
                    'Berdasarkan analisis pola belanja berkala selama %d hari terakhir, seluruh bahan baku (%d jenis) saat ini masih dalam jadwal aman dan belum memasuki siklus belanja rutin berikutnya.',
                    $spanDays,
                    count($safe)
                ),
                'actionable_advice' => 'Tidak ada belanja bahan mendesak hari ini. Pantau kembali jadwal belanja berkala Anda besok atau lusa.',
                'data_limitations' => sprintf('Prediksi didasarkan pada siklus interval belanja %d hari terakhir, BUKAN melacak jumlah fisik sisa stok aktual di tempat usaha.', $spanDays),
                'disclaimer' => self::DISCLAIMER,
                'recommendations_data' => $recommendationData,
            ];
        }

        // 3. Kasus terdapat bahan yang disarankan restock -> panggil Groq LLM
        $fallback = $this->buildFallbackRestockExplanation($recommendationData);

        try {
            $groq = $customGroq ?? $this->getGroqService();

            $systemPrompt = <<<PROMPT
Anda adalah KasiVo AI Assistant, asisten cerdas operasional dan keuangan untuk UMKM F&B (kuliner/warung/kafe) di Indonesia.
Tugas Anda: Mengubah ringkasan perhitungan pola pembelian bahan baku menjadi narasi penjelasan yang ramah, praktis, to-the-point, dan mudah dipahami pemilik warung/kafe.

PANDUAN & ATURAN WAJIB (DoD KasiVo):
1. Alasan saran harus jelas: jelaskan bahan prioritas yang perlu dibeli, urgensinya (terlewat siklus atau tepat tiba hari ini), siklus biasanya (misal tiap 4 hari), dan estimasi anggaran yang perlu disiapkan.
2. Keterbatasan data harus jelas: TEGASKAN bahwa rekomendasi ini berbasis pola kebiasaan siklus belanja berkala, BUKAN melacak atau mengklaim jumlah fisik sisa stok aktual di gudang/dapur.
3. Gunakan bahasa Indonesia sehari-hari yang santun, profesional, dan menyemangati pemilik usaha.
4. Keluarkan respon HANYA dalam format JSON valid tanpa markdown tambahan:
{
    "summary_headline": "Judul ringkas & informatif (maksimal 1 kalimat)",
    "ai_explanation": "Penjelasan naratif 2-3 kalimat yang mengalir, merangkum bahan prioritas dan alasan siklusnya",
    "actionable_advice": "1 tips praktis belanja atau persiapan operasional untuk pemilik usaha",
    "data_limitations": "Pernyataan keterbatasan data yang jelas dan transparan"
}
PROMPT;

            $payloadItems = array_map(function ($item) {
                return [
                    'item_name' => $item['item_name'],
                    'urgency' => $item['urgency'],
                    'urgency_label' => $item['urgency_label'],
                    'average_interval_days' => $item['average_interval_days'],
                    'days_since_last_purchase' => $item['days_since_last_purchase'],
                    'days_until_restock' => $item['days_until_restock'],
                    'suggested_quantity' => $item['suggested_quantity'],
                    'unit' => $item['unit'],
                    'estimated_cost' => $item['estimated_cost'],
                    'pattern_reason' => $item['pattern_reason'],
                ];
            }, $recommended);

            $userContent = json_encode([
                'business_name' => $business->name ?? 'Usaha Anda',
                'history_span_days' => $spanDays,
                'total_estimated_budget' => $recommendationData['total_estimated_budget'],
                'recommended_items' => $payloadItems,
                'safe_items_count' => count($safe),
            ], JSON_UNESCAPED_UNICODE);

            $messages = [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userContent],
            ];

            $response = $groq->chat($messages, jsonMode: true);

            if (!empty($response['ai_explanation'])) {
                return [
                    'is_sufficient' => true,
                    'is_ai_generated' => true,
                    'source' => 'groq_llm',
                    'summary_headline' => $response['summary_headline'] ?? $fallback['summary_headline'],
                    'ai_explanation' => $response['ai_explanation'],
                    'actionable_advice' => $response['actionable_advice'] ?? $fallback['actionable_advice'],
                    'data_limitations' => $response['data_limitations'] ?? $fallback['data_limitations'],
                    'disclaimer' => self::DISCLAIMER,
                    'recommendations_data' => $recommendationData,
                ];
            }

            return $fallback;
        } catch (\Throwable $e) {
            Log::warning('Groq Restock Explanation Fallback Triggered', [
                'business_id' => $business->id,
                'error' => $e->getMessage(),
            ]);

            return $fallback;
        }
    }

    /**
     * Membangun narasi penjelasan restock deterministik sebagai fallback anti-crash (DoD KV-048).
     */
    public function buildFallbackRestockExplanation(array $recommendationData): array
    {
        $sufficiency = $recommendationData['sufficiency'] ?? [];
        $recommended = $recommendationData['recommended_items'] ?? [];
        $spanDays = (int) ($sufficiency['current_span_days'] ?? 0);
        $totalBudget = (float) ($recommendationData['total_estimated_budget'] ?? 0);

        $overdueCount = count(array_filter($recommended, fn($i) => ($i['urgency'] ?? '') === 'overdue'));
        $dueTodayCount = count(array_filter($recommended, fn($i) => ($i['urgency'] ?? '') === 'due_today'));
        $upcomingCount = count(array_filter($recommended, fn($i) => ($i['urgency'] ?? '') === 'upcoming'));

        $itemDescriptions = array_map(function ($i) {
            $qty = (float) ($i['suggested_quantity'] ?? 0);
            $qtyStr = ($qty == (int) $qty) ? (string) (int) $qty : (string) $qty;
            return $i['item_name'] . ' (' . $qtyStr . ' ' . ($i['unit'] ?? '') . ')';
        }, $recommended);

        $itemsStr = implode(', ', $itemDescriptions);
        $headline = sprintf('Ada %d Bahan Baku yang Disarankan untuk Restock', count($recommended));

        $urgencyClauses = [];
        if ($overdueCount > 0) {
            $urgencyClauses[] = "{$overdueCount} bahan sudah melewati siklus belanja biasanya";
        }
        if ($dueTodayCount > 0) {
            $urgencyClauses[] = "{$dueTodayCount} bahan tepat tiba jadwal belanja rutinnya hari ini";
        }
        if ($upcomingCount > 0) {
            $urgencyClauses[] = "{$upcomingCount} bahan jadwal rutinnya tiba dalam 1-2 hari ke depan";
        }

        $explanation = sprintf(
            'Berdasarkan riwayat belanja %d hari terakhir, %s: %s. Estimasi total anggaran belanja yang perlu disiapkan sekitar Rp %s.',
            $spanDays,
            implode(' dan ', $urgencyClauses),
            $itemsStr,
            number_format($totalBudget, 0, ',', '.')
        );

        $advice = 'Disarankan untuk memeriksa tempat penyimpanan bahan dan menyiapkan dana operasional belanja agar produksi usaha berjalan lancar.';

        $limitations = sprintf(
            'Rekomendasi ini dihitung dari analisis interval riwayat belanja %d hari terakhir dan kebiasaan belanja berkala, BUKAN merupakan pelacakan sisa stok fisik riil di tempat usaha Anda.',
            $spanDays
        );

        return [
            'is_sufficient' => true,
            'is_ai_generated' => false,
            'source' => 'rule_engine_fallback',
            'summary_headline' => $headline,
            'ai_explanation' => $explanation,
            'actionable_advice' => $advice,
            'data_limitations' => $limitations,
            'disclaimer' => self::DISCLAIMER,
            'recommendations_data' => $recommendationData,
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
