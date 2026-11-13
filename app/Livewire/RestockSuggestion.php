<?php

namespace App\Livewire;

use App\Models\Business;
use App\Services\RestockService;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class RestockSuggestion extends Component
{
    /**
     * Ambil bisnis aktif milik pengguna yang sedang login.
     */
    protected function getActiveBusiness(): ?Business
    {
        $user = auth()->user();
        if (!$user) {
            return null;
        }

        return $user->businesses()->first();
    }

    /**
     * Trigger refresh data analisis saran restock secara real-time.
     */
    public function refreshData(): void
    {
        // Re-renders the component with latest live calculations
    }

    public function render(RestockService $restockService)
    {
        $business = $this->getActiveBusiness();

        if (!$business) {
            return view('livewire.restock-suggestion', [
                'hasBusiness' => false,
                'business' => null,
                'sufficiency' => [
                    'is_sufficient' => false,
                    'status' => 'insufficient_data',
                    'status_label' => 'Data Belum Cukup',
                    'threshold_days' => 7,
                    'current_span_days' => 0,
                    'remaining_days' => 7,
                    'formatted_last_updated' => Carbon::now()->translatedFormat('d F Y, H:i') . ' WIB',
                    'guidance_message' => 'Belum ada profil usaha aktif yang terdaftar.',
                    'disclaimer' => RestockService::DISCLAIMER,
                ],
                'recommendations' => [],
                'recommendedItems' => [],
                'safeItems' => [],
                'recommendedCount' => 0,
                'safeCount' => 0,
                'totalEstimatedBudget' => 0.0,
                'aiExplanation' => [
                    'summary_headline' => 'Profil Usaha Belum Ditemukan',
                    'ai_explanation' => 'Silakan daftarkan profil usaha Anda terlebih dahulu untuk mengaktifkan fitur Saran Restock otomatis.',
                    'actionable_advice' => 'Selesaikan onboarding profil usaha Anda.',
                    'data_limitations' => 'Membutuhkan akun usaha aktif.',
                    'is_ai_generated' => false,
                ],
                'disclaimer' => RestockService::DISCLAIMER,
            ]);
        }

        $overview = $restockService->getRestockOverview($business);

        return view('livewire.restock-suggestion', [
            'hasBusiness' => true,
            'business' => $business,
            'sufficiency' => $overview['sufficiency'],
            'recommendations' => $overview['recommendations'],
            'recommendedItems' => $overview['recommended_items'],
            'safeItems' => $overview['safe_items'],
            'recommendedCount' => $overview['recommended_count'],
            'safeCount' => $overview['safe_count'],
            'totalEstimatedBudget' => $overview['total_estimated_budget'],
            'aiExplanation' => $overview['ai_explanation'],
            'disclaimer' => $overview['disclaimer'],
        ]);
    }
}

