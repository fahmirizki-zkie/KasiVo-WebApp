<div class="py-10">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Header Halaman -->
        <div class="text-center mb-8">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 mb-2">
                ⚡ Jalur Cepat KasiVo
            </span>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-white">
                Mau Catat Apa Hari Ini?
            </h1>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 max-w-md mx-auto">
                Pilih salah satu jalur pencatatan di bawah untuk memperbarui arus kas usahamu.
            </p>
        </div>

        <!-- Pilihan Dua Jalur (Dual Path Cards) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <!-- JALUR 1: CATAT PENGELUARAN (KAS KELUAR) -->
            <a
                href="{{ route('transactions.expense') }}"
                class="group relative bg-white dark:bg-gray-800 rounded-2xl p-6 sm:p-8 border-2 border-gray-100 dark:border-gray-700 hover:border-rose-500 dark:hover:border-rose-500 hover:shadow-xl transition-all duration-200 flex flex-col justify-between"
            >
                <div>
                    <!-- Icon & Badge -->
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-14 h-14 rounded-2xl bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">
                            🛒
                        </div>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-100 dark:bg-rose-900/50 text-rose-700 dark:text-rose-300">
                            - Kas Keluar
                        </span>
                    </div>

                    <!-- Judul & Keterangan -->
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white group-hover:text-rose-600 dark:group-hover:text-rose-400 transition-colors">
                        Catat Pengeluaran
                    </h2>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                        Pencatatan belanja bahan dapur, stok dagangan, tabung gas, listrik, atau gaji karyawan.
                    </p>

                    <!-- Pilihan Metode Input -->
                    <div class="mt-5 pt-4 border-t border-gray-100 dark:border-gray-700 space-y-2">
                        <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Mendukung:</p>
                        <div class="flex flex-wrap gap-2 text-xs">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-gray-50 dark:bg-gray-900/60 text-gray-700 dark:text-gray-300 font-medium">
                                📸 Foto Nota / Struk
                            </span>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-gray-50 dark:bg-gray-900/60 text-gray-700 dark:text-gray-300 font-medium">
                                🎙️ Pesan Suara
                            </span>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-gray-50 dark:bg-gray-900/60 text-gray-700 dark:text-gray-300 font-medium">
                                ✍️ Ketik Teks
                            </span>
                        </div>
                    </div>
                </div>

                <!-- CTA Action Button -->
                <div class="mt-6 pt-4 flex items-center text-sm font-semibold text-rose-600 dark:text-rose-400 group-hover:translate-x-1 transition-transform">
                    <span>Mulai Catat Pengeluaran</span>
                    <svg class="w-4 h-4 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                    </svg>
                </div>
            </a>

            <!-- JALUR 2: CATAT PENJUALAN (KAS MASUK) -->
            <a
                href="{{ route('transactions.income') }}"
                class="group relative bg-white dark:bg-gray-800 rounded-2xl p-6 sm:p-8 border-2 border-gray-100 dark:border-gray-700 hover:border-emerald-500 dark:hover:border-emerald-500 hover:shadow-xl transition-all duration-200 flex flex-col justify-between"
            >
                <div>
                    <!-- Icon & Badge -->
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-14 h-14 rounded-2xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">
                            💰
                        </div>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300">
                            + Kas Masuk
                        </span>
                    </div>

                    <!-- Judul & Keterangan -->
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">
                        Catat Penjualan
                    </h2>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                        Pencatatan total omzet uang masuk hasil penjualan harian warung, baik tunai maupun QRIS.
                    </p>

                    <!-- Pilihan Metode Input -->
                    <div class="mt-5 pt-4 border-t border-gray-100 dark:border-gray-700 space-y-2">
                        <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Mendukung:</p>
                        <div class="flex flex-wrap gap-2 text-xs">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-gray-50 dark:bg-gray-900/60 text-gray-700 dark:text-gray-300 font-medium">
                                💵 Rekap Omzet Harian
                            </span>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-gray-50 dark:bg-gray-900/60 text-gray-700 dark:text-gray-300 font-medium">
                                🎙️ Pesan Suara
                            </span>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-gray-50 dark:bg-gray-900/60 text-gray-700 dark:text-gray-300 font-medium">
                                ✍️ Ketik Teks
                            </span>
                        </div>
                    </div>
                </div>

                <!-- CTA Action Button -->
                <div class="mt-6 pt-4 flex items-center text-sm font-semibold text-emerald-600 dark:text-emerald-400 group-hover:translate-x-1 transition-transform">
                    <span>Mulai Catat Penjualan</span>
                    <svg class="w-4 h-4 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                    </svg>
                </div>
            </a>

        </div>

        <!-- Tombol Kembali / Navigasi Cepat -->
        <div class="mt-8 text-center">
            <a
                href="{{ route('transactions.index') }}"
                class="inline-flex items-center text-xs font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition"
            >
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Lihat Riwayat Transaksi Tersimpan
            </a>
        </div>

    </div>
</div>

