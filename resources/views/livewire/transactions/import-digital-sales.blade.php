<div class="w-full flex-1 flex flex-col space-y-4">
    <div class="max-w-4xl mx-auto w-full space-y-6">

        <!-- Tombol Kembali & Breadcrumb Navigasi -->
        <div class="mb-6 flex items-center justify-between">
            <a
                href="{{ route('transactions.create') }}"
                wire:navigate
                class="inline-flex items-center gap-2 min-h-[44px] px-3 py-2 -ml-3 rounded-xl text-xs sm:text-sm font-medium text-[#777777] hover:text-[#252525] hover:bg-slate-100/70 transition-colors"
            >
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
                </svg>
                <span>Kembali ke Pilih Jalur</span>
            </a>

            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-[#DDF1EC] text-[#43A58A] border border-[#43A58A]/30 shadow-xs">
                <svg class="w-3.5 h-3.5 text-[#43A58A] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m0 0l6.75-6.75M12 19.5l-6.75-6.75"/>
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
            <div class="mb-6 p-4 sm:p-5 rounded-2xl bg-[#FFF9F2] border border-[#E7A34A]/40 shadow-xs">
                <div class="flex items-start gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 text-[#E7A34A] flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="w-5 h-5 text-[#E7A34A]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-sm font-bold text-[#9B651D]">
                            Kuota AI Anda Sedang Penuh / Token Hari Ini Habis
                        </h3>
                        <p class="text-xs text-[#9B651D]/90 mt-1.5 leading-relaxed">
                            Batas pemrosesan otomatis harian dengan kecerdasan buatan telah tercapai. Tenang, berkas yang Anda unggah tetap tersimpan aman dan Anda bisa langsung mengisi total penjualan secara mandiri.
                        </p>

                        <div class="mt-4 flex flex-wrap gap-2.5">
                            <button
                                type="button"
                                wire:click="proceedToEmergencyReview"
                                class="inline-flex items-center gap-2 min-h-[44px] px-4 py-2.5 rounded-xl bg-[#E87559] hover:bg-[#D66448] text-white text-xs font-bold shadow-xs transition-colors focus:outline-hidden focus:ring-2 focus:ring-[#E87559] focus:ring-offset-2"
                            >
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/>
                                </svg>
                                <span>Lanjut Catat Manual (Tanpa AI)</span>
                            </button>
                            <button
                                type="button"
                                wire:click="parseUploadedFile"
                                class="inline-flex items-center gap-2 min-h-[44px] px-4 py-2.5 rounded-xl border border-[#EEEEEE] bg-white hover:bg-[#FBFBFC] text-[#555555] hover:text-[#252525] text-xs font-semibold transition-colors focus:outline-hidden focus:ring-2 focus:ring-[#E87559]"
                            >
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/>
                                </svg>
                                <span>Coba Lagi Nanti</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @elseif ($errorMessage)
            <!-- ALERT: ERROR UMUM LAINNYA -->
            <div class="mb-6 p-4 rounded-2xl bg-[#FFF5F5] border border-[#DC6464]/30 flex items-start gap-3 shadow-xs">
                <div class="w-9 h-9 rounded-xl bg-[#FCEDEA] text-[#DC6464] flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-5 h-5 text-[#DC6464]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-xs sm:text-sm font-bold text-[#DC6464]">Berkas Tidak Dapat Diterima</h3>
                    <p class="text-xs text-[#DC6464]/90 mt-1 leading-relaxed">
                        {{ $errorMessage }}
                    </p>
                </div>
                <button
                    type="button"
                    wire:click="$set('errorMessage', null)"
                    class="p-2 -mr-1 rounded-xl text-[#DC6464]/70 hover:text-[#DC6464] hover:bg-[#DC6464]/10 transition-colors"
                    aria-label="Tutup notifikasi error"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        @endif

        <!-- Pilihan Platform Asal (Opsional / Filter) - Tampil di tahap Upload & Preview -->
        @if ($step === 'upload' || $step === 'preview_file')
            <div class="mb-6 bg-white rounded-2xl p-4 sm:p-5 border border-[#EEEEEE] shadow-xs">
                <label class="block text-xs font-bold text-[#252525] uppercase tracking-wider mb-3">
                    Pilih Platform Penjualan (Opsional)
                </label>
                <div class="flex flex-wrap gap-2 text-xs">
                    @php
                        $platformOptions = [
                            'auto' => 'Deteksi Otomatis',
                            'gofood' => 'GoFood / GoBiz',
                            'grabfood' => 'GrabFood',
                            'shopeefood' => 'ShopeeFood',
                            'qris' => 'QRIS / Settlement Bank',
                            'pos' => 'Kasir Digital (POS)',
                            'other' => 'Lainnya',
                        ];
                    @endphp
                    @foreach ($platformOptions as $key => $label)
                        <button
                            type="button"
                            wire:click="setPlatform('{{ $key }}')"
                            class="inline-flex items-center gap-1.5 min-h-[40px] px-3.5 py-2 rounded-xl text-xs font-semibold transition-all focus:outline-hidden {{ $platform === $key ? 'bg-[#E87559] text-white shadow-xs' : 'bg-[#FBFBFC] hover:bg-[#EEEEEE] text-[#555555] hover:text-[#252525] border border-[#EEEEEE]' }}"
                        >
                            @if ($key === 'auto')
                                <svg class="w-3.5 h-3.5 {{ $platform === 'auto' ? 'text-white' : 'text-[#E87559]' }} shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z"/>
                                </svg>
                            @endif
                            <span>{{ $label }}</span>
                        </button>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- TAHAP 1: DROPZONE UNGGAH BERKAS -->
        @if ($step === 'upload')
            <div class="bg-white rounded-2xl p-6 sm:p-10 border-2 border-dashed border-[#DCDCDC] hover:border-[#E87559] hover:bg-[#FCEDEA]/10 transition-colors text-center relative shadow-xs">
                <input
                    type="file"
                    id="digitalSalesFile"
                    wire:model="uploadedFile"
                    accept="image/png,image/jpeg,image/webp,.csv,.xlsx,.xls,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel"
                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                />

                <div wire:loading.remove wire:target="uploadedFile">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-[#FCEDEA] text-[#E87559] border border-[#E87559]/20 flex items-center justify-center">
                        <svg class="w-8 h-8 text-[#E87559]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z"/>
                        </svg>
                    </div>

                    <h2 class="text-base sm:text-lg font-bold text-[#252525]">
                        Pilih Screenshot atau Berkas Penjualan
                    </h2>
                    <p class="mt-1.5 text-xs sm:text-sm text-[#777777] max-w-md mx-auto leading-relaxed">
                        Klik atau seret berkas ke sini. Mendukung tangkapan layar (<span class="font-semibold text-[#252525]">PNG, JPG, WEBP</span> maks 5 MB) atau spreadsheet (<span class="font-semibold text-[#252525]">CSV, XLSX</span> maks 10 MB).
                    </p>

                    <div class="mt-6 flex flex-wrap justify-center gap-3">
                        <button
                            type="button"
                            class="inline-flex items-center gap-2 min-h-[48px] px-6 py-3 rounded-xl bg-[#E87559] hover:bg-[#D66448] text-white text-xs sm:text-sm font-bold shadow-xs transition-colors pointer-events-none"
                        >
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                            </svg>
                            <span>Pilih Berkas dari HP / Laptop</span>
                        </button>
                    </div>
                </div>

                <!-- Indikator Loading Saat File Diunggah -->
                <div wire:loading wire:target="uploadedFile" class="py-8">
                    <div class="inline-block animate-spin rounded-full h-10 w-10 border-4 border-[#E87559] border-t-transparent mb-3"></div>
                    <p class="text-sm font-semibold text-[#252525]">Sedang mengunggah berkas...</p>
                    <p class="text-xs text-[#777777] mt-1">Mohon tunggu sebentar selagi sistem memeriksa format berkas.</p>
                </div>
            </div>

            <!-- Petunjuk Format yang Didukung -->
            <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="p-4 sm:p-5 rounded-2xl bg-white border border-[#EEEEEE] flex items-start gap-3.5 shadow-xs">
                    <div class="w-10 h-10 rounded-xl bg-[#FCEDEA] text-[#E87559] flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-[#E87559]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xs sm:text-sm font-bold text-[#252525]">1. Tangkapan Layar (Screenshot)</h3>
                        <p class="text-xs text-[#777777] mt-1 leading-relaxed">
                            Buka aplikasi GoBiz / Grab / Shopee Partner di HP, screenshot layar ringkasan omzet hari ini, lalu unggah.
                        </p>
                    </div>
                </div>

                <div class="p-4 sm:p-5 rounded-2xl bg-white border border-[#EEEEEE] flex items-start gap-3.5 shadow-xs">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xs sm:text-sm font-bold text-[#252525]">2. Berkas Data (CSV / XLSX)</h3>
                        <p class="text-xs text-[#777777] mt-1 leading-relaxed">
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
                    <div class="flex items-center gap-2.5">
                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-[#DDF1EC] text-[#43A58A] shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                            </svg>
                        </span>
                        <h2 class="text-sm sm:text-base font-bold text-[#252525]">
                            Berkas Valid Berhasil Diterima
                        </h2>
                    </div>

                    @if ($fileType === 'screenshot')
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-[#DDF1EC] text-[#43A58A] border border-[#43A58A]/30">
                            <svg class="w-3.5 h-3.5 text-[#43A58A] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z"/>
                            </svg>
                            <span>Tangkapan Layar</span>
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-[#E1F0F7] text-blue-700 border border-blue-200/60">
                            <svg class="w-3.5 h-3.5 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                            </svg>
                            <span>Dokumen Spreadsheet</span>
                        </span>
                    @endif
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
                        <div class="w-full sm:w-48 h-40 rounded-xl bg-blue-50 border border-blue-200 flex flex-col items-center justify-center shrink-0 text-blue-700">
                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
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
                            <div class="p-3 rounded-xl bg-[#FBFBFC] border border-[#EEEEEE]">
                                <span class="text-[#777777] block text-[11px]">Ukuran:</span>
                                <span class="font-bold text-[#252525]">{{ $fileMeta['size'] ?? '-' }}</span>
                            </div>
                            <div class="p-3 rounded-xl bg-[#FBFBFC] border border-[#EEEEEE]">
                                <span class="text-[#777777] block text-[11px]">Platform:</span>
                                <span class="font-bold text-[#E87559] capitalize">{{ $platform === 'auto' ? 'Otomatis' : $platform }}</span>
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
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 min-h-[44px] px-4 py-2.5 rounded-xl border border-[#EEEEEE] text-xs sm:text-sm font-medium text-[#777777] hover:text-[#DC6464] hover:border-[#DC6464]/30 hover:bg-[#FFF5F5] transition-colors"
                    >
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                        </svg>
                        <span>Ganti / Hapus Berkas</span>
                    </button>

                    <button
                        type="button"
                        wire:click="parseUploadedFile"
                        wire:loading.attr="disabled"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 min-h-[44px] px-6 py-2.5 rounded-xl bg-[#E87559] hover:bg-[#D66448] text-white text-xs sm:text-sm font-bold shadow-xs transition-colors focus:outline-hidden focus:ring-2 focus:ring-[#E87559] focus:ring-offset-2"
                    >
                        <span wire:loading.remove wire:target="parseUploadedFile">Lanjut Ekstraksi Data (AI)</span>
                        <span wire:loading wire:target="parseUploadedFile" class="inline-flex items-center gap-2">
                            <span class="animate-spin rounded-full h-4 w-4 border-2 border-white border-t-transparent"></span>
                            <span>Memproses...</span>
                        </span>
                        <svg wire:loading.remove wire:target="parseUploadedFile" class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                        </svg>
                    </button>
                </div>
            </div>
        @endif

        <!-- TAHAP 3: INDIKATOR LOADING PARSING -->
        @if ($step === 'parsing')
            <div class="bg-white rounded-2xl p-8 sm:p-12 border border-[#EEEEEE] text-center shadow-xs">
                <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-[#E87559] border-t-transparent mb-4"></div>
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
                            <div class="w-8 h-8 rounded-xl bg-[#DDF1EC] text-[#43A58A] flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
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

                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-[#FCEDEA] text-[#E87559] border border-[#E87559]/30">
                            {{ $parsedData['platform'] ?? 'other' }}
                        </span>
                    </div>

                    <!-- Highlight Peringatan Review (Jika AI Ragu atau Buram) -->
                    @if (!empty($parsedData['needs_review']))
                        <div class="mt-4 p-3.5 rounded-xl bg-[#FFF9F2] border border-[#E7A34A]/40 flex items-start gap-2.5 text-xs text-[#9B651D]">
                            <svg class="w-4 h-4 text-[#E7A34A] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
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
                                class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl bg-white border border-[#DCDCDC] text-sm font-bold text-[#252525] focus:ring-2 focus:ring-[#E87559] focus:border-[#E87559]"
                            />
                            <span class="text-[11px] text-[#777777] mt-1.5 block">
                                Tanggal rekap yang tertera di struk/laporan
                            </span>
                        </div>

                        <!-- Kolom 2: Total Kas Masuk Bersih (Net) -->
                        <div class="p-4 rounded-xl bg-[#DDF1EC]/50 border border-[#43A58A]/30">
                            <label class="block text-[11px] font-bold text-[#43A58A] uppercase tracking-wider mb-1.5">
                                Total Kas Masuk Bersih (Rp)
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-sm font-bold text-[#43A58A] pointer-events-none">Rp</span>
                                <input
                                    type="number"
                                    step="any"
                                    wire:model.live="parsedData.total_amount"
                                    class="w-full min-h-[44px] pl-11 pr-3.5 py-2.5 rounded-xl bg-white border border-[#43A58A]/40 text-base font-extrabold text-[#43A58A] focus:ring-2 focus:ring-[#E87559] focus:border-[#E87559]"
                                />
                            </div>
                            <span class="text-[11px] text-[#43A58A] font-medium mt-1.5 block">
                                Uang riil yang masuk ke rekening / kas warung
                            </span>
                        </div>
                    </div>

                    <!-- Rincian Transparansi Komisi (Jika Ada Potongan Komisi) -->
                    @if (($parsedData['fee_amount'] ?? 0) > 0 || ($parsedData['gross_amount'] ?? 0) > ($parsedData['total_amount'] ?? 0))
                        <div class="mt-4 p-3.5 sm:p-4 rounded-xl bg-slate-50 border border-slate-200 grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs">
                            <div>
                                <span class="text-[#777777] block text-[11px]">Omzet Kotor (Gross):</span>
                                <span class="font-bold text-[#252525]">Rp {{ number_format($parsedData['gross_amount'] ?? 0, 0, ',', '.') }}</span>
                            </div>
                            <div>
                                <span class="text-[#DC6464] block text-[11px]">Potongan Komisi/MDR:</span>
                                <span class="font-bold text-[#DC6464]">-Rp {{ number_format($parsedData['fee_amount'] ?? 0, 0, ',', '.') }}</span>
                            </div>
                            <div class="col-span-2 sm:col-span-1">
                                <span class="text-[#43A58A] block text-[11px]">Bersih Diterima:</span>
                                <span class="font-extrabold text-[#43A58A]">Rp {{ number_format($parsedData['total_amount'] ?? 0, 0, ',', '.') }}</span>
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
                            class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl bg-white border border-[#DCDCDC] text-xs sm:text-sm text-[#252525] focus:ring-2 focus:ring-[#E87559] focus:border-[#E87559]"
                            placeholder="Ringkasan transaksi penjualan digital..."
                        />
                    </div>
                </div>

                <!-- Tabel Rincian Baris Data dengan Checkbox Persetujuan (DoD KV-037) -->
                @if (!empty($parsedData['rows']) && count($parsedData['rows']) > 1)
                    <div class="bg-white rounded-2xl p-5 sm:p-6 border border-[#EEEEEE] shadow-xs">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3 pb-3 border-b border-[#EEEEEE]">
                            <div class="flex items-center gap-2">
                                <h3 class="text-xs sm:text-sm font-bold text-[#252525]">
                                    Pilih Baris Transaksi yang Disetujui
                                </h3>
                                <span class="text-[11px] px-2 py-0.5 rounded-full bg-[#DDF1EC] text-[#43A58A] font-semibold">
                                    {{ count(array_filter($parsedData['rows'], fn($r) => !empty($r['approved']))) }} / {{ count($parsedData['rows']) }} Baris Disetujui
                                </span>
                            </div>
                            <span class="text-xs font-semibold text-[#43A58A]">
                                Total Disetujui: Rp {{ number_format($parsedData['total_amount'] ?? 0, 0, ',', '.') }}
                            </span>
                        </div>

                        <div class="overflow-x-auto -mx-5 sm:mx-0 px-5 sm:px-0">
                            <table class="w-full text-left text-xs min-w-[500px]">
                                <thead>
                                    <tr class="border-b border-[#EEEEEE] text-[#777777]">
                                        <th class="py-2.5 px-3 text-center w-10">
                                            <input
                                                type="checkbox"
                                                wire:click="toggleSelectAll"
                                                {{ $selectAllRows ? 'checked' : '' }}
                                                class="w-4 h-4 rounded border-[#DCDCDC] text-[#E87559] focus:ring-[#E87559] cursor-pointer"
                                                title="Pilih Semua Baris"
                                            />
                                        </th>
                                        <th class="py-2.5 px-3 font-semibold">Tanggal</th>
                                        <th class="py-2.5 px-3 font-semibold">Platform</th>
                                        <th class="py-2.5 px-3 font-semibold">Keterangan</th>
                                        <th class="py-2.5 px-3 font-semibold text-right">Nominal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#EEEEEE]">
                                    @foreach ($parsedData['rows'] as $index => $row)
                                        <tr class="{{ empty($row['approved']) ? 'opacity-40 bg-slate-50' : 'hover:bg-[#FBFBFC]' }} transition-colors">
                                            <td class="py-3 px-3 text-center">
                                                <input
                                                    type="checkbox"
                                                    wire:click="toggleRowApproval({{ $index }})"
                                                    {{ !empty($row['approved']) ? 'checked' : '' }}
                                                    class="w-4 h-4 rounded border-[#DCDCDC] text-[#E87559] focus:ring-[#E87559] cursor-pointer"
                                                />
                                            </td>
                                            <td class="py-3 px-3 font-medium text-[#252525]">{{ $row['date'] ?? '-' }}</td>
                                            <td class="py-3 px-3 capitalize text-[#777777]">{{ $row['platform'] ?? '-' }}</td>
                                            <td class="py-3 px-3 text-[#777777] truncate max-w-xs">{{ $row['notes'] ?? '-' }}</td>
                                            <td class="py-3 px-3 font-bold text-[#43A58A] text-right">
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
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 min-h-[48px] px-4 py-2.5 rounded-xl border border-[#EEEEEE] text-xs sm:text-sm font-medium text-[#777777] hover:text-[#DC6464] hover:border-[#DC6464]/30 transition-colors"
                    >
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                        </svg>
                        <span>Ganti Berkas Lain</span>
                    </button>

                    <div class="w-full sm:w-auto flex items-center gap-2">
                        <button
                            type="button"
                            wire:click="saveTransaction"
                            wire:loading.attr="disabled"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 min-h-[48px] px-6 py-2.5 rounded-xl bg-[#E87559] hover:bg-[#D66448] text-white text-xs sm:text-sm font-bold shadow-xs transition-colors focus:outline-hidden focus:ring-2 focus:ring-[#E87559] focus:ring-offset-2"
                        >
                            <span wire:loading.remove wire:target="saveTransaction">Konfirmasi & Simpan ke Kas Masuk</span>
                            <span wire:loading wire:target="saveTransaction" class="inline-flex items-center gap-2">
                                <span class="animate-spin rounded-full h-4 w-4 border-2 border-white border-t-transparent"></span>
                                <span>Menyimpan...</span>
                            </span>
                            <svg wire:loading.remove wire:target="saveTransaction" class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                            </svg>
                        </button>
                    </div>
                </div>

            </div>
        @endif

    </div>

        <!-- MODAL PERINGATAN TRANSAKSI DUPLIKAT (PENCEGAHAN IMPOR GANDA - DoD KV-037) -->
        @include('livewire.partials.duplicate-warning-modal')

</div>
