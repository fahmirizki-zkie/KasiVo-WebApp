<div class="space-y-6">

    @if (!$hasBusiness)
        <!-- Empty State jika profil usaha belum lengkap -->
        <div class="p-8 sm:p-12 rounded-3xl bg-white border border-amber-200 shadow-sm text-center">
            <div class="w-16 h-16 mx-auto rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <h2 class="text-xl sm:text-2xl font-extrabold text-[#252525] mb-2">
                Profil Usaha Belum Terdaftar
            </h2>
            <p class="text-sm text-[#777777] max-w-md mx-auto mb-6">
                Lengkapi identitas usaha Anda terlebih dahulu untuk mengaktifkan ringkasan performa dan ekspor laporan PDF.
            </p>
            <a href="{{ route('onboarding') }}"
               wire:navigate
               class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-[#E87559] hover:bg-[#D66448] text-white text-xs sm:text-sm font-semibold shadow-sm transition-all">
                <span>Lengkapi Profil Usaha</span>
            </a>
        </div>
    @else
        <!-- 1. Header Banner & Quick Export Actions -->
        <div class="p-6 sm:p-8 rounded-3xl bg-gradient-to-r from-white via-[#FCEDEA]/30 to-[#E1F0F7]/20 border border-[#EEEEEE] shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#FCEDEA] text-[#E87559] text-xs font-bold mb-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#E87559] animate-pulse"></span>
                    <span>{{ $business->name }}</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#252525] tracking-tight">
                    Profil Usaha & Ekspor PDF
                </h1>
                <p class="text-sm text-[#777777] mt-1 max-w-xl leading-relaxed">
                    Ringkasan performa keuangan usaha dan ekspor dokumen PDF ikhtisar arus kas resmi format A4.
                </p>
            </div>

            <!-- Action Buttons: Download PDF & Pratinjau PDF -->
            <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                <a href="{{ route('business-profile.pdf', ['period' => $period, 'stream' => 1]) }}"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white hover:bg-gray-50 text-[#252525] text-xs font-semibold border border-[#EEEEEE] shadow-sm transition-all group">
                    <svg class="w-4 h-4 text-[#777777] group-hover:text-[#252525]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <span>Pratinjau PDF</span>
                </a>

                <a href="{{ route('business-profile.download', ['period' => $period]) }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#E87559] hover:bg-[#D66448] text-white text-xs font-semibold shadow-sm shadow-[#E87559]/25 hover:shadow-md transition-all active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    <span>Unduh Laporan PDF</span>
                </a>
            </div>
        </div>

        <!-- 2. Filter Periode Laporan Switcher -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 p-4 rounded-2xl bg-white border border-[#EEEEEE] shadow-sm">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-[#E87559]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-[#777777]">Periode Laporan PDF:</span>
                    <span class="text-xs font-bold text-[#252525] ml-1">{{ $report['period']['label'] }}</span>
                    <span class="text-xs text-[#777777] ml-1">({{ $report['period']['formatted_range'] }})</span>
                </div>
            </div>

            <!-- Period Switcher Buttons -->
            <div class="inline-flex items-center p-1 rounded-xl bg-gray-100 border border-gray-200 text-xs font-semibold">
                <button type="button"
                        wire:click="setPeriod('today')"
                        class="px-3.5 py-1.5 rounded-lg transition-all {{ $period === 'today' ? 'bg-[#E87559] text-white shadow-xs font-bold' : 'text-[#777777] hover:text-[#252525]' }}">
                    Hari Ini
                </button>
                <button type="button"
                        wire:click="setPeriod('last_7_days')"
                        class="px-3.5 py-1.5 rounded-lg transition-all {{ $period === 'last_7_days' ? 'bg-[#E87559] text-white shadow-xs font-bold' : 'text-[#777777] hover:text-[#252525]' }}">
                    7 Hari Terakhir
                </button>
                <button type="button"
                        wire:click="setPeriod('this_month')"
                        class="px-3.5 py-1.5 rounded-lg transition-all {{ $period === 'this_month' ? 'bg-[#E87559] text-white shadow-xs font-bold' : 'text-[#777777] hover:text-[#252525]' }}">
                    Bulan Ini (Default)
                </button>
                <button type="button"
                        wire:click="setPeriod('all_time')"
                        class="px-3.5 py-1.5 rounded-lg transition-all {{ $period === 'all_time' ? 'bg-[#E87559] text-white shadow-xs font-bold' : 'text-[#777777] hover:text-[#252525]' }}">
                    Semua Waktu
                </button>
            </div>
        </div>

        <!-- 3. Kartu Identitas Usaha & Pemilik (KV-010) -->
        <div class="p-6 rounded-3xl bg-white border border-[#EEEEEE] shadow-sm">
            <h2 class="text-base font-extrabold text-[#252525] mb-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-[#E87559]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <span>Identitas Usaha & Pemilik</span>
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                <div class="p-4 rounded-2xl bg-[#FBFBFC] border border-[#EEEEEE]">
                    <span class="block text-[11px] font-bold uppercase tracking-wider text-[#777777]">Nama Usaha</span>
                    <span class="text-sm font-extrabold text-[#252525] mt-0.5 block">{{ $report['business']['name'] }}</span>
                </div>
                <div class="p-4 rounded-2xl bg-[#FBFBFC] border border-[#EEEEEE]">
                    <span class="block text-[11px] font-bold uppercase tracking-wider text-[#777777]">Jenis Usaha</span>
                    <span class="text-sm font-extrabold text-[#252525] mt-0.5 block">{{ $report['business']['business_type_label'] }}</span>
                </div>
                <div class="p-4 rounded-2xl bg-[#FBFBFC] border border-[#EEEEEE]">
                    <span class="block text-[11px] font-bold uppercase tracking-wider text-[#777777]">Nama Pemilik</span>
                    <span class="text-sm font-extrabold text-[#252525] mt-0.5 block">{{ $report['business']['owner_name'] }}</span>
                </div>
                <div class="p-4 rounded-2xl bg-[#FBFBFC] border border-[#EEEEEE]">
                    <span class="block text-[11px] font-bold uppercase tracking-wider text-[#777777]">Email Akun</span>
                    <span class="text-sm font-semibold text-[#252525] mt-0.5 block truncate">{{ $report['business']['owner_email'] }}</span>
                </div>
                <div class="p-4 rounded-2xl bg-[#FBFBFC] border border-[#EEEEEE]">
                    <span class="block text-[11px] font-bold uppercase tracking-wider text-[#777777]">Terdaftar Sejak</span>
                    <span class="text-sm font-semibold text-[#252525] mt-0.5 block">{{ $report['business']['registered_date'] }}</span>
                </div>
                <div class="p-4 rounded-2xl bg-[#FBFBFC] border border-[#EEEEEE]">
                    <span class="block text-[11px] font-bold uppercase tracking-wider text-[#777777]">No. Dokumen Laporan</span>
                    <span class="text-xs font-semibold text-[#777777] mt-0.5 block truncate font-mono">{{ $report['document']['document_number'] }}</span>
                </div>
            </div>
        </div>

        <!-- 4. Ringkasan Finansial Arus Kas (3 Cards Responsif) (KV-038) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Kas Masuk -->
            <div class="p-6 rounded-3xl bg-white border border-[#CDE9DE] shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-[#777777]">Total Kas Masuk</span>
                        <div class="w-8 h-8 rounded-xl bg-[#E2F2ED] text-[#43A58A] flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                            </svg>
                        </div>
                    </div>
                    <span class="text-2xl sm:text-3xl font-extrabold text-[#43A58A] tracking-tight block">
                        {{ $report['cash_flow']['formatted_cash_in'] }}
                    </span>
                </div>
                <span class="text-xs text-[#777777] mt-3 pt-3 border-t border-gray-100 block">
                    {{ $report['cash_flow']['cash_in_count'] }} transaksi penjualan/masuk
                </span>
            </div>

            <!-- Kas Keluar -->
            <div class="p-6 rounded-3xl bg-white border border-[#FCD8D0] shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-[#777777]">Total Kas Keluar</span>
                        <div class="w-8 h-8 rounded-xl bg-[#FCEDEA] text-[#E87559] flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                            </svg>
                        </div>
                    </div>
                    <span class="text-2xl sm:text-3xl font-extrabold text-[#E87559] tracking-tight block">
                        {{ $report['cash_flow']['formatted_cash_out'] }}
                    </span>
                </div>
                <span class="text-xs text-[#777777] mt-3 pt-3 border-t border-gray-100 block">
                    {{ $report['cash_flow']['cash_out_count'] }} transaksi belanja/beban
                </span>
            </div>

            <!-- Arus Kas Bersih -->
            <div class="p-6 rounded-3xl bg-white border border-[#EEEEEE] shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-[#777777]">Arus Kas Bersih</span>
                        <div class="w-8 h-8 rounded-xl bg-gray-100 text-[#252525] flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                    </div>
                    <span class="text-2xl sm:text-3xl font-extrabold text-[#252525] tracking-tight block">
                        {{ $report['cash_flow']['formatted_net'] }}
                    </span>
                </div>
                <div class="mt-3 pt-3 border-t border-gray-100 flex items-center justify-between">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $report['cash_flow']['is_negative'] ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-800' }}">
                        {{ $report['cash_flow']['status_label'] }}
                    </span>
                    <span class="text-xs text-[#777777]">{{ $report['cash_flow']['total_transactions'] }} Total Tx</span>
                </div>
            </div>
        </div>

        <!-- 5. Ringkasan Narasi Cerdas AI (DoD KV-051) -->
        @if (!empty($report['ai_narrative']))
            <div class="p-6 sm:p-7 rounded-3xl bg-gradient-to-br from-white via-[#FCEDEA]/20 to-white border border-[#E87559]/20 shadow-sm space-y-3">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#FCEDEA] text-[#E87559] text-xs font-bold">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z" />
                    </svg>
                    <span>KasiVo AI Executive Summary</span>
                </div>
                <h3 class="text-lg sm:text-xl font-extrabold text-[#252525]">
                    {{ $report['ai_narrative']['headline'] ?? 'Ikhtisar Operasional KasiVo' }}
                </h3>
                <p class="text-sm text-[#555555] leading-relaxed">
                    {{ $report['ai_narrative']['narrative'] ?? $report['ai_narrative']['content'] ?? '' }}
                </p>
            </div>
        @endif

        <!-- 6. Rincian Kategori Pemasukan & Pengeluaran -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Pemasukan -->
            <div class="p-6 rounded-3xl bg-white border border-[#EEEEEE] shadow-sm">
                <h3 class="text-sm font-extrabold uppercase tracking-wider text-[#43A58A] mb-3 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#43A58A]"></span>
                    <span>Rincian Kategori Pemasukan</span>
                </h3>
                <div class="border border-[#EEEEEE] rounded-2xl overflow-hidden">
                    <table class="min-w-full divide-y divide-[#EEEEEE] text-xs">
                        <thead class="bg-[#FBFBFC] text-[#777777] font-semibold">
                            <tr>
                                <th class="px-4 py-2.5 text-left">Kategori</th>
                                <th class="px-4 py-2.5 text-right">Total</th>
                                <th class="px-4 py-2.5 text-right">Porsi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#EEEEEE]">
                            @forelse ($report['categories']['cash_in'] as $inCat)
                                <tr class="hover:bg-gray-50/60 transition-colors">
                                    <td class="px-4 py-2.5 font-medium text-[#252525]">{{ $inCat['label'] }}</td>
                                    <td class="px-4 py-2.5 text-right font-bold text-[#43A58A]">{{ $inCat['formatted_total'] }}</td>
                                    <td class="px-4 py-2.5 text-right text-[#777777]">{{ $inCat['percentage'] }}%</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-6 text-center text-[#777777]">Belum ada pemasukan pada periode ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pengeluaran -->
            <div class="p-6 rounded-3xl bg-white border border-[#EEEEEE] shadow-sm">
                <h3 class="text-sm font-extrabold uppercase tracking-wider text-[#E87559] mb-3 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#E87559]"></span>
                    <span>Rincian Kategori Pengeluaran</span>
                </h3>
                <div class="border border-[#EEEEEE] rounded-2xl overflow-hidden">
                    <table class="min-w-full divide-y divide-[#EEEEEE] text-xs">
                        <thead class="bg-[#FBFBFC] text-[#777777] font-semibold">
                            <tr>
                                <th class="px-4 py-2.5 text-left">Kategori</th>
                                <th class="px-4 py-2.5 text-right">Total</th>
                                <th class="px-4 py-2.5 text-right">Porsi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#EEEEEE]">
                            @forelse ($report['categories']['cash_out'] as $outCat)
                                <tr class="hover:bg-gray-50/60 transition-colors">
                                    <td class="px-4 py-2.5 font-medium text-[#252525]">{{ $outCat['label'] }}</td>
                                    <td class="px-4 py-2.5 text-right font-bold text-[#E87559]">{{ $outCat['formatted_total'] }}</td>
                                    <td class="px-4 py-2.5 text-right text-[#777777]">{{ $outCat['percentage'] }}%</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-6 text-center text-[#777777]">Belum ada pengeluaran pada periode ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 7. Disclaimer Resmi Kepatuhan (DoD Wajib KV-050) -->
        <div class="p-4 sm:p-5 rounded-2xl bg-[#F8FAFC] border border-[#E2E8F0] flex items-start gap-3.5">
            <div class="w-7 h-7 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center shrink-0 mt-0.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="flex-1 text-xs text-[#64748B] leading-relaxed">
                <span class="font-bold text-[#334155]">Pernyataan Kepatuhan & Batasan Dokumen (Disclaimer):</span>
                <span class="ml-1">{{ $report['disclaimer'] }}</span>
            </div>
        </div>
    @endif

</div>
