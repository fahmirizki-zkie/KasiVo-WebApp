<?php

namespace App\Livewire;

use App\Models\Business;
use App\Services\CashFlowService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    /**
     * Filter periode dashboard.
     * Default: 'this_month' (Sesuai mandat SPS KV-041)
     */
    public string $period = 'this_month';

    public ?string $customStartDate = null;
    public ?string $customEndDate = null;

    /**
     * Update filter periode secara dinamis.
     */
    public function setPeriod(string $period): void
    {
        if (in_array($period, ['this_month', 'today', '7_days', 'custom', 'all'], true)) {
            $this->period = $period;
        }
    }

    /**
     * Ambil bisnis aktif milik pengguna.
     */
    protected function getActiveBusiness(): ?Business
    {
        $user = auth()->user();
        if (!$user) {
            return null;
        }

        return $user->businesses()->first();
    }

    public function render(CashFlowService $cashFlowService)
    {
        $business = $this->getActiveBusiness();

        if (!$business) {
            return view('livewire.dashboard', [
                'hasBusiness' => false,
                'business' => null,
                'summary' => [
                    'total_cash_in' => 0.0,
                    'total_cash_out' => 0.0,
                    'net_cash_flow' => 0.0,
                    'cash_in_count' => 0,
                    'cash_out_count' => 0,
                    'total_transactions' => 0,
                    'is_negative' => false,
                    'formatted_cash_in' => 'Rp 0',
                    'formatted_cash_out' => 'Rp 0',
                    'formatted_net' => 'Rp 0',
                    'start_date' => null,
                    'end_date' => null,
                ],
                'dailySeries' => [],
                'breakdown' => [
                    'cash_in' => [],
                    'cash_out' => [],
                    'total_cash_in' => 0.0,
                    'total_cash_out' => 0.0,
                ],
                'periodLabel' => 'Bulan Ini',
                'period' => $this->period,
                'chartYMaxLabel' => 'Rp 1 Jt',
                'chartYHalfLabel' => 'Rp 500 Rb',
                'avgDailyCashIn' => 'Rp 0',
                'avgDailyCashOut' => 'Rp 0',
                'peakInLabel' => '-',
                'surplusDays' => 0,
                'deficitDays' => 0,
                'activeDays' => 0,
                'totalDays' => 0,
            ]);
        }

        $periodDates = $cashFlowService->resolvePeriodDates(
            $this->period,
            $this->customStartDate,
            $this->customEndDate
        );

        $summary = $cashFlowService->getSummary(
            $business,
            $periodDates['start_date'],
            $periodDates['end_date']
        );

        $startDate = $periodDates['start_date'] ?? now()->startOfMonth();
        $endDate = $periodDates['end_date'] ?? now()->endOfMonth();

        $dailySeries = $cashFlowService->getDailySeries($business, $startDate, $endDate);
        $breakdown = $cashFlowService->getCategoryBreakdown(
            $business,
            $periodDates['start_date'],
            $periodDates['end_date']
        );

        $totalDays = count($dailySeries);
        $activeDays = 0;
        $surplusDays = 0;
        $deficitDays = 0;
        $peakInVal = 0.0;
        $peakInLabel = '-';
        $maxVal = 0.0;

        foreach ($dailySeries as $t) {
            $maxVal = max($maxVal, $t['cash_in'], $t['cash_out']);
            if ($t['has_activity']) {
                $activeDays++;
            }
            if ($t['cash_in'] > $t['cash_out']) {
                $surplusDays++;
            } elseif ($t['cash_out'] > $t['cash_in']) {
                $deficitDays++;
            }
            if ($t['cash_in'] > $peakInVal) {
                $peakInVal = $t['cash_in'];
                $peakInLabel = $t['day_name'] . ', ' . $t['short_date'] . ' (' . $t['cash_in_compact'] . ')';
            }
        }

        $yMax = $maxVal > 0 ? ceil($maxVal / 500000) * 500000 : 1000000;
        if ($yMax < 500000) {
            $yMax = 500000;
        }

        $avgDailyCashIn = $totalDays > 0 ? round($summary['total_cash_in'] / $totalDays, 2) : 0.0;
        $avgDailyCashOut = $totalDays > 0 ? round($summary['total_cash_out'] / $totalDays, 2) : 0.0;

        return view('livewire.dashboard', [
            'hasBusiness' => true,
            'business' => $business,
            'summary' => $summary,
            'dailySeries' => $dailySeries,
            'breakdown' => $breakdown,
            'periodDates' => $periodDates,
            'periodLabel' => $periodDates['label'],
            'period' => $this->period,
            'chartYMaxLabel' => $cashFlowService->formatCompactRupiah($yMax),
            'chartYHalfLabel' => $cashFlowService->formatCompactRupiah($yMax / 2),
            'avgDailyCashIn' => $cashFlowService->formatCompactRupiah($avgDailyCashIn),
            'avgDailyCashOut' => $cashFlowService->formatCompactRupiah($avgDailyCashOut),
            'peakInLabel' => $peakInLabel,
            'surplusDays' => $surplusDays,
            'deficitDays' => $deficitDays,
            'activeDays' => $activeDays,
            'totalDays' => $totalDays,
        ]);
    }
}
