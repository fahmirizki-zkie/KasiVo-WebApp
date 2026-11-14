<div class="space-y-6">

    <!-- Welcome Banner Card -->
    <div class="p-6 sm:p-8 rounded-3xl bg-gradient-to-r from-white via-[#FCEDEA]/30 to-[#E1F0F7]/20 border border-[#EEEEEE] shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#FCEDEA] text-[#E87559] text-xs font-bold mb-2">
                <span class="w-1.5 h-1.5 rounded-full bg-[#E87559] animate-pulse"></span>
                <span>{{ $business ? $business->name : 'Sistem POS & Kasir Online' }}</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#252525] tracking-tight">
                Selamat datang kembali, {{ auth()->user()->name }}
            </h1>
            <p class="text-sm text-[#777777] mt-1 max-w-xl">
                Pantau ringkasan arus kas, histori pencatatan, dan performa keuangan usaha Anda secara transparan dan akurat.
            </p>
        </div>

        <!-- Quick Action Shortcut Buttons -->
        <div class="flex flex-wrap items-center gap-2.5 shrink-0">
            <a href="{{ route('transactions.income') }}" 
               wire:navigate
               class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-[#43A58A] hover:bg-[#388D76] text-white text-xs font-semibold shadow-sm shadow-[#43A58A]/25 hover:shadow-md transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                <span>Catat Penjualan</span>
            </a>
            <a href="{{ route('transactions.expense') }}" 
               wire:navigate
               class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-[#E87559] hover:bg-[#D66448] text-white text-xs font-semibold shadow-sm shadow-[#E87559]/25 hover:shadow-md transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4"/></svg>
                <span>Catat Pengeluaran</span>
            </a>
        </div>
    </div>

    <!-- Filter Periode Dashboard (KV-041) -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 p-4 rounded-2xl bg-white border border-[#EEEEEE] shadow-sm">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-[#E87559]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-[#777777]">Rentang Periode Kas:</span>
                <span class="text-xs font-bold text-[#252525] ml-1">{{ $periodLabel }}</span>
            </div>
        </div>

        <!-- Period Switcher Buttons -->
        <div class="inline-flex items-center p-1 rounded-xl bg-gray-100 border border-gray-200 text-xs font-semibold">
            <button type="button"
                    wire:click="setPeriod('today')"
                    class="px-3.5 py-1.5 rounded-lg transition-all {{ $period === 'today' ? 'bg-white text-[#252525] shadow-xs font-bold' : 'text-[#777777] hover:text-[#252525]' }}">
                Hari Ini
            </button>
            <button type="button"
                    wire:click="setPeriod('7_days')"
                    class="px-3.5 py-1.5 rounded-lg transition-all {{ $period === '7_days' ? 'bg-white text-[#252525] shadow-xs font-bold' : 'text-[#777777] hover:text-[#252525]' }}">
                7 Hari Terakhir
            </button>
            <button type="button"
                    wire:click="setPeriod('this_month')"
                    class="px-3.5 py-1.5 rounded-lg transition-all {{ $period === 'this_month' ? 'bg-white text-[#E87559] shadow-xs font-bold' : 'text-[#777777] hover:text-[#252525]' }}">
                Bulan Ini (Default)
            </button>
        </div>
    </div>

    {{-- Alert Edukatif Kas Negatif (DoD KV-043) --}}
    @if ($summary['is_negative'])
        <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 flex items-start gap-3">
            <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center shrink-0 mt-0.5 font-bold">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <h4 class="text-xs font-bold text-amber-900">Catatan Arus Kas Masuk & Keluar</h4>
                <p class="text-xs text-amber-800 mt-0.5 leading-relaxed">
                    Pengeluaran kas tercatat lebih besar dari kas masuk pada rentang <strong>{{ $periodLabel }}</strong>. 
                    Dalam usaha kuliner & F&B, kondisi ini wajar terjadi saat melakukan pembelanjaan stok bahan baku dalam jumlah besar atau biaya operasional awal periode.
                </p>
            </div>
        </div>
    @endif

    <!-- 3 Kartu Ringkasan Kas Utama (DoD KV-040) + 1 Kartu Ringkasan Aktivitas -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Card 1: Kas Masuk -->
        <div class="p-5 rounded-2xl bg-white border border-[#EEEEEE] shadow-sm hover:border-[#43A58A]/40 transition flex flex-col justify-between">
            <div class="flex items-center justify-between text-xs text-[#777777]">
                <span class="font-medium">Total Kas Masuk</span>
                <span class="p-1.5 rounded-lg bg-[#DDF1EC] text-[#43A58A]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                    </svg>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-extrabold text-[#252525] tracking-tight" data-testid="dashboard-cash-in">
                    {{ $summary['formatted_cash_in'] }}
                </div>
                <div class="mt-1 text-xs text-[#43A58A] font-semibold flex items-center gap-1">
                    <span>{{ $summary['cash_in_count'] }} transaksi penjualan/masuk</span>
                </div>
            </div>
        </div>

        <!-- Card 2: Kas Keluar -->
        <div class="p-5 rounded-2xl bg-white border border-[#EEEEEE] shadow-sm hover:border-[#E87559]/40 transition flex flex-col justify-between">
            <div class="flex items-center justify-between text-xs text-[#777777]">
                <span class="font-medium">Total Kas Keluar</span>
                <span class="p-1.5 rounded-lg bg-[#FCEDEA] text-[#E87559]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                    </svg>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-extrabold text-[#252525] tracking-tight" data-testid="dashboard-cash-out">
                    {{ $summary['formatted_cash_out'] }}
                </div>
                <div class="mt-1 text-xs text-[#E87559] font-semibold flex items-center gap-1">
                    <span>{{ $summary['cash_out_count'] }} transaksi belanja/keluar</span>
                </div>
            </div>
        </div>

        <!-- Card 3: Arus Kas Bersih (Net Cash Flow) -->
        <div class="p-5 rounded-2xl bg-white border border-[#EEEEEE] shadow-sm hover:border-[#252525]/30 transition flex flex-col justify-between">
            <div class="flex items-center justify-between text-xs text-[#777777]">
                <span class="font-medium">Arus Kas Bersih</span>
                @if ($summary['is_negative'])
                    <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 font-bold text-[10px]">
                        Kas Keluar Lebih Besar
                    </span>
                @else
                    <span class="px-2 py-0.5 rounded-full bg-[#DDF1EC] text-[#43A58A] font-bold text-[10px]">
                        Kas Surplus
                    </span>
                @endif
            </div>
            <div class="mt-3">
                <div class="text-2xl font-extrabold {{ $summary['is_negative'] ? 'text-amber-700' : 'text-[#252525]' }} tracking-tight" data-testid="dashboard-net-cash">
                    {{ $summary['formatted_net'] }}
                </div>
                <div class="mt-1 text-xs text-[#777777]">
                    <span>Kas Masuk dikurangi Kas Keluar</span>
                </div>
            </div>
        </div>

        <!-- Card 4: Total Transaksi Tercatat -->
        <div class="p-5 rounded-2xl bg-white border border-[#EEEEEE] shadow-sm hover:border-[#E87559]/30 transition flex flex-col justify-between">
            <div class="flex items-center justify-between text-xs text-[#777777]">
                <span class="font-medium">Aktivitas Transaksi</span>
                <span class="px-2 py-0.5 rounded-full bg-[#E1F0F7] text-[#252525] font-bold text-[10px]">
                    {{ $periodLabel }}
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-extrabold text-[#252525]" data-testid="dashboard-total-tx">
                    {{ $summary['total_transactions'] }} Transaksi
                </div>
                <div class="mt-1 text-xs text-[#777777]">
                    Tersimpan aman di buku kas
                </div>
            </div>
        </div>

    </div>

    <!-- Empty State jika belum ada transaksi di periode terpilih (DoD KV-043) -->
    @if ($summary['total_transactions'] === 0)
        <div class="p-8 rounded-3xl bg-white border border-dashed border-gray-300 text-center flex flex-col items-center justify-center">
            <div class="w-14 h-14 rounded-2xl bg-gray-50 border border-gray-100 flex items-center justify-center text-gray-400 mb-3">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
            </div>
            <h3 class="text-base font-bold text-[#252525]">Belum Ada Catatan Kas di Periode Ini</h3>
            <p class="text-xs text-[#777777] mt-1 max-w-md">
                Tidak ada riwayat transaksi kas masuk maupun kas keluar yang tercatat pada rentang <strong>{{ $periodLabel }}</strong>.
            </p>
            <div class="mt-4 flex items-center gap-3">
                <a href="{{ route('transactions.income') }}" wire:navigate class="px-4 py-2 rounded-xl bg-[#43A58A] hover:bg-[#388D76] text-white text-xs font-semibold transition">
                    + Catat Penjualan Sekarang
                </a>
                <a href="{{ route('transactions.expense') }}" wire:navigate class="px-4 py-2 rounded-xl bg-white hover:bg-gray-50 text-[#252525] border border-gray-200 text-xs font-semibold transition">
                    + Catat Belanja Bahan
                </a>
            </div>
        </div>
    @else
        <!-- 4. Grafik Tren Kas Masuk & Kas Keluar Harian (KV-042 & KV-043) -->
        <div x-data="{ 
                selectedTrend: null,
                init() {
                    const trends = @js($dailySeries);
                    if (Array.isArray(trends) && trends.length > 0) {
                        const today = trends.find(t => t.is_today);
                        this.selectedTrend = today || trends[trends.length - 1];
                    }
                }
             }" 
             class="p-6 sm:p-8 rounded-3xl bg-white border border-[#EEEEEE] shadow-sm space-y-6">
            
            <!-- Header Grafik & Legenda Ringkasan -->
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <div>
                    <div class="flex flex-wrap items-center gap-2.5">
                        <h2 class="text-lg sm:text-xl font-bold text-[#252525]">
                            Grafik Tren Arus Kas Harian
                        </h2>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-[#FCEDEA] text-[#E87559] text-[10px] font-bold">
                            Kas Riil &bull; Tanpa Laba
                        </span>
                    </div>
                    <p class="text-xs text-[#777777] mt-1">
                        Memantau pergerakan uang tunai riil: kapan omzet penjualan melonjak dan kapan belanja bahan baku dilakukan. <span class="font-semibold text-[#252525]">Tidak menampilkan angka laba</span> sesuai standar pencatatan kas riil.
                    </p>
                </div>

                <!-- Legenda Ringkasan Periode (Kas Masuk vs Kas Keluar) -->
                <div class="flex flex-wrap items-center gap-3 text-xs shrink-0">
                    <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-[#DDF1EC]/60 border border-[#43A58A]/25">
                        <span class="w-3 h-3 rounded-full bg-[#43A58A]"></span>
                        <span class="font-bold text-[#252525]">Kas Masuk:</span>
                        <span class="text-[#43A58A] font-extrabold">{{ $summary['formatted_cash_in'] }}</span>
                    </div>
                    <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-[#FCEDEA]/60 border border-[#E87559]/25">
                        <span class="w-3 h-3 rounded-full bg-[#E87559]"></span>
                        <span class="font-bold text-[#252525]">Kas Keluar:</span>
                        <span class="text-[#E87559] font-extrabold">{{ $summary['formatted_cash_out'] }}</span>
                    </div>
                </div>
            </div>

            <!-- 3 Kartu Insight Arus Kas Cepat (Rata-Rata & Puncak Penjualan) -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 pt-1">
                
                <!-- Insight 1: Rata-rata Masuk -->
                <div class="p-3.5 rounded-2xl bg-[#DDF1EC]/35 border border-[#43A58A]/25 flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-[#43A58A] text-white flex items-center justify-center shrink-0 shadow-xs" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-graph-up" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M0 0h1v15h15v1H0zm14.817 3.113a.5.5 0 0 1 .07.704l-4.5 5.5a.5.5 0 0 1-.74.037L7.06 6.767l-3.656 5.027a.5.5 0 0 1-.808-.588l4-5.5a.5.5 0 0 1 .758-.06l2.609 2.61 4.15-5.073a.5.5 0 0 1 .704-.07"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <div class="text-[11px] font-bold text-[#777777] uppercase tracking-wider">Rata-Rata Masuk</div>
                        <div class="text-sm sm:text-base font-extrabold text-[#252525] truncate">
                            {{ $avgDailyCashIn }}<span class="text-xs font-normal text-[#777777]">/hari</span>
                        </div>
                        <div class="text-[10px] text-[#43A58A] font-medium truncate mt-0.5">
                            {{ $summary['cash_in_count'] }} transaksi omzet
                        </div>
                    </div>
                </div>

                <!-- Insight 2: Rata-rata Keluar -->
                <div class="p-3.5 rounded-2xl bg-[#FCEDEA]/35 border border-[#E87559]/25 flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-[#E87559] text-white flex items-center justify-center shrink-0 shadow-xs" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-cart-check" viewBox="0 0 16 16">
                            <path d="M11.354 6.354a.5.5 0 0 0-.708-.708L8 8.293 6.854 7.146a.5.5 0 1 0-.708.708l1.5 1.5a.5.5 0 0 0 .708 0z"/>
                            <path d="M.5 1a.5.5 0 0 0 0 1h1.11l.401 1.607 1.498 7.985A.5.5 0 0 0 4 12h1a2 2 0 1 0 0 4 2 2 0 0 0 0-4h7a2 2 0 1 0 0 4 2 2 0 0 0 0-4h1a.5.5 0 0 0 .491-.408l1.5-8A.5.5 0 0 0 14.5 3H2.89l-.405-1.621A.5.5 0 0 0 2 1zm3.915 10L3.102 4h10.796l-1.313 7zM6 14a1 1 0 1 1-2 0 1 1 0 0 1 2 0m7 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <div class="text-[11px] font-bold text-[#777777] uppercase tracking-wider">Rata-Rata Keluar</div>
                        <div class="text-sm sm:text-base font-extrabold text-[#252525] truncate">
                            {{ $avgDailyCashOut }}<span class="text-xs font-normal text-[#777777]">/hari</span>
                        </div>
                        <div class="text-[10px] text-[#E87559] font-medium truncate mt-0.5">
                            {{ $summary['cash_out_count'] }} belanja bahan &amp; modal
                        </div>
                    </div>
                </div>

                <!-- Insight 3: Puncak Penjualan -->
                <div class="p-3.5 rounded-2xl bg-[#E1F0F7]/45 border border-[#252525]/10 flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-[#252525] text-white flex items-center justify-center shrink-0 shadow-xs" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-star-fill text-[#E87559]" viewBox="0 0 16 16">
                            <path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <div class="text-[11px] font-bold text-[#777777] uppercase tracking-wider">Puncak Kas Masuk</div>
                        <div class="text-sm sm:text-base font-extrabold text-[#252525] truncate">
                            {{ $peakInLabel !== '-' ? $peakInLabel : 'Belum ada data' }}
                        </div>
                        <div class="text-[10px] text-[#777777] font-medium truncate mt-0.5">
                            Omzet tertinggi periode ini
                        </div>
                    </div>
                </div>

            </div>

            <!-- Area Grafik Batang Visual dengan Sumbu Y & Grid Bantu (KV-042) -->
            <div class="space-y-4 pt-2">
                
                <div class="flex">
                    <!-- Sumbu Y Kiri: Label Skala Nominal -->
                    <div class="w-14 sm:w-16 shrink-0 flex flex-col justify-between text-right pr-2 sm:pr-3 text-[10px] sm:text-[11px] font-bold text-[#777777] h-64 pb-10 select-none">
                        <span>{{ $chartYMaxLabel }}</span>
                        <span>{{ $chartYHalfLabel }}</span>
                        <span>Rp 0</span>
                    </div>

                    <!-- Area Grafik & Kolom Scrollable -->
                    <div class="flex-1 min-w-0 relative">
                        <!-- 3 Garis Grid Horizontal Sumbu Y -->
                        <div class="absolute inset-x-0 top-0 h-64 pb-10 pointer-events-none flex flex-col justify-between">
                            <div class="w-full border-b border-dashed border-[#EEEEEE]"></div>
                            <div class="w-full border-b border-dashed border-[#EEEEEE]"></div>
                            <div class="w-full border-b border-solid border-[#EEEEEE]"></div>
                        </div>

                        <!-- Kontainer Scroll Kolom Tanggal -->
                        <div class="overflow-x-auto pb-3 pt-6 -mt-6">
                            <div class="inline-flex items-end gap-1.5 sm:gap-2 min-w-full px-1 sm:px-2 h-64 pb-10">
                                @foreach ($dailySeries as $trend)
                                    <div 
                                        @click="selectedTrend = {{ json_encode($trend) }}"
                                        @mouseenter="selectedTrend = {{ json_encode($trend) }}"
                                        :class="selectedTrend && selectedTrend.date === '{{ $trend['date'] }}' ? 'bg-[#FCEDEA]/40 ring-2 ring-[#E87559]/60' : 'hover:bg-[#FCEDEA]/25'"
                                        class="flex-1 min-w-[48px] sm:min-w-[56px] max-w-[72px] h-full flex flex-col items-center justify-end group cursor-pointer rounded-xl p-1 transition-all"
                                    >
                                        <!-- Mini Floating Badge: Puncak Omzet atau Hari Ini -->
                                        <div class="h-4 flex items-center justify-center mb-1">
                                            @if ($trend['is_peak_in'] ?? false)
                                                <span class="text-[8px] font-extrabold text-[#43A58A] bg-[#DDF1EC] border border-[#43A58A]/30 px-1 py-0.2 rounded-full whitespace-nowrap shadow-2xs">
                                                    ★ Puncak
                                                </span>
                                            @elseif ($trend['is_today'] ?? false)
                                                <span class="text-[8px] font-extrabold text-[#E87559] bg-[#FCEDEA] border border-[#E87559]/30 px-1 py-0.2 rounded-full whitespace-nowrap shadow-2xs">
                                                    Hari Ini
                                                </span>
                                            @endif
                                        </div>

                                        <!-- Pasangan Batang Harian (Kas Masuk vs Kas Keluar) -->
                                        <div class="w-full flex items-end justify-center gap-1 sm:gap-1.5 h-full pb-0.5">
                                            <!-- Batang Kas Masuk -->
                                            <div 
                                                style="height: {{ max($trend['cash_in_percent'] ?? 0, ($trend['cash_in'] > 0 ? 8 : 2)) }}%;" 
                                                class="w-2.5 sm:w-3.5 rounded-t-md bg-[#43A58A] transition-all duration-300 {{ $trend['cash_in'] == 0 ? 'opacity-20 bg-[#EEEEEE]' : 'group-hover:brightness-95' }}"
                                                title="Kas Masuk: {{ $trend['formatted_cash_in'] }} ({{ $trend['day_name'] }}, {{ $trend['short_date'] ?? $trend['label'] }})"
                                            ></div>

                                            <!-- Batang Kas Keluar -->
                                            <div 
                                                style="height: {{ max($trend['cash_out_percent'] ?? 0, ($trend['cash_out'] > 0 ? 8 : 2)) }}%;" 
                                                class="w-2.5 sm:w-3.5 rounded-t-md bg-[#E87559] transition-all duration-300 {{ $trend['cash_out'] == 0 ? 'opacity-20 bg-[#EEEEEE]' : 'group-hover:brightness-95' }}"
                                                title="Kas Keluar: {{ $trend['formatted_cash_out'] }} ({{ $trend['day_name'] }}, {{ $trend['short_date'] ?? $trend['label'] }})"
                                            ></div>
                                        </div>

                                        <!-- Label Hari & Tanggal -->
                                        <div class="mt-2 text-center select-none w-full">
                                            <span class="block text-[10px] sm:text-[11px] font-bold leading-tight {{ ($trend['is_today'] ?? false) ? 'text-[#E87559]' : 'text-[#252525]' }}">
                                                {{ $trend['day_name'] }}
                                            </span>
                                            <span class="block text-[9px] sm:text-[10px] font-medium text-[#777777] leading-tight mt-0.5">
                                                {{ $trend['day_number'] ?? $trend['label'] }}
                                            </span>
                                            @if ($trend['is_today'] ?? false)
                                                <span class="inline-block w-1.5 h-1.5 rounded-full bg-[#E87559] mt-0.5"></span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Inspector Card: Detail Hari Interaktif & Komprehensif (Ramah Mobile/Touch & Tidak Terpotong) -->
                <div class="p-4 sm:p-5 rounded-2xl bg-[#FCEDEA]/15 border border-[#EEEEEE] space-y-3">
                    
                    <!-- Baris Atas Inspector: Identitas Tanggal & Status -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-[#EEEEEE]">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded-lg bg-[#252525] text-white font-bold text-[10px] uppercase tracking-wider">
                                Rincian Harian
                            </span>
                            <template x-if="selectedTrend">
                                <span class="font-extrabold text-[#252525] text-sm sm:text-base">
                                    <span x-text="selectedTrend.day_name"></span>, <span x-text="selectedTrend.short_date || selectedTrend.label"></span>
                                    <template x-if="selectedTrend.is_today">
                                        <span class="ml-1 px-2 py-0.5 rounded-md bg-[#FCEDEA] text-[#E87559] text-xs font-bold">(Hari Ini)</span>
                                    </template>
                                    <template x-if="selectedTrend.is_peak_in">
                                        <span class="ml-1 px-2 py-0.5 rounded-md bg-[#DDF1EC] text-[#43A58A] text-xs font-bold">★ Puncak Kas Masuk</span>
                                    </template>
                                </span>
                            </template>
                            <template x-if="!selectedTrend">
                                <span class="text-xs text-[#777777] italic">
                                    Sentuh atau arahkan kursor ke kolom diagram untuk melihat detail hari tersebut
                                </span>
                            </template>
                        </div>

                        <template x-if="selectedTrend">
                            <div class="text-xs text-[#777777] font-semibold flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#777777]"></span>
                                <span>Aktivitas: <strong class="text-[#252525]" x-text="(selectedTrend.tx_count || 0) + ' transaksi tercatat'"></strong></span>
                            </div>
                        </template>
                    </div>

                    <!-- Baris Bawah Inspector: 3 Metrik Detail (Kas Masuk, Kas Keluar, Selisih Kas Harian) -->
                    <template x-if="selectedTrend">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-1">
                            
                            <!-- Detail 1: Kas Masuk Hari Itu -->
                            <div class="p-3 rounded-xl bg-white border border-[#43A58A]/25 shadow-2xs">
                                <div class="text-[10px] font-bold text-[#777777] uppercase tracking-wider flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-[#43A58A]"></span>
                                    <span>Kas Masuk (Penjualan)</span>
                                </div>
                                <div class="mt-1 text-base sm:text-lg font-extrabold text-[#43A58A]" x-text="selectedTrend.formatted_cash_in"></div>
                                <div class="text-[10px] text-[#777777] mt-0.5">Uang masuk dari omzet warung</div>
                            </div>

                            <!-- Detail 2: Kas Keluar Hari Itu -->
                            <div class="p-3 rounded-xl bg-white border border-[#E87559]/25 shadow-2xs">
                                <div class="text-[10px] font-bold text-[#777777] uppercase tracking-wider flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-[#E87559]"></span>
                                    <span>Kas Keluar (Belanja)</span>
                                </div>
                                <div class="mt-1 text-base sm:text-lg font-extrabold text-[#E87559]" x-text="selectedTrend.formatted_cash_out"></div>
                                <div class="text-[10px] text-[#777777] mt-0.5">Bahan baku &amp; operasional</div>
                            </div>

                            <!-- Detail 3: Selisih Kas Bersih Hari Itu -->
                            <div class="p-3 rounded-xl bg-white border shadow-2xs" :class="selectedTrend.is_surplus ? 'border-[#43A58A]/35' : 'border-[#E87559]/35'">
                                <div class="text-[10px] font-bold text-[#777777] uppercase tracking-wider flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full" :class="selectedTrend.is_surplus ? 'bg-[#43A58A]' : 'bg-[#E87559]'"></span>
                                    <span>Selisih Kas Harian</span>
                                </div>
                                <div class="mt-1 text-base sm:text-lg font-extrabold" :class="selectedTrend.is_surplus ? 'text-[#43A58A]' : 'text-[#E87559]'" x-text="selectedTrend.formatted_net"></div>
                                <div class="text-[10px] mt-0.5 font-semibold" :class="selectedTrend.is_surplus ? 'text-[#43A58A]' : 'text-[#E87559]'">
                                    <span x-text="selectedTrend.is_surplus ? 'Kas Berjalan Surplus' : 'Belanja Stok Lebih Besar (Bukan Rugi)'"></span>
                                </div>
                            </div>

                        </div>
                    </template>

                </div>

                <!-- Footer Informasi & Distribusi Hari Periode -->
                <div class="pt-3 border-t border-[#EEEEEE] flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-[#777777]">
                    
                    <!-- Distribusi Hari -->
                    <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                        <span class="font-bold text-[#252525]">Distribusi Periode:</span>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-[#DDF1EC] text-[#43A58A] font-bold text-[11px]">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#43A58A]"></span>
                            <span>{{ $surplusDays }} hari surplus</span>
                        </span>
                        @if ($deficitDays > 0)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-[#FCEDEA] text-[#E87559] font-bold text-[11px]">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#E87559]"></span>
                                <span>{{ $deficitDays }} hari belanja stok</span>
                            </span>
                        @endif
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-[#EEEEEE] text-[#777777] font-semibold text-[11px]">
                            <span>{{ $totalDays }} hari total ({{ $periodLabel }})</span>
                        </span>
                    </div>

                    <!-- Petunjuk Interaksi & Edukasi Non-Laba -->
                    <div class="flex items-center gap-1 text-[11px] text-[#777777]">
                        <span class="sm:hidden text-[#E87559] font-semibold">Geser grafik ke samping untuk melihat seluruh tanggal &rarr;</span>
                        <span class="hidden sm:inline italic">Klik kolom diagram untuk mengganti rincian harian</span>
                    </div>

                </div>

            </div>

        </div>
    @endif

    <!-- Middle Quick Navigation Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <!-- Quick Feature 1: Transaksi -->
        <div class="p-6 rounded-3xl bg-white border border-[#EEEEEE] shadow-sm flex flex-col justify-between">
            <div>
                <div class="w-10 h-10 rounded-xl bg-[#FCEDEA] text-[#E87559] flex items-center justify-center font-bold mb-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-[#252525]">Kelola Transaksi</h3>
                <p class="text-xs text-[#777777] mt-1.5 leading-relaxed">
                    Catat pemasukan dan pengeluaran harian via scan struk OCR, perintah suara, impor laporan digital, atau kasir POS.
                </p>
            </div>
            <div class="mt-6 pt-4 border-t border-gray-100 flex items-center justify-between">
                <a href="{{ route('transactions.index') }}" wire:navigate class="text-xs font-bold text-[#E87559] hover:underline flex items-center gap-1">
                    <span>Buka Riwayat Kas</span>
                    <span>→</span>
                </a>
                <a href="{{ route('transactions.expense') }}" wire:navigate class="text-xs font-semibold text-[#777777] hover:text-[#252525]">
                    + Pengeluaran
                </a>
            </div>
        </div>

        <!-- Quick Feature 2: Restock -->
        <div class="p-6 rounded-3xl bg-white border border-[#EEEEEE] shadow-sm flex flex-col justify-between">
            <div>
                <div class="w-10 h-10 rounded-xl bg-[#DDF1EC] text-[#43A58A] flex items-center justify-center font-bold mb-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-[#252525]">Saran Restock Bahan</h3>
                <p class="text-xs text-[#777777] mt-1.5 leading-relaxed">
                    Prediksi kebutuhan bahan baku F&B berdasarkan histori penjualan mingguan untuk mencegah kehabisan stok.
                </p>
            </div>
            <div class="mt-6 pt-4 border-t border-gray-100 flex items-center justify-between">
                <a href="{{ route('restock') }}" wire:navigate class="text-xs font-bold text-[#E87559] hover:underline flex items-center gap-1">
                    <span>Lihat Saran Bahan</span>
                    <span>→</span>
                </a>
                <span class="text-[11px] font-semibold text-[#E87559] bg-[#FCEDEA] px-2 py-0.5 rounded-full">
                    Persediaan Aman
                </span>
            </div>
        </div>

        <!-- Quick Feature 3: Profil Usaha -->
        <div class="p-6 rounded-3xl bg-white border border-[#EEEEEE] shadow-sm flex flex-col justify-between">
            <div>
                <div class="w-10 h-10 rounded-xl bg-[#E1F0F7] text-[#252525] flex items-center justify-center font-bold mb-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-[#252525]">Profil Usaha & Ekspor</h3>
                <p class="text-xs text-[#777777] mt-1.5 leading-relaxed">
                    Data identitas bisnis, performa omzet, dan unduh laporan keuangan terformat PDF untuk pengajuan pendanaan.
                </p>
            </div>
            <div class="mt-6 pt-4 border-t border-gray-100 flex items-center justify-between">
                <a href="{{ route('business-profile') }}" wire:navigate class="text-xs font-bold text-[#E87559] hover:underline flex items-center gap-1">
                    <span>Kelola Profil</span>
                    <span>→</span>
                </a>
                <span class="text-[11px] font-semibold text-[#43A58A] bg-[#DDF1EC] px-2 py-0.5 rounded-full">
                    Profil Aktif
                </span>
            </div>
        </div>

    </div>

</div>
