@props([
    'currentMode' => 'voice', // 'voice' | 'photo' | 'text'
])

@php
    $modes = [
        'photo' => [
            'id' => 'photo',
            'label' => 'Catat via Foto Nota',
            'short_label' => 'Foto Nota',
            'description' => 'Foto atau unggah nota belanja agar AI dapat membaca rincian transaksi.',
            'route' => route('transactions.photo'),
            'icon' => 'camera',
        ],
        'voice' => [
            'id' => 'voice',
            'label' => 'Catat via Suara',
            'short_label' => 'Suara',
            'description' => 'Rekam suara atau unggah audio untuk mencatat pengeluaran.',
            'route' => route('transactions.expense'),
            'icon' => 'microphone',
        ],
        'text' => [
            'id' => 'text',
            'label' => 'Catat via Teks',
            'short_label' => 'Teks',
            'description' => 'Tulis catatan belanja dengan bahasa sehari-hari.',
            'route' => route('transactions.text'),
            'icon' => 'file-text',
        ],
    ];
@endphp

<div x-data="{ modeModalOpen: false }" class="relative inline-block">

    <!-- Tombol Pemicu "Ganti Jalur" -->
    <button type="button" 
            @click="modeModalOpen = true"
            aria-haspopup="dialog"
            :aria-expanded="modeModalOpen"
            class="text-xs text-[#777777] hover:text-[#E87559] flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl border border-[#EEEEEE] hover:border-[#E87559]/30 hover:bg-[#FCEDEA]/40 transition-all font-semibold shadow-2xs group">
        <svg class="w-3.5 h-3.5 text-[#E87559] group-hover:rotate-180 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
        </svg>
        <span>Ganti Jalur</span>
        <svg class="w-3 h-3 text-[#999999] group-hover:text-[#E87559] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    <!-- Modal Dialog / Bottom Sheet Pemilihan Moda Pencatatan -->
    <div x-show="modeModalOpen" 
         class="fixed inset-0 z-50 overflow-y-auto"
         role="dialog"
         aria-modal="true"
         style="display: none;"
         @keydown.escape.window="modeModalOpen = false">

        <!-- Backdrop Overlay -->
        <div x-show="modeModalOpen"
             x-transition:enter="transition-opacity ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="modeModalOpen = false"
             class="fixed inset-0 bg-black/50 backdrop-blur-xs"></div>

        <!-- Panel Kontainer: Bottom sheet di mobile (< sm), Centered dialog di desktop (sm+) -->
        <div class="fixed inset-0 flex items-end sm:items-center justify-center p-0 sm:p-4 pointer-events-none">
            <div x-show="modeModalOpen"
                 x-transition:enter="transition ease-out duration-300 transform"
                 x-transition:enter-start="translate-y-full sm:translate-y-0 sm:scale-95 sm:opacity-0"
                 x-transition:enter-end="translate-y-0 sm:scale-100 sm:opacity-100"
                 x-transition:leave="transition ease-in duration-200 transform"
                 x-transition:leave-start="translate-y-0 sm:scale-100 sm:opacity-100"
                 x-transition:leave-end="translate-y-full sm:translate-y-0 sm:scale-95 sm:opacity-0"
                 class="w-full sm:max-w-lg bg-white rounded-t-3xl sm:rounded-3xl shadow-2xl border border-[#EEEEEE] overflow-hidden pointer-events-auto max-h-[90vh] flex flex-col">

                <!-- Header Modal -->
                <div class="p-5 sm:p-6 pb-4 border-b border-[#EEEEEE] flex items-center justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-[#E87559]"></span>
                            <h3 class="text-base sm:text-lg font-extrabold text-[#252525]">
                                Pilih Moda Pencatatan
                            </h3>
                        </div>
                        <p class="text-xs text-[#777777] mt-1">
                            Pilih metode yang paling nyaman untuk mencatat pengeluaran usahamu saat ini.
                        </p>
                    </div>

                    <button type="button" 
                            @click="modeModalOpen = false"
                            class="p-2 rounded-xl text-[#777777] hover:text-[#252525] hover:bg-gray-100 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- List 3 Moda Pencatatan -->
                <div class="p-5 sm:p-6 space-y-3 overflow-y-auto">
                    @foreach($modes as $key => $mode)
                        @if($currentMode === $key)
                            <!-- Moda yang Sedang Aktif (Non-navigating, penanda visual jelas) -->
                            <div class="p-4 rounded-2xl border-2 border-[#E87559] bg-[#FCEDEA]/50 flex items-start justify-between gap-3 shadow-xs">
                                <div class="flex items-start gap-3.5">
                                    <div class="w-11 h-11 rounded-xl bg-[#E87559] text-white flex items-center justify-center shrink-0 shadow-xs">
                                        @if($mode['icon'] === 'camera')
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            </svg>
                                        @elseif($mode['icon'] === 'microphone')
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                                            </svg>
                                        @else
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                        @endif
                                    </div>
                                    <div class="space-y-0.5">
                                        <div class="flex items-center gap-2">
                                            <h4 class="text-sm font-bold text-[#252525]">{{ $mode['label'] }}</h4>
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#E87559] text-white">
                                                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                <span>Sedang Aktif</span>
                                            </span>
                                        </div>
                                        <p class="text-xs text-[#555555] leading-relaxed">{{ $mode['description'] }}</p>
                                    </div>
                                </div>
                            </div>
                        @else
                            <!-- Moda Lainnya (Bisa Diklik & Navigasi Langsung) -->
                            <a href="{{ $mode['route'] }}" 
                               wire:navigate
                               class="p-4 rounded-2xl border border-[#EEEEEE] hover:border-[#E87559] hover:bg-[#FCEDEA]/20 bg-white flex items-start justify-between gap-3 transition-all group shadow-2xs hover:shadow-xs">
                                <div class="flex items-start gap-3.5">
                                    <div class="w-11 h-11 rounded-xl bg-gray-100 text-[#777777] group-hover:bg-[#FCEDEA] group-hover:text-[#E87559] flex items-center justify-center shrink-0 transition-colors">
                                        @if($mode['icon'] === 'camera')
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            </svg>
                                        @elseif($mode['icon'] === 'microphone')
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                                            </svg>
                                        @else
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                        @endif
                                    </div>
                                    <div class="space-y-0.5">
                                        <h4 class="text-sm font-bold text-[#252525] group-hover:text-[#E87559] transition-colors">
                                            {{ $mode['label'] }}
                                        </h4>
                                        <p class="text-xs text-[#777777] leading-relaxed">
                                            {{ $mode['description'] }}
                                        </p>
                                    </div>
                                </div>
                                <div class="w-7 h-7 rounded-lg bg-gray-50 group-hover:bg-[#E87559] group-hover:text-white text-[#777777] flex items-center justify-center shrink-0 transition-colors mt-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </div>
                            </a>
                        @endif
                    @endforeach
                </div>

                <!-- Footer Modal: Beralih ke Catat Penjualan -->
                <div class="p-4 sm:p-5 bg-gray-50 border-t border-[#EEEEEE] flex items-center justify-between text-xs">
                    <a href="{{ route('transactions.income') }}"
                       wire:navigate
                       class="text-[#777777] hover:text-[#43A58A] font-medium flex items-center gap-1.5 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        <span>Beralih ke Catat Penjualan</span>
                    </a>

                    <button type="button" 
                            @click="modeModalOpen = false"
                            class="px-3.5 py-1.5 rounded-xl bg-white border border-[#EEEEEE] text-[#555555] font-semibold hover:bg-gray-100 transition-colors shadow-2xs">
                        Tutup
                    </button>
                </div>

            </div>
        </div>
    </div>

</div>

