<?php

namespace App\Http\Controllers;

use App\Services\BusinessReportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BusinessProfilePdfController extends Controller
{
    public function __construct(
        protected BusinessReportService $reportService
    ) {}

    /**
     * Mengunduh atau menampilkan pratinjau dokumen PDF profil usaha (DoD KV-052).
     */
    public function download(Request $request): Response
    {
        $user = $request->user();
        $business = $user?->businesses()->whereHas('transactions')->latest('id')->first()
            ?? $user?->business
            ?? $user?->businesses()->first();

        if (!$business) {
            return redirect()->route('onboarding')->with('warning', 'Silakan lengkapi profil usaha Anda terlebih dahulu sebelum mengunduh laporan PDF.');
        }

        $validated = $request->validate([
            'period' => 'nullable|string|in:today,last_7_days,7_days,this_month,last_month,this_year,all,all_time,custom',
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d|after_or_equal:start_date',
            'stream' => 'nullable|boolean',
            'preview' => 'nullable|boolean',
        ]);

        $period = $validated['period'] ?? 'this_month';
        $customStart = $validated['start_date'] ?? null;
        $customEnd = $validated['end_date'] ?? null;

        $pdf = $this->reportService->renderPdf($business, $period, $customStart, $customEnd);
        $filename = $this->reportService->generatePdfFilename($business, $period, $customStart, $customEnd);

        $isStream = $request->boolean('stream') || $request->boolean('preview');

        if ($isStream) {
            return $pdf->stream($filename);
        }

        return $pdf->download($filename);
    }
}

