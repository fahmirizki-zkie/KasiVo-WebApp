<?php

namespace App\Services;

use App\Models\Business;
use Carbon\Carbon;

class BusinessReportService
{
    /**
     * Klausul kepatuhan resmi KasiVo (DoD KV-050: Tidak menyebut laporan sebagai bukti kelayakan kredit).
     */
    public const DISCLAIMER = 'Dokumen ini merupakan profil ringkasan operasional dan catatan arus kas internal usaha berdasarkan pencatatan transaksi mandiri oleh pemilik usaha. Dokumen ini BUKAN merupakan surat jaminan, rekomendasi pinjaman, atau bukti kelayakan kredit perbankan/lembaga pembiayaan.';

    public function __construct(
        protected ?CashFlowService $cashFlowService = null
    ) {
        $this->cashFlowService = $cashFlowService ?? app(CashFlowService::class);
    }

    /**
     * Menyiapkan seluruh data profil usaha dan ringkasan arus kas untuk template laporan PDF.
     * Menggabungkan identitas usaha (KV-010), kalkulasi arus kas (KV-038), dan disclaimer (KV-050).
     */
    public function getProfileReportData(
        Business $business,
        string $period = 'this_month',
        ?string $customStart = null,
        ?string $customEnd = null,
        ?array $aiNarrative = null
    ): array {
        $dates = $this->cashFlowService->resolvePeriodDates($period, $customStart, $customEnd);

        $startCarbon = !empty($dates['start_date']) ? Carbon::parse($dates['start_date']) : null;
        $endCarbon = !empty($dates['end_date']) ? Carbon::parse($dates['end_date']) : null;

        $summary = $this->cashFlowService->getSummary($business, $startCarbon, $endCarbon);
        $categories = $this->cashFlowService->getCategoryBreakdown($business, $startCarbon, $endCarbon);

        $owner = $business->user;
        $now = Carbon::now();

        $businessTypeLabel = match ($business->business_type) {
            'food_beverage', 'fnb' => 'Kuliner / F&B (Makanan & Minuman)',
            'retail' => 'Toko Kelontong / Retail',
            'services' => 'Jasa / Layanan',
            default => !empty($business->business_type) ? ucwords(str_replace('_', ' ', $business->business_type)) : 'Usaha Mikro / Kecil (UMKM)',
        };

        return [
            'document' => [
                'title' => 'PROFIL USAHA & IKHTISAR ARUS KAS',
                'document_number' => 'KASIVO-REP-' . str_pad((string) $business->id, 4, '0', STR_PAD_LEFT) . '-' . $now->format('YmdHis'),
                'generated_at' => $now->toIso8601String(),
                'formatted_generated_at' => $now->translatedFormat('d F Y, H:i') . ' WIB',
            ],
            'business' => [
                'id' => $business->id,
                'name' => $business->name,
                'business_type' => $business->business_type,
                'business_type_label' => $businessTypeLabel,
                'owner_name' => $owner->name ?? 'Pemilik Usaha',
                'owner_email' => $owner->email ?? '-',
                'registered_date' => $business->created_at ? $business->created_at->translatedFormat('d F Y') : '-',
            ],
            'period' => [
                'key' => $period,
                'label' => $dates['label'] ?? 'Semua Waktu',
                'start_date' => $dates['start_date'],
                'end_date' => $dates['end_date'],
                'formatted_range' => $startCarbon && $endCarbon
                    ? $startCarbon->translatedFormat('d M Y') . ' s.d. ' . $endCarbon->translatedFormat('d M Y')
                    : 'Seluruh Riwayat',
            ],
            'cash_flow' => [
                'total_cash_in' => $summary['total_cash_in'],
                'total_cash_out' => $summary['total_cash_out'],
                'net_cash_flow' => $summary['net_cash_flow'],
                'formatted_cash_in' => $summary['formatted_cash_in'],
                'formatted_cash_out' => $summary['formatted_cash_out'],
                'formatted_net' => $summary['formatted_net'],
                'cash_in_count' => $summary['cash_in_count'],
                'cash_out_count' => $summary['cash_out_count'],
                'total_transactions' => $summary['total_transactions'],
                'is_negative' => $summary['is_negative'],
                'status_label' => $summary['is_negative'] ? 'Defisit Arus Kas (Arus Keluar Lebih Besar)' : 'Surplus Arus Kas Bersih',
            ],
            'categories' => [
                'cash_in' => $categories['cash_in'] ?? [],
                'cash_out' => $categories['cash_out'] ?? [],
            ],
            'ai_narrative' => $aiNarrative,
            'disclaimer' => self::DISCLAIMER,
        ];
    }
}

