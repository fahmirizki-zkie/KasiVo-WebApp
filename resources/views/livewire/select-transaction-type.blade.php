<div class="w-full flex-1 flex flex-col justify-between space-y-4">

        <!-- Header Halaman -->
        <div class="text-center mb-8 sm:mb-10">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-[#FCEDEA] text-[#E87559] border border-[#E87559]/20 mb-3 shadow-xs">
                <svg class="w-3.5 h-3.5 text-[#E87559]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                <span>Pencatatan Cepat KasiVo</span>
            </span>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#252525] tracking-tight">
                Mau Catat Apa Hari Ini?
            </h1>
            <p class="mt-2 text-xs sm:text-sm text-[#777777] max-w-md mx-auto leading-relaxed">
                Pilih salah satu jalur pencatatan di bawah untuk memperbarui arus kas usahamu secara instan.
            </p>
        </div>

        <!-- Pilihan Dua Jalur (Dual Path Cards) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 sm:gap-6">

            <!-- JALUR 1: CATAT PENGELUARAN (KAS KELUAR) -->
            <div class="bg-white rounded-2xl p-6 sm:p-8 border border-[#EEEEEE] hover:border-[#E87559]/50 hover:shadow-md transition-all duration-200 flex flex-col justify-between">
                <div>
                    <!-- Icon & Badge -->
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-13 h-13 sm:w-14 sm:h-14 rounded-2xl bg-[#FCEDEA] text-[#E87559] border border-[#E87559]/20 flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                        </div>
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-[#FCEDEA] text-[#E87559] border border-[#E87559]/20">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                            </svg>
                            <span>Kas Keluar</span>
                        </span>
                    </div>

                    <!-- Judul & Keterangan -->
                    <h2 class="text-lg sm:text-xl font-bold text-[#252525]">
                        Catat Pengeluaran
                    </h2>
                    <p class="mt-2 text-xs sm:text-sm text-[#777777] leading-relaxed">
                        Pencatatan belanja bahan dapur, stok dagangan, tagihan listrik, operasional, atau gaji karyawan.
                    </p>

                    <!-- Pilihan 3 Moda Input Pengeluaran -->
                    <div class="mt-5 pt-4 border-t border-[#EEEEEE] space-y-2.5">
                        <p class="text-[11px] font-bold text-[#777777] uppercase tracking-wider">Pilih Cara Catat:</p>
                        
                        <div class="space-y-2">
                            <!-- 1. Foto Nota (Rekomendasi Utama) -->
                            <a href="{{ route('transactions.photo') }}" 
                               wire:navigate 
                               class="group/opt p-3 rounded-xl border border-[#EEEEEE] hover:border-[#E87559] hover:bg-[#FCEDEA]/30 bg-[#FBFBFC] flex items-center justify-between gap-3 transition-all">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-lg bg-[#FCEDEA] text-[#E87559] flex items-center justify-center shrink-0 group-hover/opt:scale-105 transition-transform">
                                        <svg class="w-4.5 h-4.5 text-[#E87559]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="text-xs font-bold text-[#252525] group-hover/opt:text-[#E87559] transition-colors flex items-center gap-1.5">
                                            <span>Foto Nota / Struk</span>
                                            <span class="text-[9px] font-semibold px-1.5 py-0.2 rounded bg-amber-100 text-amber-700">Rekomendasi</span>
                                        </div>
                                        <div class="text-[11px] text-[#777777]">AI memindai struk & rincian belanjaan</div>
                                    </div>
                                </div>
                                <svg class="w-4 h-4 text-[#777777] group-hover/opt:text-[#E87559] group-hover/opt:translate-x-0.5 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>

                            <!-- 2. Pesan Suara -->
                            <a href="{{ route('transactions.expense') }}" 
                               wire:navigate 
                               class="group/opt p-3 rounded-xl border border-[#EEEEEE] hover:border-[#E87559] hover:bg-[#FCEDEA]/30 bg-[#FBFBFC] flex items-center justify-between gap-3 transition-all">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-lg bg-gray-100 group-hover/opt:bg-[#FCEDEA] text-[#777777] group-hover/opt:text-[#E87559] flex items-center justify-center shrink-0 transition-colors">
                                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="text-xs font-bold text-[#252525] group-hover/opt:text-[#E87559] transition-colors">
                                            Catat via Suara
                                        </div>
                                        <div class="text-[11px] text-[#777777]">Sebutkan belanja langsung dari mikrofon</div>
                                    </div>
                                </div>
                                <svg class="w-4 h-4 text-[#777777] group-hover/opt:text-[#E87559] group-hover/opt:translate-x-0.5 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>

                            <!-- 3. Ketik Teks -->
                            <a href="{{ route('transactions.text') }}" 
                               wire:navigate 
                               class="group/opt p-3 rounded-xl border border-[#EEEEEE] hover:border-[#E87559] hover:bg-[#FCEDEA]/30 bg-[#FBFBFC] flex items-center justify-between gap-3 transition-all">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-lg bg-gray-100 group-hover/opt:bg-[#FCEDEA] text-[#777777] group-hover/opt:text-[#E87559] flex items-center justify-center shrink-0 transition-colors">
                                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="text-xs font-bold text-[#252525] group-hover/opt:text-[#E87559] transition-colors">
                                            Catat via Teks
                                        </div>
                                        <div class="text-[11px] text-[#777777]">Tulis catatan santai dengan bahasa sehari-hari</div>
                                    </div>
                                </div>
                                <svg class="w-4 h-4 text-[#777777] group-hover/opt:text-[#E87559] group-hover/opt:translate-x-0.5 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- CTA Action Button -->
                <div class="mt-6 pt-4 border-t border-[#EEEEEE] flex items-center justify-between text-xs sm:text-sm font-semibold text-[#E87559]">
                    <a href="{{ route('transactions.photo') }}" wire:navigate class="hover:underline flex items-center gap-1.5">
                        <span>Mulai Catat Pengeluaran</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </a>
                </div>
            </div>

            <!-- JALUR 2: CATAT PENJUALAN (KAS MASUK) -->
            <a
                href="{{ route('transactions.income') }}"
                wire:navigate
                class="group relative bg-white rounded-2xl p-6 sm:p-8 border border-[#EEEEEE] hover:border-emerald-500 hover:shadow-md transition-all duration-200 flex flex-col justify-between"
            >
                <div>
                    <!-- Icon & Badge -->
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-13 h-13 sm:w-14 sm:h-14 rounded-2xl bg-[#DDF1EC] text-emerald-700 border border-emerald-200/50 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                            <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                            </svg>
                        </div>
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-[#DDF1EC] text-emerald-700 border border-emerald-200/50">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                            </svg>
                            <span>Kas Masuk</span>
                        </span>
                    </div>

                    <!-- Judul & Keterangan -->
                    <h2 class="text-lg sm:text-xl font-bold text-[#252525] group-hover:text-emerald-600 transition-colors">
                        Catat Penjualan
                    </h2>
                    <p class="mt-2 text-xs sm:text-sm text-[#777777] leading-relaxed">
                        Pencatatan total omzet uang masuk hasil penjualan harian warung, baik tunai maupun QRIS.
                    </p>

                    <!-- Pilihan Metode Input -->
                    <div class="mt-5 pt-4 border-t border-[#EEEEEE] space-y-2">
                        <p class="text-[11px] font-semibold text-[#777777] uppercase tracking-wider">Mendukung Jalur:</p>
                        <div class="flex flex-wrap gap-2 text-xs">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-[#FBFBFC] border border-[#EEEEEE] text-[#252525] font-medium">
                                <svg class="w-3.5 h-3.5 text-[#777777]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <span>Rekap Omzet Harian</span>
                            </span>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-[#FBFBFC] border border-[#EEEEEE] text-[#252525] font-medium">
                                <svg class="w-3.5 h-3.5 text-[#777777]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                                </svg>
                                <span>Pesan Suara</span>
                            </span>
                            <a href="{{ route('transactions.text') }}" wire:navigate class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-[#FBFBFC] hover:bg-[#FCEDEA] border border-[#EEEEEE] hover:border-[#E87559]/30 text-[#252525] hover:text-[#E87559] font-medium transition-colors">
                                <svg class="w-3.5 h-3.5 text-[#777777]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                </svg>
                                <span>Ketik Teks</span>
                            </a>
                            <a href="{{ route('transactions.import-digital') }}" wire:navigate class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-[#FBFBFC] hover:bg-[#DDF1EC] border border-[#EEEEEE] hover:border-emerald-500/30 text-[#252525] hover:text-emerald-700 font-medium transition-colors">
                                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                                <span>Impor Digital</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- CTA Action Button -->
                <div class="mt-6 pt-4 border-t border-[#EEEEEE] flex items-center justify-between text-xs sm:text-sm font-semibold text-emerald-600">
                    <span>Mulai Catat Penjualan</span>
                    <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                </div>
            </a>

        </div>

        <!-- Link Kontekstual ke Riwayat Transaksi -->
        <div class="mt-8 sm:mt-10 text-center">
            <a
                href="{{ route('transactions.index') }}"
                wire:navigate
                class="inline-flex items-center gap-1.5 text-xs font-medium text-[#777777] hover:text-[#E87559] transition-colors"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z"/>
                </svg>
                <span>Lihat Riwayat Transaksi Tersimpan</span>
            </a>
        </div>

</div>
