<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CashFlowService
{
    public const CATEGORY_LABELS = [
        'penjualan' => 'Penjualan Umum',
        'penjualan_tunai' => 'Penjualan Tunai',
        'penjualan_qris' => 'Penjualan QRIS / Transfer',
        'bahan_baku' => 'Bahan Baku & Dapur',
        'operasional' => 'Biaya Operasional',
        'gaji' => 'Gaji / Upah Karyawan',
        'sewa' => 'Sewa Tempat',
        'lainnya' => 'Lain-lain',
    ];

    /**
     * Hitung ringkasan arus kas (Kas Masuk, Kas Keluar, Arus Kas Bersih).
     * Sesuai DoD KV-038: Rumus dihitung program, pembelian stok masuk kas keluar sekali (no double counting).
     */
    public function getSummary(Business $business, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $aggregation = Transaction::where('business_id', $business->id)
            ->where('is_cash_settled', true)
            ->when($startDate, fn($q) => $q->where('transaction_date', '>=', $startDate->toDateString()))
            ->when($endDate, fn($q) => $q->where('transaction_date', '<=', $endDate->toDateString()))
            ->selectRaw("
                COALESCE(SUM(CASE WHEN type = 'cash_in' THEN amount ELSE 0 END), 0) as total_cash_in,
                COALESCE(SUM(CASE WHEN type = 'cash_out' THEN amount ELSE 0 END), 0) as total_cash_out,
                COUNT(CASE WHEN type = 'cash_in' THEN 1 END) as cash_in_count,
                COUNT(CASE WHEN type = 'cash_out' THEN 1 END) as cash_out_count,
                COUNT(*) as total_transactions
            ")
            ->first();

        $totalCashIn = (float) ($aggregation->total_cash_in ?? 0);
        $totalCashOut = (float) ($aggregation->total_cash_out ?? 0);
        $netCashFlow = (float) ($totalCashIn - $totalCashOut);

        $cashInCount = (int) ($aggregation->cash_in_count ?? 0);
        $cashOutCount = (int) ($aggregation->cash_out_count ?? 0);
        $totalTransactions = (int) ($aggregation->total_transactions ?? 0);

        return [
            'total_cash_in' => $totalCashIn,
            'total_cash_out' => $totalCashOut,
            'net_cash_flow' => $netCashFlow,
            'cash_in_count' => $cashInCount,
            'cash_out_count' => $cashOutCount,
            'total_transactions' => $totalTransactions,
            'is_negative' => $netCashFlow < 0,
            'formatted_cash_in' => $this->formatRupiah($totalCashIn),
            'formatted_cash_out' => $this->formatRupiah($totalCashOut),
            'formatted_net' => $this->formatRupiah($netCashFlow, false),
            'start_date' => $startDate?->toDateString(),
            'end_date' => $endDate?->toDateString(),
        ];
    }

    /**
     * Hitung rincian kategori kas masuk & keluar beserta persentase kontribusi.
     */
    public function getCategoryBreakdown(Business $business, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $records = Transaction::where('business_id', $business->id)
            ->where('is_cash_settled', true)
            ->when($startDate, fn($q) => $q->where('transaction_date', '>=', $startDate->toDateString()))
            ->when($endDate, fn($q) => $q->where('transaction_date', '<=', $endDate->toDateString()))
            ->selectRaw("
                type,
                category,
                COALESCE(SUM(amount), 0) as total,
                COUNT(*) as count
            ")
            ->groupBy('type', 'category')
            ->orderByDesc('total')
            ->get();

        $cashInItems = [];
        $cashOutItems = [];
        $totalCashIn = 0.0;
        $totalCashOut = 0.0;

        foreach ($records as $row) {
            $amount = (float) $row->total;
            if ($row->type === 'cash_in') {
                $totalCashIn += $amount;
            } else {
                $totalCashOut += $amount;
            }
        }

        foreach ($records as $row) {
            $amount = (float) $row->total;
            $isCashIn = $row->type === 'cash_in';
            $baseTotal = $isCashIn ? $totalCashIn : $totalCashOut;
            $percentage = $baseTotal > 0 ? round(($amount / $baseTotal) * 100, 1) : 0.0;

            $item = [
                'category' => $row->category,
                'label' => $this->getCategoryLabel($row->category),
                'total' => $amount,
                'count' => (int) $row->count,
                'percentage' => $percentage,
                'formatted_total' => $this->formatRupiah($amount),
            ];

            if ($isCashIn) {
                $cashInItems[] = $item;
            } else {
                $cashOutItems[] = $item;
            }
        }

        return [
            'cash_in' => $cashInItems,
            'cash_out' => $cashOutItems,
            'total_cash_in' => $totalCashIn,
            'total_cash_out' => $totalCashOut,
        ];
    }

    /**
     * Hasilkan runtun waktu harian (daily time-series) dengan zero-fill untuk visualisasi grafik.
     * Mengikuti DoD KV-042: menyajikan data kas masuk dan kas keluar tanpa label laba.
     */
    public function getDailySeries(Business $business, Carbon $startDate, Carbon $endDate): array
    {
        $start = $startDate->copy()->startOfDay();
        $end = $endDate->copy()->startOfDay();

        if ($start->gt($end)) {
            [$start, $end] = [$end, $start];
        }

        $records = Transaction::where('business_id', $business->id)
            ->where('is_cash_settled', true)
            ->where('transaction_date', '>=', $start->toDateString())
            ->where('transaction_date', '<=', $end->toDateString())
            ->selectRaw("
                transaction_date,
                type,
                COALESCE(SUM(amount), 0) as total
            ")
            ->groupBy('transaction_date', 'type')
            ->get();

        $matrix = [];
        foreach ($records as $record) {
            $dateStr = Carbon::parse($record->transaction_date)->toDateString();
            $matrix[$dateStr . '-' . $record->type] = (float) $record->total;
        }

        $series = [];
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            $dateKey = $cursor->toDateString();
            $cashIn = $matrix[$dateKey . '-cash_in'] ?? 0.0;
            $cashOut = $matrix[$dateKey . '-cash_out'] ?? 0.0;
            $net = $cashIn - $cashOut;

            $series[] = [
                'date' => $dateKey,
                'label' => $cursor->translatedFormat('d M'),
                'day_name' => $cursor->translatedFormat('D'),
                'cash_in' => (float) $cashIn,
                'cash_out' => (float) $cashOut,
                'net' => (float) $net,
                'formatted_cash_in' => $this->formatRupiah($cashIn),
                'formatted_cash_out' => $this->formatRupiah($cashOut),
                'formatted_net' => $this->formatRupiah($net, false),
            ];

            $cursor->addDay();
        }

        return $series;
    }

    /**
     * Resolver periode tanggal dinamis sesuai standar SPS KV-041.
     * Default: 'this_month'
     */
    public function resolvePeriodDates(string $period = 'this_month', ?string $customStart = null, ?string $customEnd = null): array
    {
        $today = Carbon::today();

        switch ($period) {
            case 'today':
                $start = $today->copy()->startOfDay();
                $end = $today->copy()->endOfDay();
                $label = 'Hari Ini (' . $start->translatedFormat('d M Y') . ')';
                break;

            case '7_days':
            case 'last_7_days':
                $period = '7_days';
                $start = $today->copy()->subDays(6)->startOfDay();
                $end = $today->copy()->endOfDay();
                $label = '7 Hari Terakhir (' . $start->translatedFormat('d M') . ' - ' . $end->translatedFormat('d M Y') . ')';
                break;

            case '30_days':
            case 'last_30_days':
                $period = '30_days';
                $start = $today->copy()->subDays(29)->startOfDay();
                $end = $today->copy()->endOfDay();
                $label = '30 Hari Terakhir (' . $start->translatedFormat('d M') . ' - ' . $end->translatedFormat('d M Y') . ')';
                break;

            case 'custom':
                if (!empty($customStart) && !empty($customEnd)) {
                    try {
                        $start = Carbon::parse($customStart)->startOfDay();
                        $end = Carbon::parse($customEnd)->endOfDay();
                        if ($start->gt($end)) {
                            [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
                        }
                        $label = $start->translatedFormat('d M Y') . ' - ' . $end->translatedFormat('d M Y');
                        break;
                    } catch (\Throwable $e) {
                        // Fallback ke default 'this_month'
                    }
                }
                // Fall through jika custom tanggal tidak valid
                $period = 'this_month';
                $start = $today->copy()->startOfMonth()->startOfDay();
                $end = $today->copy()->endOfMonth()->endOfDay();
                $label = 'Bulan Ini (' . $start->translatedFormat('F Y') . ')';
                break;

            case 'all':
                return [
                    'start_date' => null,
                    'end_date' => null,
                    'period' => 'all',
                    'label' => 'Semua Waktu',
                ];

            case 'this_month':
            default:
                $period = 'this_month';
                $start = $today->copy()->startOfMonth()->startOfDay();
                $end = $today->copy()->endOfMonth()->endOfDay();
                $label = 'Bulan Ini (' . $start->translatedFormat('F Y') . ')';
                break;
        }

        return [
            'start_date' => $start,
            'end_date' => $end,
            'period' => $period,
            'label' => $label,
        ];
    }

    /**
     * Format nilai angka ke mata uang Rupiah standar.
     */
    public function formatRupiah(float $amount, bool $withSign = false): string
    {
        $isNegative = $amount < 0;
        $absFormatted = 'Rp ' . number_format(abs($amount), 0, ',', '.');

        if ($isNegative) {
            return '-Rp ' . number_format(abs($amount), 0, ',', '.');
        }

        if ($withSign && $amount > 0) {
            return '+Rp ' . number_format($amount, 0, ',', '.');
        }

        return $absFormatted;
    }

    /**
     * Dapatkan label yang ramah pengguna untuk kategori transaksi.
     */
    public function getCategoryLabel(string $category): string
    {
        return self::CATEGORY_LABELS[$category] ?? ucwords(str_replace('_', ' ', $category));
    }
}

