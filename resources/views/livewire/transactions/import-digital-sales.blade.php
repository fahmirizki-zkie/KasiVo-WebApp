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

        <!-- Pesan Error / Format Ditolak -->
        @if ($errorMessage)
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

        <!-- Pilihan Platform Asal (Opsional / Filter) -->
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
                        wire:click="proceedToParsing"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-bold shadow-xs transition-colors"
                    >
                        <span>Lanjut Ekstraksi Data (AI)</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </button>
                </div>
            </div>
        @endif

        <!-- TAHAP 3: PLACEHOLDER PARSING (SIAP UNTUK KV-036) -->
        @if ($step === 'parsing')
            <div class="bg-white rounded-2xl p-8 border border-[#EEEEEE] text-center shadow-xs">
                <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-emerald-500 border-t-transparent mb-4"></div>
                <h2 class="text-base sm:text-lg font-bold text-[#252525]">
                    Sedang Mengekstrak Data Penjualan Digital...
                </h2>
                <p class="mt-1 text-xs sm:text-sm text-[#777777] max-w-md mx-auto leading-relaxed">
                    Kecerdasan buatan KasiVo sedang membaca tanggal transaksi dan total nominal omzet dari berkas <span class="font-semibold text-[#252525]">{{ $fileMeta['name'] ?? '' }}</span>.
                </p>
                <div class="mt-6">
                    <button
                        type="button"
                        wire:click="$set('step', 'preview_file')"
                        class="text-xs font-semibold text-emerald-600 hover:underline"
                    >
                        ← Kembali ke Pratinjau Berkas
                    </button>
                </div>
            </div>
        @endif

    </div>
</div>

