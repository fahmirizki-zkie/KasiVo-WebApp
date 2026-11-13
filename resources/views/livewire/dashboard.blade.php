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
            <a href="{{ route('transactions.import-digital') }}" 
               wire:navigate
               class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-white hover:bg-gray-50 text-[#252525] text-xs font-semibold border border-[#EEEEEE] shadow-sm transition-all">
                <svg class="w-4 h-4 text-[#777777]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                <span>Impor Digital</span>
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
