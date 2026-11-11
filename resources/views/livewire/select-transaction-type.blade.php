<div class="py-6 sm:py-10">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

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
            <a
                href="{{ route('transactions.expense') }}"
                wire:navigate
                class="group relative bg-white rounded-2xl p-6 sm:p-8 border border-[#EEEEEE] hover:border-[#E87559] hover:shadow-md transition-all duration-200 flex flex-col justify-between"
            >
                <div>
                    <!-- Icon & Badge -->
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-13 h-13 sm:w-14 sm:h-14 rounded-2xl bg-[#FCEDEA] text-[#E87559] border border-[#E87559]/20 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                            <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                        </div>
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-[#FCEDEA] text-[#E87559] border border-[#E87559]/20">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                            </svg>
                            <span>Kas Keluar</span>
                        </span>
                    </div>

                    <!-- Judul & Keterangan -->
                    <h2 class="text-lg sm:text-xl font-bold text-[#252525] group-hover:text-[#E87559] transition-colors">
                        Catat Pengeluaran
                    </h2>
                    <p class="mt-2 text-xs sm:text-sm text-[#777777] leading-relaxed">
                        Pencatatan belanja bahan dapur, stok dagangan, tabung gas, listrik, atau gaji karyawan.
                    </p>

                    <!-- Pilihan Metode Input -->
                    <div class="mt-5 pt-4 border-t border-[#EEEEEE] space-y-2">
                        <p class="text-[11px] font-semibold text-[#777777] uppercase tracking-wider">Mendukung Jalur:</p>
                        <div class="flex flex-wrap gap-2 text-xs">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-[#FBFBFC] border border-[#EEEEEE] text-[#252525] font-medium">
                                <svg class="w-3.5 h-3.5 text-[#777777]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span>Foto Nota / Struk</span>
                            </span>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-[#FBFBFC] border border-[#EEEEEE] text-[#252525] font-medium">
                                <svg class="w-3.5 h-3.5 text-[#777777]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                                </svg>
                                <span>Pesan Suara</span>
                            </span>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-[#FBFBFC] border border-[#EEEEEE] text-[#252525] font-medium">
                                <svg class="w-3.5 h-3.5 text-[#777777]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                </svg>
                                <span>Ketik Teks</span>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- CTA Action Button -->
                <div class="mt-6 pt-4 border-t border-[#EEEEEE] flex items-center justify-between text-xs sm:text-sm font-semibold text-[#E87559]">
                    <span>Mulai Catat Pengeluaran</span>
                    <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                </div>
            </a>

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
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-[#FBFBFC] border border-[#EEEEEE] text-[#252525] font-medium">
                                <svg class="w-3.5 h-3.5 text-[#777777]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                </svg>
                                <span>Ketik Teks</span>
                            </span>
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
</div>
