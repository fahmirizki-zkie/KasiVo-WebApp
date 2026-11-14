<div class="py-6 sm:py-10">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Tombol Kembali & Breadcrumb Navigasi -->
        <div class="mb-6 flex items-center justify-between">
            <a
                href="{{ route('transactions.create') }}"
                wire:navigate
                class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-medium text-[#777777] hover:text-[#252525] transition-colors"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
                <span>Kembali ke Pilih Jalur</span>
            </a>

            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-[#DDF1EC] text-emerald-700 border border-emerald-200/50">
                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
                <span>Impor Kas Masuk Digital</span>
            </span>
        </div>

        <!-- Header Halaman -->
        <div class="text-center mb-8">
            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#252525] tracking-tight">
                Impor Penjualan Digital
            </h1>
            <p class="mt-2 text-xs sm:text-sm text-[#777777] max-w-lg mx-auto leading-relaxed">
                Catat omzet penjualan dari GoFood, GrabFood, ShopeeFood, atau mutasi QRIS tanpa ketik satu per satu. Cukup unggah screenshot atau berkas CSV/Excel.
            </p>
        </div>

        <!-- ALERT: KUOTA / TOKEN AI PENUH (HTTP 429) -->
        @if ($errorType === 'quota')
            <div class="mb-6 p-4 sm:p-5 rounded-2xl bg-amber-50 border border-amber-300 shadow-xs">
                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-sm font-bold text-amber-900">
                            Kuota AI Anda Sedang Penuh / Token Hari Ini Habis
                        </h3>
                        <p class="text-xs text-amber-800 mt-1 leading-relaxed">
                            Batas pemrosesan otomatis harian dengan kecerdasan buatan telah tercapai. Tenang, berkas yang Anda unggah tetap tersimpan aman dan Anda bisa langsung mengisi total penjualan secara mandiri.
                        </p>

                        <div class="mt-4 flex flex-wrap gap-2.5">
                            <button
                                type="button"
                                wire:click="proceedToEmergencyReview"
                                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold shadow-xs transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                <span>✍️ Lanjut Catat Manual (Tanpa AI)</span>
                            </button>
                            <button
                                type="button"
                                wire:click="parseUploadedFile"
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-amber-400 text-amber-900 hover:bg-amber-100 text-xs font-semibold transition-colors"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                <span>Coba Lagi Nanti</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @elseif ($errorMessage)
            <!-- ALERT: ERROR UMUM LAINNYA -->
            <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 flex items-start gap-3 shadow-xs">
                <div class="w-8 h-8 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-xs sm:text-sm font-bold text-rose-800">Berkas Tidak Dapat Diterima</h3>
                    <p class="text-xs text-rose-700 mt-0.5 leading-relaxed">
                        {{ $errorMessage }}
                    </p>
                </div>
                <button
                    type="button"
                    wire:click="$set('errorMessage', null)"
                    class="text-rose-400 hover:text-rose-600 p-1 rounded-lg"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        @endif

        <!-- Pilihan Platform Asal (Opsional / Filter) - Tampil di tahap Upload & Preview -->
        @if ($step === 'upload' || $step === 'preview_file')
            <div class="mb-6 bg-white rounded-2xl p-4 sm:p-5 border border-[#EEEEEE]">
                <label class="block text-xs font-bold text-[#252525] uppercase tracking-wider mb-2.5">
                    Pilih Platform Penjualan (Opsional)
                </label>
                <div class="flex flex-wrap gap-2 text-xs">
                    @php
                        $platforms = [
                            'auto' => '✨ Deteksi Otomatis',
                            'gofood' => 'GoFood / GoBiz',
                            'grabfood' => 'GrabFood',
                            'shopeefood' => 'ShopeeFood',
                            'qris' => 'QRIS / Settlement Bank',
                            'pos' => 'Kasir Digital (POS)',
                            'other' => 'Lainnya',
                        ];
                    @endphp
                    @foreach ($platforms as $key => $label)
                        <button
                            type="button"
                            wire:click="setPlatform('{{ $key }}')"
                            class="px-3 py-1.5 rounded-xl font-medium transition-all {{ $platform === $key ? 'bg-emerald-600 text-white shadow-xs' : 'bg-[#FBFBFC] hover:bg-[#EEEEEE] text-[#252525] border border-[#EEEEEE]' }}"
                        >
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- TAHAP 1: DROPZONE UNGGAH BERKAS -->
        @if ($step === 'upload')
            <div class="bg-white rounded-2xl p-6 sm:p-8 border-2 border-dashed border-[#DCDCDC] hover:border-emerald-500 transition-colors text-center relative">
                <input
                    type="file"
                    id="digitalSalesFile"
                    wire:model="uploadedFile"
                    accept="image/png,image/jpeg,image/webp,.csv,.xlsx,.xls,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel"
                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                />

                <div wire:loading.remove wire:target="uploadedFile">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-[#DDF1EC] text-emerald-700 border border-emerald-200/50 flex items-center justify-center">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>

                    <h2 class="text-base sm:text-lg font-bold text-[#252525]">
                        Pilih Screenshot atau Berkas Penjualan
                    </h2>
                    <p class="mt-1 text-xs sm:text-sm text-[#777777] max-w-md mx-auto">
                        Klik atau seret berkas ke sini. Mendukung tangkapan layar (<span class="font-semibold text-[#252525]">PNG, JPG, WEBP</span> maks 5 MB) atau spreadsheet (<span class="font-semibold text-[#252525]">CSV, XLSX</span> maks 10 MB).
                    </p>

                    <div class="mt-6 flex flex-wrap justify-center gap-3">
                        <button
                            type="button"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-semibold shadow-xs transition-colors pointer-events-none"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                            </svg>
                            <span>Pilih Berkas dari HP / Laptop</span>
                        </button>
                    </div>
                </div>

                <!-- Indikator Loading Saat File Diunggah -->
                <div wire:loading wire:target="uploadedFile" class="py-6">
                    <div class="inline-block animate-spin rounded-full h-10 w-10 border-4 border-emerald-500 border-t-transparent mb-3"></div>
                    <p class="text-sm font-semibold text-[#252525]">Sedang mengunggah berkas...</p>
                    <p class="text-xs text-[#777777] mt-1">Mohon tunggu sebentar selagi sistem memeriksa format berkas.</p>
                </div>
            </div>

            <!-- Petunjuk Format yang Didukung -->
            <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="p-4 rounded-2xl bg-white border border-[#EEEEEE] flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl bg-[#DDF1EC] text-emerald-700 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xs font-bold text-[#252525]">1. Tangkapan Layar (Screenshot)</h3>
                        <p class="text-[11px] text-[#777777] mt-0.5 leading-relaxed">
                            Buka aplikasi GoBiz / Grab / Shopee Partner di HP, screenshot layar ringkasan omzet hari ini, lalu unggah.
                        </p>
                    </div>
                </div>

                <div class="p-4 rounded-2xl bg-white border border-[#EEEEEE] flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xs font-bold text-[#252525]">2. Berkas Data (CSV / XLSX)</h3>
                        <p class="text-[11px] text-[#777777] mt-0.5 leading-relaxed">
                            Unduh laporan penjualan bulanan atau mutasi rekening QRIS dalam format spreadsheet Excel/CSV.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <!-- TAHAP 2: PRATINJAU BERKAS YANG BERHASIL DITERIMA -->
        @if ($step === 'preview_file' && !empty($fileMeta))
            <div class="bg-white rounded-2xl p-6 sm:p-8 border border-[#EEEEEE] shadow-xs">
                <div class="flex items-center justify-between pb-4 border-b border-[#EEEEEE]">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-emerald-100 text-emerald-700">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </span>
                        <h2 class="text-sm sm:text-base font-bold text-[#252525]">
                            Berkas Valid Berhasil Diterima
                        </h2>
                    </div>

                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $fileType === 'screenshot' ? 'bg-[#DDF1EC] text-emerald-700' : 'bg-blue-50 text-blue-700' }}">
                        {{ $fileType === 'screenshot' ? '📷 Tangkapan Layar' : '📊 Dokumen Spreadsheet' }}
                    </span>
                </div>

                <!-- Tampilan Detail File / Thumbnail -->
                <div class="mt-6 flex flex-col sm:flex-row items-center gap-6">
                    @if ($fileType === 'screenshot' && $uploadedFile)
                        <div class="w-full sm:w-48 h-48 rounded-xl overflow-hidden bg-slate-50 border border-[#EEEEEE] flex items-center justify-center shrink-0">
                            <img
                                src="{{ $uploadedFile->temporaryUrl() }}"
                                alt="Pratinjau Screenshot Penjualan"
                                class="w-full h-full object-contain"
                            />
                        </div>
                    @else
                        <div class="w-full sm:w-48 h-36 rounded-xl bg-blue-50 border border-blue-200 flex flex-col items-center justify-center shrink-0 text-blue-700">
                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span class="mt-2 text-xs font-bold uppercase tracking-wider">{{ $fileMeta['extension'] ?? 'DATA' }}</span>
                        </div>
                    @endif

                    <div class="flex-1 w-full space-y-3">
                        <div>
                            <span class="text-[11px] font-bold text-[#777777] uppercase tracking-wider">Nama Berkas:</span>
                            <p class="text-sm font-bold text-[#252525] truncate">{{ $fileMeta['name'] ?? '-' }}</p>
                        </div>

                        <div class="grid grid-cols-2 gap-3 text-xs">
                            <div class="p-2.5 rounded-xl bg-[#FBFBFC] border border-[#EEEEEE]">
                                <span class="text-[#777777] block text-[11px]">Ukuran:</span>
                                <span class="font-bold text-[#252525]">{{ $fileMeta['size'] ?? '-' }}</span>
                            </div>
                            <div class="p-2.5 rounded-xl bg-[#FBFBFC] border border-[#EEEEEE]">
                                <span class="text-[#777777] block text-[11px]">Platform:</span>
                                <span class="font-bold text-emerald-700 capitalize">{{ $platform === 'auto' ? 'Otomatis' : $platform }}</span>
                            </div>
                        </div>

                        <p class="text-xs text-[#777777] leading-relaxed">
                            Berkas siap diproses. Klik tombol di bawah untuk mengekstrak tanggal, total penjualan, dan rincian transaksi secara otomatis.
                        </p>
                    </div>
                </div>

                <!-- Tombol Aksi: Ganti Berkas & Lanjut Parsing -->
                <div class="mt-6 pt-5 border-t border-[#EEEEEE] flex flex-col sm:flex-row items-center justify-between gap-3">
                    <button
                        type="button"
                        wire:click="removeFile"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl border border-[#EEEEEE] text-xs sm:text-sm font-medium text-[#777777] hover:text-rose-600 hover:border-rose-200 hover:bg-rose-50 transition-colors"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        <span>Ganti / Hapus Berkas</span>
                    </button>

                    <button
                        type="button"
                        wire:click="parseUploadedFile"
                        wire:loading.attr="disabled"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-bold shadow-xs transition-colors"
                    >
                        <span wire:loading.remove wire:target="parseUploadedFile">Lanjut Ekstraksi Data (AI)</span>
                        <span wire:loading wire:target="parseUploadedFile" class="inline-flex items-center gap-2">
                            <span class="animate-spin rounded-full h-4 w-4 border-2 border-white border-t-transparent"></span>
                            <span>Memproses...</span>
                        </span>
                        <svg wire:loading.remove wire:target="parseUploadedFile" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </button>
                </div>
            </div>
        @endif

        <!-- TAHAP 3: INDIKATOR LOADING PARSING -->
        @if ($step === 'parsing')
            <div class="bg-white rounded-2xl p-8 border border-[#EEEEEE] text-center shadow-xs">
                <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-emerald-500 border-t-transparent mb-4"></div>
                <h2 class="text-base sm:text-lg font-bold text-[#252525]">
                    Sedang Mengekstrak Data Penjualan Digital...
                </h2>
                <p class="mt-1 text-xs sm:text-sm text-[#777777] max-w-md mx-auto leading-relaxed">
                    Kecerdasan buatan KasiVo sedang membaca tanggal transaksi dan total nominal omzet dari berkas <span class="font-semibold text-[#252525]">{{ $fileMeta['name'] ?? '' }}</span>.
                </p>
            </div>
        @endif

        <!-- TAHAP 4: PRATINJAU HASIL EKSTRAKSI & KONFIRMASI SIMPAN (DoD KV-037) -->
        @if ($step === 'review' && !empty($parsedData))
            <div class="space-y-6">

                <!-- Header Konfirmasi Hasil Parsing -->
                <div class="bg-white rounded-2xl p-5 sm:p-6 border border-[#EEEEEE] shadow-xs">
                    <div class="flex items-center justify-between pb-4 border-b border-[#EEEEEE]">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-base font-bold text-[#252525]">
                                    Pratinjau Hasil Ekstraksi Penjualan
                                </h2>
                                <p class="text-xs text-[#777777]">
                                    Periksa tanggal dan total penjualan di bawah sebelum disimpan ke kas masuk.
                                </p>
                            </div>
                        </div>

                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-emerald-50 text-emerald-800 border border-emerald-200">
                            {{ $parsedData['platform'] ?? 'other' }}
                        </span>
                    </div>

                    <!-- Highlight Peringatan Review (Jika AI Ragu atau Buram) -->
                    @if (!empty($parsedData['needs_review']))
                        <div class="mt-4 p-3.5 rounded-xl bg-yellow-50 border border-yellow-200 flex items-start gap-2.5 text-xs text-yellow-800">
                            <svg class="w-4 h-4 text-yellow-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <div>
                                <span class="font-bold">Perlu Verifikasi Mandiri:</span>
                                <span>{{ $parsedData['review_reason'] ?? 'Silakan pastikan tanggal dan nominal omzet di bawah sudah sesuai dengan bukti aslinya.' }}</span>
                            </div>
                        </div>
                    @endif

                    <!-- Kartu Ringkasan Angka Kunci (Tanggal & Nominal Bersih) -->
                    <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Kolom 1: Tanggal Transaksi -->
                        <div class="p-4 rounded-xl bg-[#FBFBFC] border border-[#EEEEEE]">
                            <label class="block text-[11px] font-bold text-[#777777] uppercase tracking-wider mb-1.5">
                                Tanggal Penjualan
                            </label>
                            <input
                                type="date"
                                wire:model.live="parsedData.transaction_date"
                                class="w-full px-3 py-2 rounded-xl bg-white border border-[#DCDCDC] text-sm font-bold text-[#252525] focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            />
                            <span class="text-[11px] text-[#777777] mt-1 block">
                                Tanggal rekap yang tertera di struk/laporan
                            </span>
                        </div>

                        <!-- Kolom 2: Total Kas Masuk Bersih (Net) -->
                        <div class="p-4 rounded-xl bg-[#DDF1EC]/40 border border-emerald-200">
                            <label class="block text-[11px] font-bold text-emerald-800 uppercase tracking-wider mb-1.5">
                                Total Kas Masuk Bersih (Rp)
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-sm font-bold text-emerald-700">Rp</span>
                                <input
                                    type="number"
                                    step="any"
                                    wire:model.live="parsedData.total_amount"
                                    class="w-full pl-10 pr-3 py-2 rounded-xl bg-white border border-emerald-300 text-base font-extrabold text-emerald-800 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                />
                            </div>
                            <span class="text-[11px] text-emerald-700 font-medium mt-1 block">
                                Uang riil yang masuk ke rekening / kas warung
                            </span>
                        </div>
                    </div>

                    <!-- Rincian Transparansi Komisi (Jika Ada Potongan Komisi) -->
                    @if (($parsedData['fee_amount'] ?? 0) > 0 || ($parsedData['gross_amount'] ?? 0) > ($parsedData['total_amount'] ?? 0))
                        <div class="mt-4 p-3.5 rounded-xl bg-slate-50 border border-slate-200 grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs">
                            <div>
                                <span class="text-[#777777] block text-[11px]">Omzet Kotor (Gross):</span>
                                <span class="font-bold text-[#252525]">Rp {{ number_format($parsedData['gross_amount'] ?? 0, 0, ',', '.') }}</span>
                            </div>
                            <div>
                                <span class="text-rose-600 block text-[11px]">Potongan Komisi/MDR:</span>
                                <span class="font-bold text-rose-700">-Rp {{ number_format($parsedData['fee_amount'] ?? 0, 0, ',', '.') }}</span>
                            </div>
                            <div class="col-span-2 sm:col-span-1">
                                <span class="text-emerald-700 block text-[11px]">Bersih Diterima:</span>
                                <span class="font-extrabold text-emerald-800">Rp {{ number_format($parsedData['total_amount'] ?? 0, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    @endif

                    <!-- Catatan Ringkasan Transaksi -->
                    <div class="mt-4">
                        <label class="block text-[11px] font-bold text-[#777777] uppercase tracking-wider mb-1.5">
                            Keterangan Catatan
                        </label>
                        <input
                            type="text"
                            wire:model.live="parsedData.notes"
                            class="w-full px-3.5 py-2 rounded-xl bg-white border border-[#DCDCDC] text-xs sm:text-sm text-[#252525] focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            placeholder="Ringkasan transaksi penjualan digital..."
                        />
                    </div>
                </div>

                <!-- Tabel Rincian Baris Data dengan Checkbox Persetujuan (DoD KV-037) -->
                @if (!empty($parsedData['rows']) && count($parsedData['rows']) > 1)
                    <div class="bg-white rounded-2xl p-5 sm:p-6 border border-[#EEEEEE] shadow-xs">
                        <div class="flex items-center justify-between mb-3 pb-3 border-b border-[#EEEEEE]">
                            <div class="flex items-center gap-2">
                                <h3 class="text-xs sm:text-sm font-bold text-[#252525]">
                                    Pilih Baris Transaksi yang Disetujui
                                </h3>
                                <span class="text-[11px] px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-semibold">
                                    {{ count(array_filter($parsedData['rows'], fn($r) => !empty($r['approved']))) }} / {{ count($parsedData['rows']) }} Baris Disetujui
                                </span>
                            </div>
                            <span class="text-xs font-semibold text-emerald-700">
                                Total Disetujui: Rp {{ number_format($parsedData['total_amount'] ?? 0, 0, ',', '.') }}
                            </span>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead>
                                    <tr class="border-b border-[#EEEEEE] text-[#777777]">
                                        <th class="py-2 px-2 text-center w-8">
                                            <input
                                                type="checkbox"
                                                wire:click="toggleSelectAll"
                                                {{ $selectAllRows ? 'checked' : '' }}
                                                class="rounded border-[#DCDCDC] text-emerald-600 focus:ring-emerald-500 cursor-pointer"
                                                title="Pilih Semua Baris"
                                            />
                                        </th>
                                        <th class="py-2 px-2 font-semibold">Tanggal</th>
                                        <th class="py-2 px-2 font-semibold">Platform</th>
                                        <th class="py-2 px-2 font-semibold">Keterangan</th>
                                        <th class="py-2 px-2 font-semibold text-right">Nominal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#EEEEEE]">
                                    @foreach ($parsedData['rows'] as $index => $row)
                                        <tr class="{{ empty($row['approved']) ? 'opacity-40 bg-slate-50' : 'hover:bg-[#FBFBFC]' }} transition-colors">
                                            <td class="py-2.5 px-2 text-center">
                                                <input
                                                    type="checkbox"
                                                    wire:click="toggleRowApproval({{ $index }})"
                                                    {{ !empty($row['approved']) ? 'checked' : '' }}
                                                    class="rounded border-[#DCDCDC] text-emerald-600 focus:ring-emerald-500 cursor-pointer"
                                                />
                                            </td>
                                            <td class="py-2.5 px-2 font-medium text-[#252525]">{{ $row['date'] ?? '-' }}</td>
                                            <td class="py-2.5 px-2 capitalize text-[#777777]">{{ $row['platform'] ?? '-' }}</td>
                                            <td class="py-2.5 px-2 text-[#777777] truncate max-w-xs">{{ $row['notes'] ?? '-' }}</td>
                                            <td class="py-2.5 px-2 font-bold text-emerald-700 text-right">
                                                Rp {{ number_format($row['amount'] ?? 0, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                <!-- Tombol Aksi Bawah: Ganti Berkas & Konfirmasi Simpan (DoD KV-037) -->
                <div class="bg-white rounded-2xl p-4 sm:p-5 border border-[#EEEEEE] flex flex-col sm:flex-row items-center justify-between gap-3 shadow-xs">
                    <button
                        type="button"
                        wire:click="removeFile"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl border border-[#EEEEEE] text-xs sm:text-sm font-medium text-[#777777] hover:text-rose-600 hover:border-rose-200 transition-colors"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        <span>Ganti Berkas Lain</span>
                    </button>

                    <div class="w-full sm:w-auto flex items-center gap-2">
                        <button
                            type="button"
                            wire:click="saveTransaction"
                            wire:loading.attr="disabled"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-bold shadow-xs transition-colors"
                        >
                            <span wire:loading.remove wire:target="saveTransaction">Konfirmasi & Simpan ke Kas Masuk</span>
                            <span wire:loading wire:target="saveTransaction" class="inline-flex items-center gap-2">
                                <span class="animate-spin rounded-full h-4 w-4 border-2 border-white border-t-transparent"></span>
                                <span>Menyimpan...</span>
                            </span>
                            <svg wire:loading.remove wire:target="saveTransaction" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                        </button>
                    </div>
                </div>

            </div>
        @endif

        <!-- MODAL PERINGATAN TRANSAKSI DUPLIKAT (PENCEGAHAN IMPOR GANDA - DoD KV-037) -->
        @include('livewire.partials.duplicate-warning-modal')

    </div>
</div>
