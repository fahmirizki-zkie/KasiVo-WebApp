<?php

namespace App\Livewire;

use App\Models\Business;
use App\Services\BusinessReportService;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
class BusinessProfile extends Component
{
    #[Url]
    public string $period = 'this_month';

    public function setPeriod(string $period): void
    {
        if (in_array($period, ['today', 'last_7_days', 'this_month', 'all_time'])) {
            $this->period = $period;
        }
    }

    protected function getActiveBusiness(): ?Business
    {
        $user = auth()->user();
        if (!$user) {
            return null;
        }

        return $user->businesses()->whereHas('transactions')->latest('id')->first()
            ?? $user->business
            ?? $user->businesses()->first();
    }

    public function render(BusinessReportService $reportService)
    {
        $business = $this->getActiveBusiness();

        if (!$business) {
            return view('livewire.business-profile', [
                'hasBusiness' => false,
                'business' => null,
                'report' => null,
            ]);
        }

        $report = $reportService->getCompleteProfileReportWithAi($business, $this->period);

        return view('livewire.business-profile', [
            'hasBusiness' => true,
            'business' => $business,
            'report' => $report,
        ]);
    }
}
