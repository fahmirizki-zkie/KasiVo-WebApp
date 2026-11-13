<?php

namespace App\Services;

use App\Models\Business;
use App\Services\Ai\GroqService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class BusinessReportService
{
    /**
     * Klausul kepatuhan resmi KasiVo (DoD KV-050: Tidak menyebut laporan sebagai bukti kelayakan kredit).
     */
    public const DISCLAIMER = 'Dokumen ini merupakan profil ringkasan operasional dan catatan arus kas internal usaha berdasarkan pencatatan transaksi mandiri oleh pemilik usaha. Dokumen ini BUKAN merupakan surat jaminan, rekomendasi pinjaman, atau bukti kelayakan kredit perbankan/lembaga pembiayaan.';

    public function __construct(
        protected ?CashFlowService $cashFlowService = null,
        protected ?GroqService $groqService = null
    ) {
        $this->cashFlowService = $cashFlowService ?? app(CashFlowService::class);
    }

    public function getGroqService(): GroqService
    {
        return $this->groqService ??= app(GroqService::class);
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

    /**
     * Membuat ringkasan narasi operasional dan kinerja usaha menggunakan Groq LLM (DoD KV-051).
     * Sesuai mandat SPS: Menggunakan data riil, tidak mengarang omzet, dan tidak membuat keputusan pembiayaan.
     */
    public function generateAiNarrative(
        Business $business,
        string $period = 'this_month',
        ?string $customStart = null,
        ?string $customEnd = null,
        ?GroqService $customGroq = null
    ): array {
        $reportData = $this->getProfileReportData($business, $period, $customStart, $customEnd);
        $fallback = $this->buildFallbackNarrative($reportData);

        // Jika tidak ada transaksi sama sekali pada periode ini
        if (($reportData['cash_flow']['total_transactions'] ?? 0) === 0) {
            return $fallback;
        }

        try {
            $groq = $customGroq ?? $this->getGroqService();

            $systemPrompt = <<<PROMPT
Anda adalah KasiVo Financial Analyst, asisten cerdas analisis keuangan dan operasional untuk usaha mikro dan kecil (UMKM) F&B di Indonesia.
Tugas Anda: Membuat narasi ringkas (2-3 kalimat) profil kinerja usaha dan perputaran arus kas berdasarkan data aktual yang tercatat pada periode yang ditentukan.

PANDUAN & ATURAN WAJIB KEPATUHAN (DoD KasiVo):
1. TIDAK BOLEH MENGARANG OMZET: Gunakan HANYA angka aktual yang tertera di payload data (Kas Masuk, Kas Keluar, Arus Kas Bersih, jumlah transaksi). Dilarang mengasumsikan angka omzet tambahan atau proyeksi di luar catatan riil.
2. DILARANG MEMBUAT KEPUTUSAN / REKOMENDASI PEMBIAYAAN: Dokumen ini BUKAN analisis kelayakan kredit. Dilarang keras memberikan keputusan atau rekomendasi seperti "usaha ini layak mendapat pinjaman modal", "disetujui kredit", atau saran underwriting bank.
3. Gunakan bahasa Indonesia formal yang santun, objektif, dan profesional untuk dokumen profil usaha.
4. Format output HANYA dalam JSON valid tanpa markdown tambahan:
{
    "headline": "Judul ringkas (misal: Ikhtisar Kinerja Operasional & Arus Kas)",
    "narrative": "Narasi deskriptif 2-3 kalimat objektif merangkum kas masuk, kas keluar, posisi arus kas bersih, dan alokasi kategori pengeluaran terbesar",
    "highlights": [
        "Poin 1: Kas masuk tercatat",
        "Poin 2: Alokasi pengeluaran terbesar",
        "Poin 3: Posisi arus kas bersih"
    ],
    "compliance_note": "Ringkasan narasi ini disusun secara deskriptif berdasarkan pencatatan transaksi aktual di platform KasiVo, bukan merupakan evaluasi atau keputusan kelayakan kredit pembiayaan."
}
PROMPT;

            $cashFlow = $reportData['cash_flow'];
            $categoriesIn = $reportData['categories']['cash_in'] ?? [];
            $categoriesOut = $reportData['categories']['cash_out'] ?? [];

            $topExpense = !empty($categoriesOut) ? ($categoriesOut[0]['label'] . ' (' . $categoriesOut[0]['formatted_total'] . ')') : '-';
            $topIncome = !empty($categoriesIn) ? ($categoriesIn[0]['label'] . ' (' . $categoriesIn[0]['formatted_total'] . ')') : '-';

            $userPayload = [
                'business_name' => $reportData['business']['name'],
                'business_type' => $reportData['business']['business_type_label'],
                'period_label' => $reportData['period']['label'],
                'period_range' => $reportData['period']['formatted_range'],
                'total_cash_in' => $cashFlow['total_cash_in'],
                'formatted_cash_in' => $cashFlow['formatted_cash_in'],
                'cash_in_count' => $cashFlow['cash_in_count'],
                'total_cash_out' => $cashFlow['total_cash_out'],
                'formatted_cash_out' => $cashFlow['formatted_cash_out'],
                'cash_out_count' => $cashFlow['cash_out_count'],
                'net_cash_flow' => $cashFlow['net_cash_flow'],
                'formatted_net' => $cashFlow['formatted_net'],
                'status_label' => $cashFlow['status_label'],
                'top_expense_category' => $topExpense,
                'top_income_category' => $topIncome,
            ];

            $messages = [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => json_encode($userPayload, JSON_UNESCAPED_UNICODE)],
            ];

            $response = $groq->chat($messages, jsonMode: true);

            return $this->sanitizeAiNarrative($response, $fallback);
        } catch (\Throwable $e) {
            Log::warning('Groq Business Profile AI Narrative Fallback Triggered', [
                'business_id' => $business->id,
                'error' => $e->getMessage(),
            ]);

            return $fallback;
        }
    }

    /**
     * Membangun narasi deskriptif deterministik berdasarkan data aktual (DoD KV-051 Fallback).
     * Menjamin zero crash, data riil 100%, dan bebas dari klaim kelayakan kredit.
     */
    public function buildFallbackNarrative(array $reportData): array
    {
        $business = $reportData['business'] ?? [];
        $period = $reportData['period'] ?? [];
        $cashFlow = $reportData['cash_flow'] ?? [];
        $categoriesOut = $reportData['categories']['cash_out'] ?? [];

        $businessName = $business['name'] ?? 'Usaha';
        $periodLabel = $period['label'] ?? 'periode ini';
        $totalTransactions = (int) ($cashFlow['total_transactions'] ?? 0);

        if ($totalTransactions === 0) {
            return [
                'is_ai_generated' => false,
                'source' => 'rule_engine_fallback',
                'headline' => 'Ikhtisar Aktivitas Usaha',
                'narrative' => sprintf(
                    'Pada periode %s, %s belum mencatatkan transaksi kas masuk maupun kas keluar. Mulai catat transaksi harian untuk melihat rangkuman kinerja operasional usaha Anda secara otomatis.',
                    $periodLabel,
                    $businessName
                ),
                'highlights' => [
                    'Belum ada transaksi tercatat pada periode ini',
                    'Posisi kas masuk dan kas keluar Rp 0',
                ],
                'compliance_note' => 'Ringkasan narasi ini disusun secara deskriptif berdasarkan pencatatan transaksi aktual di platform KasiVo, bukan merupakan evaluasi atau keputusan kelayakan kredit pembiayaan.',
            ];
        }

        $topExpenseCategory = !empty($categoriesOut) ? $categoriesOut[0]['label'] : 'operasional';
        $statusDesc = $cashFlow['is_negative']
            ? sprintf('posisi defisit sebesar %s di mana arus pengeluaran lebih besar daripada pemasukan', $cashFlow['formatted_net'])
            : sprintf('posisi surplus arus kas bersih sebesar %s', $cashFlow['formatted_net']);

        $narrative = sprintf(
            'Selama periode %s, %s mencatatkan total perputaran kas masuk sebesar %s dari %d transaksi dan total pengeluaran sebesar %s dari %d transaksi. Arus kas berada dalam %s, dengan alokasi pengeluaran terbesar dialokasikan untuk pos %s.',
            $periodLabel,
            $businessName,
            $cashFlow['formatted_cash_in'],
            $cashFlow['cash_in_count'],
            $cashFlow['formatted_cash_out'],
            $cashFlow['cash_out_count'],
            $statusDesc,
            $topExpenseCategory
        );

        return [
            'is_ai_generated' => false,
            'source' => 'rule_engine_fallback',
            'headline' => 'Ikhtisar Kinerja Operasional & Arus Kas',
            'narrative' => $narrative,
            'highlights' => [
                'Pemasukan tercatat sebesar ' . $cashFlow['formatted_cash_in'],
                'Pengeluaran tercatat sebesar ' . $cashFlow['formatted_cash_out'],
                'Arus kas bersih ' . $cashFlow['formatted_net'] . ' (' . $cashFlow['status_label'] . ')',
            ],
            'compliance_note' => 'Ringkasan narasi ini disusun secara deskriptif berdasarkan pencatatan transaksi aktual di platform KasiVo, bukan merupakan evaluasi atau keputusan kelayakan kredit pembiayaan.',
        ];
    }

    /**
     * Memverifikasi dan menyaring narasi AI agar mematuhi aturan DoD KV-051.
     * Mencegah adanya klaim kelayakan kredit atau keputusan pembiayaan.
     */
    public function sanitizeAiNarrative(array $aiResponse, array $fallback): array
    {
        $narrative = $aiResponse['narrative'] ?? '';

        if (empty($narrative)) {
            return $fallback;
        }

        // Daftar frasa terlarang terkait keputusan pembiayaan / kredit
        $prohibitedPhrases = [
            'layak kredit',
            'kelayakan kredit',
            'disetujui pinjaman',
            'rekomendasi pinjaman',
            'layak pembiayaan',
            'keputusan pembiayaan',
            'rekomendasi kredit',
            'persetujuan kredit',
            'layak mendapatkan pinjaman',
            'layak menerima pembiayaan',
        ];

        foreach ($prohibitedPhrases as $phrase) {
            if (mb_stripos($narrative, $phrase) !== false) {
                Log::warning('Prohibited financing decision phrase detected in AI narrative, falling back to compliant template', [
                    'phrase' => $phrase,
                ]);
                return $fallback;
            }
        }

        return [
            'is_ai_generated' => true,
            'source' => 'groq_llm',
            'headline' => $aiResponse['headline'] ?? $fallback['headline'],
            'narrative' => $narrative,
            'highlights' => $aiResponse['highlights'] ?? $fallback['highlights'],
            'compliance_note' => $aiResponse['compliance_note'] ?? $fallback['compliance_note'],
        ];
    }

    /**
     * Menghasilkan data laporan profil usaha lengkap beserta narasi AI (siap cetak PDF di KV-052).
     */
    public function getCompleteProfileReportWithAi(
        Business $business,
        string $period = 'this_month',
        ?string $customStart = null,
        ?string $customEnd = null,
        ?GroqService $customGroq = null
    ): array {
        $aiNarrative = $this->generateAiNarrative($business, $period, $customStart, $customEnd, $customGroq);
        return $this->getProfileReportData($business, $period, $customStart, $customEnd, $aiNarrative);
    }
}

