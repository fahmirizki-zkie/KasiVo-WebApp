<x-app-layout>
    <div class="py-4 sm:py-6">
        <!-- Main Card Surface -->
        <div class="bg-white rounded-3xl border border-[#EEEEEE] shadow-sm p-6 sm:p-10 text-center max-w-3xl mx-auto">
            
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
            <h2 class="text-2xl sm:text-3xl font-extrabold text-[#252525] tracking-tight">
                {{ $title ?? 'Halaman KasiVo' }}
            </h2>

            <!-- Page Description -->
            <p class="mt-3 text-sm sm:text-base text-[#777777] max-w-lg mx-auto leading-relaxed">
                {{ $description ?? 'Halaman fitur ini telah terhubung dengan sistem navigasi responsif KasiVo KV-008.' }}
            </p>

            <!-- Quick Navigation Testing Grid -->
            <div class="mt-8 pt-8 border-t border-[#EEEEEE]">
                <div class="text-xs font-bold text-[#999999] uppercase tracking-wider mb-4">
                    Pintasan Navigasi Lainnya
                </div>
                
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 max-w-xl mx-auto text-left">
                    <a href="{{ route('dashboard') }}" wire:navigate class="p-3 rounded-2xl border border-[#EEEEEE] hover:border-[#E87559]/40 hover:bg-[#FCEDEA]/20 transition-all group">
                        <div class="text-xs font-bold text-[#252525] group-hover:text-[#E87559]">Dashboard</div>
                        <div class="text-[10px] text-[#777777]">Overview omzet</div>
                    </a>
                    
                    <a href="{{ route('transactions.index') }}" wire:navigate class="p-3 rounded-2xl border border-[#EEEEEE] hover:border-[#E87559]/40 hover:bg-[#FCEDEA]/20 transition-all group">
                        <div class="text-xs font-bold text-[#252525] group-hover:text-[#E87559]">Transaksi</div>
                        <div class="text-[10px] text-[#777777]">Kas masuk & keluar</div>
                    </a>

                    <a href="{{ route('restock') }}" wire:navigate class="p-3 rounded-2xl border border-[#EEEEEE] hover:border-[#E87559]/40 hover:bg-[#FCEDEA]/20 transition-all group">
                        <div class="text-xs font-bold text-[#252525] group-hover:text-[#E87559]">Restock</div>
                        <div class="text-[10px] text-[#777777]">Saran bahan</div>
                    </a>

                    <a href="{{ route('business-profile') }}" wire:navigate class="p-3 rounded-2xl border border-[#EEEEEE] hover:border-[#E87559]/40 hover:bg-[#FCEDEA]/20 transition-all group">
                        <div class="text-xs font-bold text-[#252525] group-hover:text-[#E87559]">Profil Usaha</div>
                        <div class="text-[10px] text-[#777777]">Ekspor PDF</div>
                    </a>
                </div>
            </div>

            <!-- Back to Dashboard CTA -->
            <div class="mt-8 flex justify-center">
                <a href="{{ route('dashboard') }}" 
                   wire:navigate
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-[#252525] text-xs font-semibold transition-all">
                    <svg class="w-4 h-4 text-[#777777]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span>Kembali ke Dashboard</span>
                </a>
            </div>

        </div>
    </div>
</x-app-layout>
