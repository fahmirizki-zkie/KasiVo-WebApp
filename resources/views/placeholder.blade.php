<x-app-layout>
    <div class="py-4 sm:py-6">
        <!-- Main Card Surface -->
        <div class="bg-white rounded-3xl border border-[#EEEEEE] shadow-sm p-6 sm:p-12 text-center max-w-2xl mx-auto">
            
            <!-- Feature Icon Badge -->
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-[#FCEDEA] text-[#E87559] mb-5 shadow-sm">
                @if (request()->routeIs('transactions.*'))
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z"/>
                    </svg>
                @elseif (request()->routeIs('restock'))
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                @elseif (request()->routeIs('business-profile'))
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                @else
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                @endif
            </div>

            <!-- Page Title -->
            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#252525] tracking-tight">
                {{ $title ?? 'Halaman KasiVo' }}
            </h1>

            <!-- Page Description -->
            <p class="mt-3 text-sm sm:text-base text-[#777777] max-w-md mx-auto leading-relaxed">
                {{ $description ?? 'Halaman fitur ini telah terhubung dengan sistem navigasi responsif KasiVo.' }}
            </p>

            <!-- Status Pill: Modul Dalam Pengembangan -->
            <div class="mt-6 inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[#FCEDEA] text-[#E87559] text-xs font-semibold">
                <span class="w-2 h-2 rounded-full bg-[#E87559] animate-pulse"></span>
                <span>Modul Dalam Tahap Pengembangan Aktif</span>
            </div>

            <!-- Contextual Flow Action: Only for Sub-features (e.g. Catat Transaksi -> Kembali ke Riwayat Transaksi) -->
            @if (request()->routeIs('transactions.*'))
                <div class="mt-8 pt-6 border-t border-[#EEEEEE] flex justify-center">
                    <a href="{{ route('transactions.index') }}" 
                       wire:navigate
                       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-white hover:bg-gray-50 text-[#252525] text-xs font-semibold border border-[#EEEEEE] hover:border-[#E87559]/40 shadow-sm transition-all group">
                        <svg class="w-4 h-4 text-[#777777] group-hover:text-[#E87559] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        <span>Kembali ke Riwayat Transaksi</span>
                    </a>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
