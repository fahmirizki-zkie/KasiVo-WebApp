@if($showDuplicateModal && $duplicateInfo)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm transition-opacity"
         role="dialog" 
         aria-modal="true" 
         aria-labelledby="duplicate-modal-title">
        <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl border border-amber-200 overflow-hidden transform transition-all">
            
            {{-- Header Peringatan --}}
            <div class="bg-gradient-to-r from-amber-500 to-orange-500 px-6 py-4 text-white">
                <div class="flex items-center space-x-3">
                    <div class="flex-shrink-0 w-10 h-10 rounded-full bg-white/20 flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold" id="duplicate-modal-title">
                            Tunggu Sebentar, Cek Dulu!
                        </h3>
                        <p class="text-xs text-amber-100">
                            Terdeteksi transaksi dengan nominal & kategori identik
                        </p>
                    </div>
                </div>
            </div>

            {{-- Isi Modal --}}
            <div class="p-6 space-y-4">
                {{-- Kalimat Pemantik Kesadaran --}}
                <div class="text-sm text-gray-700 leading-relaxed bg-amber-50 p-3.5 rounded-xl border border-amber-100">
                    <p class="font-medium text-amber-900 mb-1">
                        Apakah kamu memang keluar uang 2 kali, atau tidak sengaja mencatat ulang?
                    </p>
                    <p class="text-xs text-amber-800">
                        Kami menemukan transaksi dengan nominal serupa baru saja kamu catat <strong>{{ $duplicateInfo['time_ago'] ?? 'beberapa menit lalu' }}</strong>.
                    </p>
                </div>

                {{-- Kotak Perbandingan --}}
                <div class="grid grid-cols-2 gap-3 text-xs">
                    <div class="bg-gray-50 border border-gray-200 rounded-xl p-3">
                        <div class="text-gray-500 font-medium mb-1 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2"/>
                            </svg>
                            <span>Transaksi Sebelumnya</span>
                        </div>
                        <div class="text-base font-bold text-gray-900">{{ $duplicateInfo['formatted_amount'] }}</div>
                        <div class="text-gray-600 mt-0.5 capitalize">{{ str_replace('_', ' ', $duplicateInfo['category'] ?? '') }}</div>
                        <div class="text-[11px] text-gray-400 mt-1 truncate">{{ $duplicateInfo['time_ago'] }}</div>
                    </div>
                    <div class="bg-orange-50/70 border border-orange-200 rounded-xl p-3">
                        <div class="text-orange-700 font-medium mb-1 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            <span>Mau Dicatat Sekarang</span>
                        </div>
                        <div class="text-base font-bold text-orange-900">Rp {{ number_format((float) ($draft['amount'] ?? 0), 0, ',', '.') }}</div>
                        <div class="text-orange-700 mt-0.5 capitalize">{{ str_replace('_', ' ', $draft['category'] ?? '') }}</div>
                        <div class="text-[11px] text-orange-600 mt-1">Draf Baru</div>
                    </div>
                </div>

                {{-- Pilihan Tombol Keputusan --}}
                <div class="pt-2 space-y-2.5">
                    {{-- Opsi 1: Batal Simpan (Lupa / Salah Dobel) --}}
                    <button type="button" 
                            wire:click="dismissDuplicateModal" 
                            class="w-full flex flex-col items-center justify-center px-4 py-3 bg-white border-2 border-red-300 hover:bg-red-50 hover:border-red-400 text-red-700 rounded-xl font-medium transition shadow-xs">
                        <span class="text-sm font-bold flex items-center space-x-1.5">
                            <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            <span>Batalkan, Tadi Saya Lupa Sudah Catat</span>
                        </span>
                        <span class="text-[11px] text-red-500 font-normal mt-0.5">
                            (Transaksi ini tidak akan disimpan, saldo kas aman)
                        </span>
                    </button>

                    {{-- Opsi 2: Tetap Simpan (Benar Transaksi Baru) --}}
                    <button type="button" 
                            wire:click="confirmDuplicateAndSave" 
                            class="w-full flex flex-col items-center justify-center px-4 py-3 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white rounded-xl font-medium transition shadow-xs">
                        <span class="text-sm font-bold flex items-center space-x-1.5">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>Ya, Ini Beneran Pengeluaran Baru</span>
                        </span>
                        <span class="text-[11px] text-emerald-100 font-normal mt-0.5">
                            (Uang memang keluar 2 kali di warung saya)
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif

