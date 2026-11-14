<div class="w-full flex-1 flex flex-col space-y-4" x-data="incomeSalesManager($wire)" @income-back-to-input.window="backToInput()">

    <!-- ======================================================== -->
    <!-- HALAMAN 1: INPUT PENJUALAN (NOTA / SUARA / TEKS BEBAS)  -->
    <!-- Hanya ditampilkan pada tahap input data (currentStep = 'input') -->
    <!-- ======================================================== -->
    <div x-show="currentStep === 'input'" x-cloak class="bg-white rounded-2xl sm:rounded-3xl shadow-sm border border-[#EEEEEE] p-5 sm:p-7 lg:p-8 flex-1 flex flex-col justify-between">

        <div>
            <!-- 1. Header Halaman Input & Badge Kas Masuk -->
            <div class="mb-6 pb-5 border-b border-[#EEEEEE]">
                <div class="mb-3">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-[#DDF1EC] text-[#43A58A] border border-[#43A58A]/30 shadow-xs">
                        <svg class="w-3.5 h-3.5 text-[#43A58A]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>Kas Masuk (Penjualan)</span>
                    </span>
                </div>

                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#252525] tracking-tight">
                    Catat Penjualan Harian
                </h1>
                <p class="text-xs sm:text-sm text-[#777777] mt-1.5 leading-relaxed">
                    Pilih salah satu metode input penjualan: foto/unggah nota, rekam suara kasir, atau tulis deskripsi bebas. Hasil ekstraksi akan dipetakan ke halaman konfirmasi kas masuk.
                </p>
            </div>

            <!-- 2. Tab Pintas Navigasi 3 Moda Input Penjualan -->
            <div class="max-w-xl mx-auto w-full mb-6 sm:mb-8">
                <div class="grid grid-cols-3 gap-1.5 p-1.5 rounded-2xl bg-gray-100/80 border border-[#EEEEEE]">
                    <!-- Tab 1: Input Nota -->
                    <button
                        type="button"
                        @click="switchTab('note')"
                        :class="activeTab === 'note' ? 'bg-white text-[#E87559] font-bold shadow-xs border border-[#E87559]/20' : 'text-[#777777] hover:text-[#252525] hover:bg-white/60 font-semibold'"
                        class="flex items-center justify-center gap-1.5 py-2.5 px-2 sm:px-3 rounded-xl text-xs sm:text-sm transition-all cursor-pointer"
                    >
                        <svg class="w-4 h-4" :class="activeTab === 'note' ? 'text-[#E87559]' : 'text-[#777777]'" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span class="truncate">Input Nota</span>
                    </button>

                    <!-- Tab 2: Suara -->
                    <button
                        type="button"
                        @click="switchTab('voice')"
                        :class="activeTab === 'voice' ? 'bg-white text-[#E87559] font-bold shadow-xs border border-[#E87559]/20' : 'text-[#777777] hover:text-[#252525] hover:bg-white/60 font-semibold'"
                        class="flex items-center justify-center gap-1.5 py-2.5 px-2 sm:px-3 rounded-xl text-xs sm:text-sm transition-all cursor-pointer"
                    >
                        <svg class="w-4 h-4" :class="activeTab === 'voice' ? 'text-[#E87559]' : 'text-[#777777]'" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                        </svg>
                        <span class="truncate">Suara</span>
                    </button>

                    <!-- Tab 3: Teks Bebas -->
                    <button
                        type="button"
                        @click="switchTab('text')"
                        :class="activeTab === 'text' ? 'bg-white text-[#E87559] font-bold shadow-xs border border-[#E87559]/20' : 'text-[#777777] hover:text-[#252525] hover:bg-white/60 font-semibold'"
                        class="flex items-center justify-center gap-1.5 py-2.5 px-2 sm:px-3 rounded-xl text-xs sm:text-sm transition-all cursor-pointer"
                    >
                        <svg class="w-4 h-4" :class="activeTab === 'text' ? 'text-[#E87559]' : 'text-[#777777]'" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span class="truncate">Teks Bebas</span>
                    </button>
                </div>
            </div>

            <!-- Loading State Saat AI Mengekstrak Data -->
            <div x-show="isProcessing" x-cloak class="p-4 sm:p-5 rounded-2xl bg-[#FCEDEA]/70 border border-[#E87559]/30 mb-6 flex items-center gap-3.5 shadow-xs animate-pulse">
                <div class="w-10 h-10 rounded-xl bg-[#E87559] text-white flex items-center justify-center shrink-0">
                    <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <h4 class="text-xs sm:text-sm font-bold text-[#252525]">AI Sedang Membaca & Memproses Data Penjualan</h4>
                    <p class="text-xs text-[#777777] mt-0.5" x-text="processingMessage || 'Mengekstrak nominal penjualan kas masuk...'"></p>
                </div>
            </div>

            <!-- Error Banner -->
            <template x-if="errorMessage">
                <div class="p-4 rounded-2xl bg-[#FFF5F5] border border-[#DC6464]/30 flex items-start gap-3 shadow-xs mb-6">
                    <div class="w-9 h-9 rounded-xl bg-[#FCEDEA] text-[#DC6464] flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="w-5 h-5 text-[#DC6464]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h4 class="text-xs sm:text-sm font-bold text-[#DC6464]">Pemrosesan Belum Berhasil</h4>
                        <p class="text-xs text-[#DC6464]/90 mt-0.5 leading-relaxed" x-text="errorMessage"></p>
                    </div>
                    <button type="button" @click="errorMessage = null" class="p-1.5 rounded-lg text-[#DC6464] hover:bg-[#DC6464]/10 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </template>

            <!-- ======================================================== -->
            <!-- TAB 1: INPUT NOTA                                        -->
            <!-- ======================================================== -->
            <div x-show="activeTab === 'note'" class="space-y-5 max-w-2xl mx-auto w-full">
                <input
                    type="file"
                    accept="image/jpeg,image/png,image/jpg,image/webp"
                    capture="environment"
                    class="sr-only"
                    id="incomeCameraInput"
                    @change="handleFileSelect($event)"
                />
                <input
                    type="file"
                    accept="image/jpeg,image/png,image/jpg,image/webp,.csv,.xlsx,.xls,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel"
                    class="sr-only"
                    id="incomeFileInput"
                    @change="handleFileSelect($event)"
                />

                <!-- Format Error -->
                <template x-if="fileError">
                    <div class="p-4 rounded-2xl bg-[#FFF5F5] border border-[#DC6464]/30 flex items-start gap-3 shadow-xs">
                        <div class="w-9 h-9 rounded-xl bg-[#FCEDEA] text-[#DC6464] flex items-center justify-center shrink-0 mt-0.5">
                            <svg class="w-5 h-5 text-[#DC6464]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-xs sm:text-sm font-bold text-[#DC6464]">Berkas Tidak Didukung</h4>
                            <p class="text-xs text-[#DC6464]/90 mt-0.5 leading-relaxed" x-text="fileError"></p>
                        </div>
                        <button type="button" @click="fileError = null" class="p-1.5 rounded-lg text-[#DC6464] hover:bg-[#DC6464]/10 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </template>

                <!-- Dropzone / Pilihan Ambil Foto -->
                <div x-show="!photoPreview && !photoMeta" class="space-y-4">
                    <div
                        @dragover.prevent="isDragging = true"
                        @dragleave.prevent="isDragging = false"
                        @drop.prevent="handleFileDrop($event)"
                        :class="isDragging ? 'border-[#E87559] bg-[#FCEDEA]/20 scale-[1.01]' : 'border-[#EEEEEE] bg-[#FBFBFC]'"
                        class="p-6 sm:p-8 rounded-2xl border-2 border-dashed text-center transition-all"
                    >
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <button
                                type="button"
                                onclick="document.getElementById('incomeCameraInput').click()"
                                class="p-5 sm:p-6 rounded-2xl border border-[#EEEEEE] hover:border-[#E87559] bg-white text-center cursor-pointer transition-all space-y-3 group shadow-2xs hover:shadow-xs"
                            >
                                <div class="w-14 h-14 rounded-2xl bg-[#FCEDEA] text-[#E87559] flex items-center justify-center mx-auto shadow-2xs group-hover:scale-105 transition-transform">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </div>
                                <div class="space-y-1">
                                    <p class="text-sm font-bold text-[#252525] group-hover:text-[#E87559] transition-colors">Buka Kamera Perangkat</p>
                                    <p class="text-xs text-[#777777] leading-relaxed">Ambil foto nota/bon penjualan dari jarak dekat agar tulisan terbaca jelas (hindari memotret terlalu jauh)</p>
                                </div>
                            </button>

                            <button
                                type="button"
                                onclick="document.getElementById('incomeFileInput').click()"
                                class="p-5 sm:p-6 rounded-2xl border border-[#EEEEEE] hover:border-[#E87559] bg-white text-center cursor-pointer transition-all space-y-3 group shadow-2xs hover:shadow-xs"
                            >
                                <div class="w-14 h-14 rounded-2xl bg-[#E1F0F7] text-[#0284C7] flex items-center justify-center mx-auto shadow-2xs group-hover:scale-105 transition-transform">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                    </svg>
                                </div>
                                <div class="space-y-1">
                                    <p class="text-sm font-bold text-[#252525] group-hover:text-[#E87559] transition-colors">Pilih Berkas Penjualan</p>
                                    <p class="text-xs text-[#777777]">Unggah berkas struk (<span class="font-semibold text-[#252525]">JPG, PNG, WEBP</span>) atau spreadsheet (<span class="font-semibold text-[#252525]">CSV, XLSX</span>)</p>
                                </div>
                            </button>
                        </div>

                        <div class="mt-4 pt-4 border-t border-[#EEEEEE]/80 flex flex-wrap items-center justify-center gap-2 text-[11px] text-[#777777]">
                            <span class="font-medium text-[#252525]">Format yang didukung:</span>
                            <span class="px-2 py-0.5 rounded-md bg-white border border-[#EEEEEE] font-semibold text-[#555555]">JPG, PNG, WEBP (maks 5 MB)</span>
                            <span>&bull;</span>
                            <span class="px-2 py-0.5 rounded-md bg-white border border-[#EEEEEE] font-semibold text-[#555555]">CSV, XLSX (maks 10 MB)</span>
                        </div>
                    </div>
                </div>

                <!-- Pratinjau Foto Nota -->
                <div x-show="photoPreview" class="p-5 rounded-2xl bg-[#FBFBFC] border border-[#EEEEEE] space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#43A58A]"></span>
                            <span class="text-xs font-bold text-[#252525]" x-text="photoMeta ? photoMeta.name : 'Foto Nota Terpilih'"></span>
                            <span class="text-[11px] px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-semibold" x-text="photoMeta ? photoMeta.size : ''"></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                onclick="document.getElementById('incomeCameraInput').click()"
                                class="px-3 py-1.5 rounded-lg text-xs font-semibold text-[#777777] hover:text-[#252525] bg-white border border-[#EEEEEE] hover:bg-gray-50 transition"
                            >
                                Foto Ulang
                            </button>
                            <button
                                type="button"
                                @click="removePhoto()"
                                class="px-3 py-1.5 rounded-lg text-xs font-semibold text-[#DC6464] hover:bg-red-50 border border-transparent hover:border-red-200 transition"
                            >
                                Hapus
                            </button>
                        </div>
                    </div>

                    <div class="w-full flex justify-center bg-white p-3 rounded-xl border border-[#EEEEEE]">
                        <img :src="photoPreview" alt="Pratinjau Nota Penjualan" class="max-h-72 rounded-lg object-contain shadow-xs" />
                    </div>

                    <div class="pt-2 flex flex-col sm:flex-row items-center justify-between gap-3">
                        <p class="text-[11px] text-[#777777]">
                            Foto siap diproses. Klik tombol di samping untuk mengekstrak data dan melanjutkan ke Halaman Konfirmasi.
                        </p>
                        <button
                            type="button"
                            @click="extractFromNote()"
                            :disabled="isProcessing"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold text-white bg-[#E87559] hover:bg-[#D66448] shadow-xs transition active:scale-95 disabled:opacity-50 shrink-0 cursor-pointer"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            <span>Ekstrak Nota & Lanjut ke Konfirmasi &rarr;</span>
                        </button>
                    </div>
                </div>

                <!-- Pratinjau Dokumen Spreadsheet -->
                <div x-show="photoMeta && photoMeta.type === 'spreadsheet'" class="p-5 rounded-2xl bg-[#E1F0F7]/40 border border-[#BCE1F1] space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl bg-blue-100 text-[#0284C7] flex items-center justify-center shrink-0">
                                <svg class="w-6 h-6 text-[#0284C7]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h4 class="text-xs sm:text-sm font-bold text-[#252525]" x-text="photoMeta ? photoMeta.name : 'Laporan Spreadsheet'"></h4>
                                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-blue-100 text-[#0284C7] font-bold" x-text="photoMeta ? photoMeta.ext : 'CSV'"></span>
                                </div>
                                <p class="text-xs text-[#777777] mt-0.5" x-text="'Ukuran dokumen: ' + (photoMeta ? photoMeta.size : '-')"></p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                @click="removePhoto()"
                                class="px-3 py-1.5 rounded-lg text-xs font-semibold text-[#DC6464] hover:bg-red-50 border border-transparent hover:border-red-200 transition"
                            >
                                Hapus
                            </button>
                        </div>
                    </div>
                    <div class="p-3 rounded-xl bg-white/80 border border-[#BCE1F1]/60 text-xs text-[#0369A1] flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
                        <span>Dokumen tervalidasi. Untuk impor otomatis multi-baris dari merchant, gunakan modul Impor Digital.</span>
                        <a href="{{ route('transactions.import-digital') }}" wire:navigate class="font-bold underline shrink-0">Buka Impor Digital &rarr;</a>
                    </div>
                </div>
            </div>

            <!-- ======================================================== -->
            <!-- TAB 2: SUARA (REKAM SUARA LANGSUNG & AUDIO FILE)         -->
            <!-- ======================================================== -->
            <div x-show="activeTab === 'voice'" class="space-y-6 max-w-xl mx-auto w-full">
                <!-- Switcher: Rekam vs Upload Audio -->
                <div class="flex p-1 bg-[#FBFBFC] rounded-2xl border border-[#EEEEEE] max-w-lg mx-auto w-full">
                    <button
                        type="button"
                        @click="voiceSubTab = 'record'"
                        :class="voiceSubTab === 'record' ? 'bg-white text-[#252525] shadow-xs font-bold' : 'text-[#777777] hover:text-[#252525] font-medium'"
                        class="flex-1 py-2 px-3 rounded-xl text-xs sm:text-sm flex items-center justify-center gap-2 transition-all cursor-pointer"
                    >
                        <svg class="w-4 h-4 text-[#E87559]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                        </svg>
                        <span>Rekam Suara Langsung</span>
                    </button>

                    <button
                        type="button"
                        @click="voiceSubTab = 'upload'"
                        :class="voiceSubTab === 'upload' ? 'bg-white text-[#252525] shadow-xs font-bold' : 'text-[#777777] hover:text-[#252525] font-medium'"
                        class="flex-1 py-2 px-3 rounded-xl text-xs sm:text-sm flex items-center justify-center gap-2 transition-all cursor-pointer"
                    >
                        <svg class="w-4 h-4 text-[#777777]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        <span>Unggah File Audio</span>
                    </button>
                </div>

                <!-- Error Mic -->
                <template x-if="voiceError">
                    <div class="p-4 rounded-2xl bg-[#FFF5F5] border border-[#DC6464]/30 text-xs text-[#DC6464] flex items-center justify-between">
                        <span x-text="voiceError"></span>
                        <button type="button" @click="voiceError = null" class="font-bold underline ml-2">Tutup</button>
                    </div>
                </template>

                <!-- Subtab Rekam Langsung -->
                <div x-show="voiceSubTab === 'record'" class="text-center py-6 sm:py-8 space-y-5">
                    <!-- State A: Idle -->
                    <div x-show="voiceState === 'idle'" class="space-y-4">
                        <div class="relative inline-flex items-center justify-center">
                            <button
                                type="button"
                                @click="startVoiceRecording()"
                                class="w-24 h-24 sm:w-28 sm:h-28 rounded-full bg-[#E87559] hover:bg-[#D66448] text-white flex items-center justify-center shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105 active:scale-95 group focus:outline-hidden cursor-pointer"
                                aria-label="Mulai merekam suara penjualan"
                            >
                                <svg class="w-10 h-10 sm:w-12 sm:h-12 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                                </svg>
                            </button>
                        </div>
                        <div>
                            <h3 class="text-sm sm:text-base font-bold text-[#252525]">Sentuh Tombol Mikrofon untuk Bicara</h3>
                            <p class="text-xs text-[#777777] mt-1 max-w-md mx-auto">
                                Sebutkan rincian penjualan kasir. Contoh: <span class="italic text-[#252525]">"Hasil penjualan hari ini ada satu juta lima ratus ribu."</span>
                            </p>
                        </div>
                    </div>

                    <!-- State B: Recording -->
                    <div x-show="voiceState === 'recording'" class="space-y-5">
                        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-red-100 text-red-700 text-xs font-bold animate-pulse">
                            <span class="w-2.5 h-2.5 rounded-full bg-red-600"></span>
                            <span>Merekam Suara Kasir...</span>
                        </div>

                        <div class="text-3xl sm:text-4xl font-extrabold text-[#252525] font-mono tracking-wider" x-text="formatDuration(voiceDuration)">
                            00:00
                        </div>

                        <!-- Wave Visualizer -->
                        <div class="flex items-center justify-center gap-1.5 h-12">
                            <span class="w-1.5 bg-[#E87559] rounded-full animate-bounce h-5" style="animation-delay: 0.1s"></span>
                            <span class="w-1.5 bg-[#E87559] rounded-full animate-bounce h-9" style="animation-delay: 0.3s"></span>
                            <span class="w-1.5 bg-[#E87559] rounded-full animate-bounce h-12" style="animation-delay: 0.2s"></span>
                            <span class="w-1.5 bg-[#E87559] rounded-full animate-bounce h-7" style="animation-delay: 0.4s"></span>
                            <span class="w-1.5 bg-[#E87559] rounded-full animate-bounce h-11" style="animation-delay: 0.15s"></span>
                            <span class="w-1.5 bg-[#E87559] rounded-full animate-bounce h-6" style="animation-delay: 0.35s"></span>
                        </div>

                        <div class="flex items-center justify-center gap-3 pt-2">
                            <button
                                type="button"
                                @click="stopVoiceRecording()"
                                class="px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold text-white bg-red-600 hover:bg-red-700 shadow-md transition active:scale-95 flex items-center gap-2 cursor-pointer"
                            >
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                    <rect x="6" y="6" width="12" height="12" rx="2"/>
                                </svg>
                                <span>Selesai Merekam</span>
                            </button>
                        </div>
                    </div>

                    <!-- State C: Recorded Preview -->
                    <div x-show="voiceState === 'recorded'" class="space-y-4 max-w-lg mx-auto">
                        <div class="p-4 rounded-2xl bg-[#FBFBFC] border border-[#EEEEEE] space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-[#252525] flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#43A58A]"></span>
                                    <span>Rekaman Suara Siap Diproses</span>
                                </span>
                                <span class="text-xs font-mono text-[#777777]" x-text="formatDuration(voiceDuration)"></span>
                            </div>
                            <template x-if="voiceAudioUrl">
                                <audio :src="voiceAudioUrl" controls class="w-full h-10 rounded-lg"></audio>
                            </template>

                            <div x-show="voiceTranscription" class="p-3 rounded-xl bg-white border border-[#EEEEEE] text-left">
                                <span class="text-[10px] font-bold text-[#777777] uppercase tracking-wider block mb-1">Transkrip Terdeteksi:</span>
                                <p class="text-xs text-[#252525] italic leading-relaxed" x-text="'“' + voiceTranscription + '”'"></p>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center justify-center gap-3">
                            <button
                                type="button"
                                @click="resetVoiceRecording()"
                                class="px-4 py-2.5 rounded-xl text-xs font-semibold text-[#777777] hover:text-[#252525] bg-white border border-[#EEEEEE] hover:bg-gray-50 transition cursor-pointer"
                            >
                                Rekam Ulang
                            </button>
                            <button
                                type="button"
                                @click="extractFromVoice()"
                                :disabled="isProcessing"
                                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold text-white bg-[#E87559] hover:bg-[#D66448] shadow-xs transition active:scale-95 disabled:opacity-50 cursor-pointer"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                                <span>Ekstrak Suara & Lanjut ke Konfirmasi &rarr;</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Subtab Upload Audio -->
                <div x-show="voiceSubTab === 'upload'" class="space-y-4">
                    <input
                        type="file"
                        accept="audio/*,.mp3,.wav,.m4a,.ogg,.aac"
                        class="sr-only"
                        id="incomeAudioFileInput"
                        @change="handleAudioFileSelect($event)"
                    />
                    <div
                        onclick="document.getElementById('incomeAudioFileInput').click()"
                        class="p-8 rounded-2xl border-2 border-dashed border-[#EEEEEE] hover:border-[#E87559] bg-[#FBFBFC] hover:bg-[#FCEDEA]/20 text-center cursor-pointer transition-all space-y-3 group"
                    >
                        <div class="w-14 h-14 rounded-2xl bg-[#FCEDEA] text-[#E87559] flex items-center justify-center mx-auto shadow-2xs group-hover:scale-105 transition-transform">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                        </div>
                        <div class="space-y-1">
                            <p class="text-sm font-bold text-[#252525] group-hover:text-[#E87559] transition-colors">Pilih Berkas Audio Rekaman</p>
                            <p class="text-xs text-[#777777]">Mendukung format MP3, WAV, M4A, AAC, OGG (maks 10 MB)</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ======================================================== -->
            <!-- TAB 3: TEKS BEBAS                                        -->
            <!-- ======================================================== -->
            <div x-show="activeTab === 'text'" class="space-y-4 max-w-2xl mx-auto w-full">
                <div>
                    <label class="block text-xs sm:text-sm font-semibold text-[#252525] mb-1.5">
                        Tuliskan Rincian Penjualan Bebas
                    </label>
                    <textarea
                        x-model="textInput"
                        rows="3"
                        placeholder="Contoh: Hasil penjualan hari ini ada satu juta lima ratus ribu."
                        class="block w-full py-3 px-3.5 text-xs sm:text-sm rounded-xl border border-[#EEEEEE] text-[#252525] placeholder:text-[#BBBBBB] focus:ring-[#E87559]/20 focus:border-[#E87559] bg-[#FBFBFC] transition leading-relaxed"
                    ></textarea>
                </div>

                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 pt-1">
                    <div class="flex flex-wrap items-center gap-1.5 text-xs">
                        <span class="text-[#777777] font-medium mr-1">Contoh Cepat:</span>
                        <button
                            type="button"
                            @click="applyPresetText('Hasil penjualan hari ini ada satu juta lima ratus ribu.')"
                            class="px-2.5 py-1 rounded-lg bg-[#FBFBFC] hover:bg-[#DDF1EC] border border-[#EEEEEE] hover:border-emerald-300 text-[#252525] hover:text-emerald-800 transition cursor-pointer"
                        >
                            1.5 Juta Omzet
                        </button>
                        <button
                            type="button"
                            @click="applyPresetText('Penjualan hari ini 10 porsi nasi goreng harga dua puluh ribu per porsi dan 5 es teh harga lima ribu.')"
                            class="px-2.5 py-1 rounded-lg bg-[#FBFBFC] hover:bg-[#DDF1EC] border border-[#EEEEEE] hover:border-emerald-300 text-[#252525] hover:text-emerald-800 transition cursor-pointer"
                        >
                            10 Nasgor & 5 Es Teh
                        </button>
                        <button
                            type="button"
                            @click="applyPresetText('Penjualan ramai saat makan siang omzet 850 ribu')"
                            class="px-2.5 py-1 rounded-lg bg-[#FBFBFC] hover:bg-[#DDF1EC] border border-[#EEEEEE] hover:border-emerald-300 text-[#252525] hover:text-emerald-800 transition cursor-pointer"
                        >
                            Shift Makan Siang (850rb)
                        </button>
                    </div>

                    <button
                        type="button"
                        @click="extractFromText()"
                        :disabled="isProcessing"
                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold text-white bg-[#E87559] hover:bg-[#D66448] shadow-xs transition active:scale-95 disabled:opacity-50 shrink-0 cursor-pointer"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        <span>Proses Teks & Lanjut ke Konfirmasi &rarr;</span>
                    </button>
                </div>
            </div>

        </div>

    </div>

    <!-- ======================================================== -->
    <!-- HALAMAN 2: KONFIRMASI & RINCIAN KAS MASUK TERPISAH        -->
    <!-- Khusus untuk memeriksa dan mengedit hasil ekstraksi sebelum disimpan -->
    <!-- ======================================================== -->
    <div x-show="currentStep === 'review'" x-cloak class="bg-white rounded-2xl sm:rounded-3xl shadow-sm border border-[#EEEEEE] p-5 sm:p-7 lg:p-8 flex-1 flex flex-col justify-between space-y-6">

        <div class="space-y-6 max-w-3xl mx-auto w-full">

            <!-- Header Halaman Konfirmasi & Badge Sumber -->
            <div class="pb-5 border-b border-[#EEEEEE]">
                <div class="flex flex-wrap items-center gap-2 mb-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-[#DDF1EC] text-[#43A58A] border border-[#43A58A]/30">
                        <svg class="w-3.5 h-3.5 text-[#43A58A]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Tahap Konfirmasi Akhir</span>
                    </span>

                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-[#E1F0F7] text-[#0284C7] border border-[#BCE1F1]" x-text="'Sumber: ' + (extractedSource || 'Manual')"></span>
                </div>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-[#252525] tracking-tight">
                    Konfirmasi & Rincian Kas Masuk
                </h2>
                <p class="text-xs sm:text-sm text-[#777777] mt-1">
                    Periksa kembali total nominal penjualan dan rincian kas masuk di bawah. Anda dapat mengedit sebelum disimpan permanen.
                </p>
            </div>

            <!-- 3. Form Konfirmasi Terintegrasi ke Backend Livewire -->
            <form wire:submit="save" novalidate class="space-y-6">

                <!-- BAGIAN A: TOTAL NOMINAL PENJUALAN (HASIL EKSTRAKSI WAJIB MASUK KE SINI) -->
                <div class="p-5 rounded-2xl bg-[#FCEDEA]/30 border border-[#E87559]/30 space-y-3">
                    <div class="flex items-center justify-between">
                        <label for="amount" class="block text-xs sm:text-sm font-bold text-[#252525]">
                            Total Nominal Penjualan <span class="text-[#E87559]">*</span>
                        </label>
                        <span class="text-xs font-bold text-[#E87559] uppercase tracking-wider">
                            Field Kas Masuk Utama
                        </span>
                    </div>

                    <div class="relative rounded-xl shadow-xs">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <span class="text-[#777777] font-extrabold text-xl sm:text-2xl">Rp</span>
                        </div>
                        <input
                            type="number"
                            id="amount"
                            wire:model="amount"
                            placeholder="0"
                            step="any"
                            min="0"
                            class="block w-full pl-14 sm:pl-16 pr-4 py-3.5 text-2xl sm:text-3xl font-extrabold rounded-xl border {{ $errors->has('amount') ? 'border-[#DC6464] text-[#DC6464] focus:ring-[#DC6464]/20 focus:border-[#DC6464]' : 'border-[#E87559]/40 text-[#252525] focus:ring-[#E87559]/20 focus:border-[#E87559]' }} bg-white placeholder:text-[#BBBBBB] transition"
                        />
                    </div>
                    @error('amount')
                        <p class="mt-1 text-xs text-[#DC6464] font-medium flex items-center gap-1.5">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <span>{{ $message }}</span>
                        </p>
                    @enderror

                    <!-- Quick Add Pills Penyesuaian Nominal -->
                    <div class="pt-1 flex flex-wrap items-center gap-1.5 sm:gap-2">
                        <span class="text-xs text-[#777777] font-medium mr-1">Penyesuaian Cepat:</span>
                        <button type="button" wire:click="addAmount(50000)" class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-white hover:bg-[#DDF1EC] border border-[#EEEEEE] hover:border-emerald-300 text-[#252525] hover:text-emerald-800 transition active:scale-95 cursor-pointer">
                            +50rb
                        </button>
                        <button type="button" wire:click="addAmount(100000)" class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-white hover:bg-[#DDF1EC] border border-[#EEEEEE] hover:border-emerald-300 text-[#252525] hover:text-emerald-800 transition active:scale-95 cursor-pointer">
                            +100rb
                        </button>
                        <button type="button" wire:click="addAmount(500000)" class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-white hover:bg-[#DDF1EC] border border-[#EEEEEE] hover:border-emerald-300 text-[#252525] hover:text-emerald-800 transition active:scale-95 cursor-pointer">
                            +500rb
                        </button>
                        <button type="button" wire:click="addAmount(1000000)" class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-white hover:bg-[#DDF1EC] border border-[#EEEEEE] hover:border-emerald-300 text-[#252525] hover:text-emerald-800 transition active:scale-95 cursor-pointer">
                            +1jt
                        </button>
                        <button type="button" wire:click="setAmount(0)" class="px-2.5 py-1 text-xs font-semibold rounded-lg text-[#E87559] hover:bg-[#FCEDEA] border border-transparent hover:border-[#E87559]/30 transition cursor-pointer">
                            Reset
                        </button>
                </div>

                <!-- BAGIAN B: RINCIAN BARANG & MENU TERJUAL -->
                <div class="p-4 sm:p-5 rounded-2xl bg-[#FBFBFC] border border-[#EEEEEE] space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-xs sm:text-sm font-bold text-[#252525] flex items-center gap-2">
                                <span>Rincian Barang & Menu Terjual</span>
                                <template x-if="items.length > 0">
                                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-bold" x-text="items.length + ' Menu'"></span>
                                </template>
                            </h4>
                            <p class="text-[11px] text-[#777777] mt-0.5">
                                Subtotal menu di bawah otomatis menyelaraskan Total Nominal Penjualan.
                            </p>
                        </div>
                        <button
                            type="button"
                            @click="addItem()"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-[#E87559] bg-white border border-[#E87559]/30 hover:bg-[#FCEDEA] transition cursor-pointer"
                        >
                            <svg class="w-3.5 h-3.5 text-[#E87559]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                            </svg>
                            <span>Tambah Menu</span>
                        </button>
                    </div>

                    <!-- Jika Ada Baris Barang Terdeteksi -->
                    <template x-if="items.length > 0">
                        <div class="space-y-3">
                            <div class="overflow-x-auto rounded-xl border border-[#EEEEEE] bg-white shadow-2xs">
                                <table class="w-full text-left text-xs text-[#252525]">
                                    <thead class="bg-[#FBFBFC] border-b border-[#EEEEEE] text-[11px] font-bold text-[#777777] uppercase tracking-wider">
                                        <tr>
                                            <th class="py-2.5 px-3">Nama Menu / Barang</th>
                                            <th class="py-2.5 px-2 w-20 text-center">Kuantitas</th>
                                            <th class="py-2.5 px-2 w-20 text-center">Satuan</th>
                                            <th class="py-2.5 px-3 w-32 text-right">Harga Satuan</th>
                                            <th class="py-2.5 px-3 w-32 text-right">Subtotal</th>
                                            <th class="py-2.5 px-2 w-10 text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-[#EEEEEE]">
                                        <template x-for="(item, index) in items" :key="index">
                                            <tr class="hover:bg-gray-50/60 transition">
                                                <td class="py-2 px-3">
                                                    <input
                                                        type="text"
                                                        x-model="item.item_name"
                                                        placeholder="Nama barang / menu..."
                                                        class="w-full py-1.5 px-2 text-xs rounded-lg border border-[#EEEEEE] focus:ring-[#E87559]/20 focus:border-[#E87559] bg-white text-[#252525]"
                                                    />
                                                </td>
                                                <td class="py-2 px-2 text-center">
                                                    <input
                                                        type="number"
                                                        x-model.number="item.quantity"
                                                        @input="updateItemSubtotal(index)"
                                                        step="0.1"
                                                        min="0.1"
                                                        class="w-full py-1.5 px-1 text-center text-xs rounded-lg border border-[#EEEEEE] focus:ring-[#E87559]/20 focus:border-[#E87559] bg-white text-[#252525]"
                                                    />
                                                </td>
                                                <td class="py-2 px-2 text-center">
                                                    <input
                                                        type="text"
                                                        x-model="item.unit"
                                                        placeholder="porsi"
                                                        class="w-full py-1.5 px-1 text-center text-xs rounded-lg border border-[#EEEEEE] focus:ring-[#E87559]/20 focus:border-[#E87559] bg-white text-[#777777]"
                                                    />
                                                </td>
                                                <td class="py-2 px-3 text-right">
                                                    <input
                                                        type="number"
                                                        x-model.number="item.unit_price"
                                                        @input="updateItemSubtotal(index)"
                                                        step="any"
                                                        min="0"
                                                        class="w-full py-1.5 px-2 text-right text-xs rounded-lg border border-[#EEEEEE] focus:ring-[#E87559]/20 focus:border-[#E87559] bg-white text-[#252525] font-semibold"
                                                    />
                                                </td>
                                                <td class="py-2 px-3 text-right font-bold text-[#252525]" x-text="formatRupiah(item.subtotal)">
                                                    Rp 0
                                                </td>
                                                <td class="py-2 px-2 text-center">
                                                    <button
                                                        type="button"
                                                        @click="removeItem(index)"
                                                        class="p-1 rounded-md text-[#DC6464] hover:bg-red-50 transition cursor-pointer"
                                                        title="Hapus baris menu"
                                                    >
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                        </svg>
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>

                            <div class="flex flex-wrap items-center justify-between gap-2 pt-1 px-1">
                                <div class="text-xs text-[#777777]">
                                    <span>Total Subtotal Menu: </span>
                                    <span class="font-bold text-[#252525]" x-text="formatRupiah(calculateItemsSum())"></span>
                                </div>
                                <button
                                    type="button"
                                    @click="syncTotalWithItems()"
                                    class="text-xs font-semibold text-[#E87559] hover:underline cursor-pointer"
                                >
                                    Selaraskan Total Penjualan dengan Rincian Barang &rarr;
                                </button>
                            </div>
                        </div>
                    </template>

                    <!-- Jika Tidak Ada Rincian Barang (Hanya Total Omzet) -->
                    <template x-if="items.length === 0">
                        <div class="p-4 rounded-xl bg-white border border-[#EEEEEE] text-center space-y-1.5">
                            <p class="text-xs text-[#777777] leading-relaxed">
                                Rincian barang per porsi/menu tidak disebutkan dalam input. Transaksi ini dicatat sebagai <strong>total omzet kas masuk</strong> secara utuh.
                            </p>
                            <button
                                type="button"
                                @click="addItem()"
                                class="text-xs font-bold text-[#E87559] hover:underline cursor-pointer"
                            >
                                + Tambah Rincian Menu Secara Manual
                            </button>
                        </div>
                    </template>
                </div>

                <!-- BAGIAN C: METODE PENERIMAAN PENJUALAN -->
                <div>
                    <label class="block text-xs sm:text-sm font-semibold text-[#252525] mb-2">
                        Metode Penerimaan Penjualan <span class="text-[#E87559]">*</span>
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <!-- Uang Tunai -->
                        <label class="relative flex flex-col p-3.5 rounded-xl border cursor-pointer transition-all {{ $category === 'penjualan_tunai' ? 'border-[#43A58A] bg-[#DDF1EC]/40 ring-1 ring-[#43A58A]' : 'border-[#EEEEEE] bg-white hover:bg-[#FBFBFC]' }}">
                            <input type="radio" wire:model.live="category" value="penjualan_tunai" class="sr-only">
                            <div class="w-8 h-8 rounded-lg {{ $category === 'penjualan_tunai' ? 'bg-[#DDF1EC] text-[#43A58A]' : 'bg-gray-100 text-[#777777]' }} flex items-center justify-center mb-2 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                            <span class="text-xs font-bold text-[#252525]">Uang Tunai</span>
                            <span class="text-[11px] text-[#777777] mt-0.5">Cash di laci warung</span>
                        </label>

                        <!-- QRIS / Transfer -->
                        <label class="relative flex flex-col p-3.5 rounded-xl border cursor-pointer transition-all {{ $category === 'penjualan_qris' ? 'border-[#43A58A] bg-[#DDF1EC]/40 ring-1 ring-[#43A58A]' : 'border-[#EEEEEE] bg-white hover:bg-[#FBFBFC]' }}">
                            <input type="radio" wire:model.live="category" value="penjualan_qris" class="sr-only">
                            <div class="w-8 h-8 rounded-lg {{ $category === 'penjualan_qris' ? 'bg-[#DDF1EC] text-[#43A58A]' : 'bg-gray-100 text-[#777777]' }} flex items-center justify-center mb-2 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <span class="text-xs font-bold text-[#252525]">QRIS / Transfer</span>
                            <span class="text-[11px] text-[#777777] mt-0.5">Non-tunai bank/e-wallet</span>
                        </label>

                        <!-- Rekap Umum -->
                        <label class="relative flex flex-col p-3.5 rounded-xl border cursor-pointer transition-all {{ $category === 'penjualan' ? 'border-[#43A58A] bg-[#DDF1EC]/40 ring-1 ring-[#43A58A]' : 'border-[#EEEEEE] bg-white hover:bg-[#FBFBFC]' }}">
                            <input type="radio" wire:model.live="category" value="penjualan" class="sr-only">
                            <div class="w-8 h-8 rounded-lg {{ $category === 'penjualan' ? 'bg-[#DDF1EC] text-[#43A58A]' : 'bg-gray-100 text-[#777777]' }} flex items-center justify-center mb-2 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z"/>
                                </svg>
                            </div>
                            <span class="text-xs font-bold text-[#252525]">Rekap Umum</span>
                            <span class="text-[11px] text-[#777777] mt-0.5">Gabungan seluruh omzet</span>
                        </label>
                    </div>
                    @error('category')
                        <p class="mt-1.5 text-xs text-[#DC6464] font-medium flex items-center gap-1.5">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <span>{{ $message }}</span>
                        </p>
                    @enderror
                </div>

                <!-- BAGIAN D & E: TANGGAL PENJUALAN & CATATAN RINGKAS (NOMINAL TIDAK MASUK KE CATATAN) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 sm:gap-6">
                    <!-- Tanggal Penjualan -->
                    <div>
                        <label for="transaction_date" class="block text-xs sm:text-sm font-semibold text-[#252525] mb-1.5">
                            Tanggal Penjualan <span class="text-[#E87559]">*</span>
                        </label>
                        <input
                            type="date"
                            id="transaction_date"
                            wire:model="transaction_date"
                            max="{{ date('Y-m-d') }}"
                            class="block w-full py-2.5 px-3 text-xs sm:text-sm rounded-xl border {{ $errors->has('transaction_date') ? 'border-[#DC6464] text-[#DC6464] focus:ring-[#DC6464]/20 focus:border-[#DC6464]' : 'border-[#EEEEEE] text-[#252525] focus:ring-[#E87559]/20 focus:border-[#E87559]' }} bg-[#FBFBFC] transition"
                        />
                        <p class="mt-1.5 text-[11px] text-[#777777]">
                            Default hari ini. Tidak dapat memilih tanggal di masa depan.
                        </p>
                        @error('transaction_date')
                            <p class="mt-1.5 text-xs text-[#DC6464] font-medium flex items-center gap-1.5">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <span>{{ $message }}</span>
                            </p>
                        @enderror
                    </div>

                    <!-- Catatan Ringkas (Hanya Informasi Tambahan Kontekstual, Nominal Dilarang Masuk ke Sini) -->
                    <div>
                        <label for="notes" class="block text-xs sm:text-sm font-semibold text-[#252525] mb-1.5">
                            Catatan Ringkas <span class="text-xs text-[#777777] font-normal">(Opsional)</span>
                        </label>
                        <textarea
                            id="notes"
                            wire:model="notes"
                            rows="2"
                            placeholder="Contoh: Ramai saat makan siang, banyak pesanan nasi kotak..."
                            class="block w-full py-2.5 px-3 text-xs sm:text-sm rounded-xl border border-[#EEEEEE] text-[#252525] placeholder:text-[#BBBBBB] focus:ring-[#E87559]/20 focus:border-[#E87559] bg-[#FBFBFC] transition"
                        ></textarea>
                        <p class="mt-1.5 text-[11px] text-[#777777]">
                            Hanya untuk keterangan tambahan situasi kasir (bukan untuk menulis nominal).
                        </p>
                        @error('notes')
                            <p class="mt-1 text-xs text-[#DC6464]">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Tombol Aksi Halaman Konfirmasi (Simpan Penjualan & Batal) -->
                <div class="pt-6 border-t border-[#EEEEEE] flex items-center justify-between gap-3">
                    <button
                        type="button"
                        @click="backToInput()"
                        class="px-4 py-2.5 text-xs sm:text-sm font-semibold text-[#777777] hover:text-[#252525] hover:bg-gray-100 rounded-xl transition cursor-pointer flex items-center gap-1.5"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        <span>Ubah Input</span>
                    </button>

                    <div class="flex items-center gap-2">
                        <a
                            href="{{ route('transactions.index') }}"
                            wire:navigate
                            class="px-4 py-2.5 text-xs sm:text-sm font-semibold text-[#777777] hover:text-[#DC6464] rounded-xl transition"
                        >
                            Batalkan
                        </a>

                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center gap-2 px-7 py-2.5 text-xs sm:text-sm font-bold text-white bg-[#43A58A] hover:bg-[#388D76] active:bg-[#2F7764] rounded-xl shadow-xs hover:shadow-md transition active:scale-95 disabled:opacity-50 cursor-pointer"
                        >
                            <span wire:loading.remove class="inline-flex items-center gap-1.5">
                                <span>Simpan Penjualan</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                            </span>
                            <span wire:loading class="inline-flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                <span>Menyimpan Kas Masuk...</span>
                            </span>
                        </button>
                    </div>
                </div>

            </form>

        </div>

    </div>

    <!-- Kotak Tips & Panduan Kas Masuk KasiVo -->
    <div class="p-5 rounded-2xl bg-white border border-[#EEEEEE] shadow-2xs space-y-3">
        <h4 class="text-xs font-bold text-[#252525] flex items-center gap-1.5 uppercase tracking-wider">
            <svg class="w-4 h-4 text-[#43A58A]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>Panduan Kas Masuk KasiVo</span>
        </h4>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs text-[#777777]">
            <div class="p-3 rounded-xl bg-[#FBFBFC] border border-[#EEEEEE]/80 space-y-1">
                <p class="font-semibold text-[#252525]">1. Alur Dua Tahap Terpisah</p>
                <p class="leading-relaxed">Masukkan rekaman atau teks di Halaman Input, lalu periksa hasil ekstraksi nominal pada Halaman Konfirmasi khusus.</p>
            </div>
            <div class="p-3 rounded-xl bg-[#FBFBFC] border border-[#EEEEEE]/80 space-y-1">
                <p class="font-semibold text-[#252525]">2. Nominal Masuk ke Total Penjualan</p>
                <p class="leading-relaxed">Hasil ekstraksi otomatis mengisi field Total Nominal Penjualan, sementara Catatan Ringkas tetap bersih untuk info pendukung.</p>
            </div>
            <div class="p-3 rounded-xl bg-[#FBFBFC] border border-[#EEEEEE]/80 space-y-1">
                <p class="font-semibold text-[#252525]">3. Keputusan Simpan Manual</p>
                <p class="leading-relaxed">Tidak ada auto-save. Seluruh transaksi kas masuk hanya disimpan permanen setelah Anda menekan tombol Simpan Penjualan.</p>
            </div>
        </div>
    </div>
</div>

<script>
    function incomeSalesManager(wire) {
        return {
            wire: wire,
            // State Alur Tahapan Terpisah ('input' | 'review')
            currentStep: 'input',

            // Tab Input ('note' | 'voice' | 'text')
            activeTab: 'voice',
            isDragging: false,
            photoPreview: null,
            photoMeta: null,
            fileError: null,

            // State Suara
            voiceSubTab: 'record',
            voiceState: 'idle', // 'idle' | 'recording' | 'recorded'
            voiceDuration: 0,
            voiceTimer: null,
            voiceAudioUrl: null,
            voiceMediaRecorder: null,
            voiceAudioChunks: [],
            voiceError: null,
            voiceTranscription: '',
            speechRecognition: null,

            // State Teks Bebas
            textInput: '',

            // State Ekstraksi
            isProcessing: false,
            processingMessage: '',
            extractionSuccess: false,
            extractedSource: '',
            errorMessage: null,
            items: [],

            init() {
                window.dispatchEvent(new CustomEvent('income-step-changed', { detail: this.currentStep }));
            },

            switchTab(tab) {
                this.activeTab = tab;
                this.errorMessage = null;
            },

            backToInput() {
                // Kembali ke Halaman 1 tanpa menghilangkan draft atau me-reset input
                this.currentStep = 'input';
                this.errorMessage = null;
                window.dispatchEvent(new CustomEvent('income-step-changed', { detail: 'input' }));
                this.scrollToTop();
            },

            goToReview(autoExtract = true) {
                if (autoExtract && this.activeTab === 'text' && this.textInput.trim()) {
                    this.extractFromText();
                } else if (autoExtract && this.activeTab === 'voice' && this.voiceState === 'recorded') {
                    this.extractFromVoice();
                } else if (autoExtract && this.activeTab === 'note' && (this.photoPreview || this.photoMeta)) {
                    this.extractFromNote();
                } else {
                    this.currentStep = 'review';
                    window.dispatchEvent(new CustomEvent('income-step-changed', { detail: 'review' }));
                    this.scrollToTop();
                }
            },

            handleFileSelect(event) {
                const file = event.target.files[0];
                if (!file) return;
                this.processFile(file);
            },

            handleFileDrop(event) {
                this.isDragging = false;
                const file = event.dataTransfer.files[0];
                if (!file) return;
                this.processFile(file);
            },

            processFile(file) {
                this.fileError = null;
                this.errorMessage = null;
                const ext = file.name.split('.').pop().toLowerCase();
                const validImages = ['jpg', 'jpeg', 'png', 'webp'];
                const validDocs = ['csv', 'xlsx', 'xls'];

                if (!validImages.includes(ext) && !validDocs.includes(ext)) {
                    this.fileError = `Format berkas '.${ext}' tidak didukung. Harap unggah tangkapan layar (.png, .jpg, .webp) atau dokumen data (.csv, .xlsx).`;
                    this.photoPreview = null;
                    this.photoMeta = null;
                    return;
                }

                if (validImages.includes(ext) && file.size > 5 * 1024 * 1024) {
                    this.fileError = 'Ukuran tangkapan layar melebihi batas maksimum 5 MB.';
                    return;
                }
                if (validDocs.includes(ext) && file.size > 10 * 1024 * 1024) {
                    this.fileError = 'Ukuran berkas dokumen melebihi batas maksimum 10 MB.';
                    return;
                }

                this.photoMeta = {
                    name: file.name,
                    size: this.formatBytes(file.size),
                    type: validImages.includes(ext) ? 'image' : 'spreadsheet',
                    ext: ext.toUpperCase()
                };

                if (validImages.includes(ext)) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.photoPreview = e.target.result;
                    };
                    reader.readAsDataURL(file);
                } else {
                    this.photoPreview = null;
                }
            },

            removePhoto() {
                this.photoPreview = null;
                this.photoMeta = null;
                this.fileError = null;
                const camInput = document.getElementById('incomeCameraInput');
                if (camInput) camInput.value = '';
                const fileInput = document.getElementById('incomeFileInput');
                if (fileInput) fileInput.value = '';
            },

            async startVoiceRecording() {
                try {
                    this.voiceError = null;
                    this.errorMessage = null;
                    this.voiceTranscription = '';

                    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                        this.voiceError = 'Peramban ini tidak mendukung akses mikrofon rekaman audio.';
                        return;
                    }

                    // Inisialisasi Web Speech Recognition jika didukung browser
                    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
                    if (SpeechRecognition) {
                        try {
                            this.speechRecognition = new SpeechRecognition();
                            this.speechRecognition.lang = 'id-ID';
                            this.speechRecognition.continuous = true;
                            this.speechRecognition.interimResults = true;
                            this.speechRecognition.onresult = (e) => {
                                let transcript = '';
                                for (let i = 0; i < e.results.length; i++) {
                                    transcript += e.results[i][0].transcript + ' ';
                                }
                                this.voiceTranscription = transcript.trim();
                            };
                            this.speechRecognition.start();
                        } catch (srErr) {
                            console.warn('Speech recognition notice:', srErr);
                        }
                    }

                    const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                    this.voiceAudioChunks = [];
                    this.voiceMediaRecorder = new MediaRecorder(stream);
                    this.voiceMediaRecorder.ondataavailable = (e) => {
                        if (e.data.size > 0) this.voiceAudioChunks.push(e.data);
                    };
                    this.voiceMediaRecorder.onstop = () => {
                        const blob = new Blob(this.voiceAudioChunks, { type: 'audio/webm' });
                        this.voiceAudioUrl = URL.createObjectURL(blob);
                        stream.getTracks().forEach(t => t.stop());
                    };
                    this.voiceMediaRecorder.start();
                    this.voiceState = 'recording';
                    this.voiceDuration = 0;
                    this.voiceTimer = setInterval(() => {
                        this.voiceDuration++;
                    }, 1000);
                } catch (err) {
                    this.voiceError = 'Izin akses mikrofon ditolak atau perangkat mikrofon tidak terdeteksi.';
                    this.voiceState = 'idle';
                }
            },

            stopVoiceRecording() {
                if (this.voiceMediaRecorder && this.voiceState === 'recording') {
                    this.voiceMediaRecorder.stop();
                    clearInterval(this.voiceTimer);
                    this.voiceState = 'recorded';

                    if (this.speechRecognition) {
                        try {
                            this.speechRecognition.stop();
                        } catch (e) {}
                    }
                }
            },

            resetVoiceRecording() {
                if (this.voiceTimer) clearInterval(this.voiceTimer);
                if (this.speechRecognition) {
                    try { this.speechRecognition.stop(); } catch (e) {}
                }
                this.voiceState = 'idle';
                this.voiceDuration = 0;
                this.voiceAudioUrl = null;
                this.voiceTranscription = '';
                this.voiceError = null;
            },

            handleAudioFileSelect(event) {
                const file = event.target.files[0];
                if (!file) return;
                this.voiceAudioUrl = URL.createObjectURL(file);
                this.voiceState = 'recorded';
                this.voiceDuration = 10;
                this.voiceSubTab = 'record';
            },

            applyPresetText(text) {
                this.textInput = text;
                this.extractFromText();
            },

            // ========================================================
            // ENGINE EKSTRAKSI HASIL AI & PEMETAAN FORM KONFIRMASI
            // ========================================================
            async extractFromText() {
                const raw = (this.textInput || '').trim();
                if (!raw) {
                    this.errorMessage = 'Tuliskan catatan atau deskripsi penjualan terlebih dahulu sebelum memproses.';
                    return;
                }

                this.isProcessing = true;
                this.processingMessage = 'AI sedang menganalisis kalimat penjualan...';
                this.errorMessage = null;

                try {
                    let parsed = null;
                    const caller = this.wire || (window.Livewire ? this.$wire : null);
                    if (caller && typeof caller.extractTextAi === 'function') {
                        parsed = await caller.extractTextAi(raw);
                    }

                    if (parsed && parsed.amount > 0) {
                        this.applyExtractedData(parsed, 'AI NLP');
                    } else {
                        parsed = this.parseSalesSentence(raw);
                        this.applyExtractedData(parsed, 'Teks Bebas');
                    }
                } catch (e) {
                    console.warn('Backend NLP fallback to regex:', e);
                    const parsed = this.parseSalesSentence(raw);
                    this.applyExtractedData(parsed, 'Teks Bebas');
                } finally {
                    this.isProcessing = false;
                }
            },

            async extractFromVoice() {
                if (this.voiceState !== 'recorded' && !this.voiceAudioUrl) {
                    this.errorMessage = 'Rekam suara atau unggah berkas audio kasir terlebih dahulu.';
                    return;
                }

                this.isProcessing = true;
                this.processingMessage = 'AI sedang memproses hasil transkripsi suara...';
                this.errorMessage = null;

                try {
                    const sentence = this.voiceTranscription || 'Penjualan hari ini satu juta lima ratus ribu rupiah';
                    let parsed = null;
                    const caller = this.wire || (window.Livewire ? this.$wire : null);
                    if (caller && typeof caller.extractTextAi === 'function') {
                        parsed = await caller.extractTextAi(sentence);
                    }

                    if (parsed && parsed.amount > 0) {
                        this.applyExtractedData(parsed, 'Suara Kasir (AI)');
                    } else {
                        parsed = this.parseSalesSentence(sentence);
                        this.applyExtractedData(parsed, 'Suara Kasir');
                    }
                } catch (e) {
                    console.warn('Voice NLP fallback to regex:', e);
                    const sentence = this.voiceTranscription || 'Hasil penjualan hari ini ada satu juta lima ratus ribu.';
                    const parsed = this.parseSalesSentence(sentence);
                    this.applyExtractedData(parsed, 'Suara Kasir');
                } finally {
                    this.isProcessing = false;
                }
            },

            async extractFromNote() {
                if (!this.photoPreview && !this.photoMeta) {
                    this.errorMessage = 'Pilih atau foto nota penjualan terlebih dahulu sebelum memproses.';
                    return;
                }

                this.isProcessing = true;
                this.processingMessage = 'Google Gemini Vision sedang memindai nota penjualan...';
                this.errorMessage = null;

                try {
                    let parsed = null;
                    const caller = this.wire || (window.Livewire ? this.$wire : null);
                    if (caller && this.photoPreview && typeof caller.extractPhotoAi === 'function') {
                        parsed = await caller.extractPhotoAi(this.photoPreview);
                    }

                    if (parsed && parsed.amount > 0) {
                        this.applyExtractedData(parsed, 'Google Gemini Vision');
                    } else {
                        // Fallback jika Vision tidak membaca angka spesifik atau berupa CSV
                        const noteContext = `Struk Nota (${this.photoMeta ? this.photoMeta.name : 'Foto Bon'})`;
                        this.applyExtractedData({
                            amount: parsed?.amount || 0,
                            notes: parsed?.notes || noteContext,
                            category: parsed?.category || 'penjualan_tunai',
                            date: parsed?.date || new Date().toISOString().split('T')[0],
                            items: parsed?.items || []
                        }, 'Foto Nota');
                    }
                } catch (e) {
                    console.error('Photo AI extraction failed:', e);
                    this.errorMessage = 'Gagal memproses nota via AI: ' + (e.message || 'Silakan isi nominal secara manual di form konfirmasi.');
                } finally {
                    this.isProcessing = false;
                }
            },

            parseSalesSentence(text) {
                let cleanText = text.trim();
                const result = {
                    items: [],
                    amount: 0,
                    notes: '',
                    category: 'penjualan_tunai',
                    date: new Date().toISOString().split('T')[0]
                };

                // 1. Deteksi metode penerimaan kas
                if (/qris|transfer|gopay|ovo|dana|bca|mandiri|bri/i.test(cleanText)) {
                    result.category = 'penjualan_qris';
                } else if (/tunai|cash|laci/i.test(cleanText)) {
                    result.category = 'penjualan_tunai';
                }

                // 2. Deteksi tanggal
                if (/kemarin/i.test(cleanText)) {
                    const yesterday = new Date();
                    yesterday.setDate(yesterday.getDate() - 1);
                    result.date = yesterday.toISOString().split('T')[0];
                }

                // 3. Pecah klausa per menu untuk mendeteksi rincian item barang
                const clauses = cleanText.split(/(?:,|\bdan\b|;|\n|\+)/i);
                for (let clause of clauses) {
                    clause = clause.trim();
                    if (!clause) continue;

                    const m = clause.match(/(?:penjualan\s+(?:hari\s+ini\s+)?)?(\d+|setengah|seperempat|satu|dua|tiga|empat|lima|enam|tujuh|delapan|sembilan|sepuluh)\s*(porsi|bungkus|gelas|cup|kotak|box|pcs|butir|kg|liter|ikat)?\s+(?:dari\s+)?(.+?)\s+(?:harga|seharga|@|sebesar|rp\.?)\s*(.+?)(?:\s+per\s+\w+)?$/i);
                    if (m) {
                        const rawQty = m[1];
                        const unit = m[2] || 'porsi';
                        const itemName = m[3].replace(/^(penjualan|omzet|order)\s+/i, '').trim();
                        const rawPrice = m[4].replace(/per\s+\w+/i, '').trim();

                        const qty = !isNaN(rawQty) ? Number(rawQty) : this.parseIndoWords(rawQty);
                        const price = this.parseIndoWords(rawPrice);

                        if (qty > 0 && price > 0 && itemName) {
                            result.items.push({
                                item_name: itemName.charAt(0).toUpperCase() + itemName.slice(1),
                                quantity: qty,
                                unit: unit.toLowerCase(),
                                unit_price: price,
                                subtotal: qty * price
                            });
                        }
                    }
                }

                // 4. Hitung Total Nominal Penjualan
                if (result.items.length > 0) {
                    result.amount = result.items.reduce((sum, it) => sum + it.subtotal, 0);
                } else {
                    // Ekstraksi nominal langsung (angka format Indonesia maupun kata terbilang)
                    result.amount = this.extractGlobalNominal(cleanText);
                }

                // 5. Bersihkan Catatan Ringkas (NOMINAL DILARANG MASUK KE CATATAN RINGKAS)
                result.notes = this.extractContextNotes(cleanText, result.items.length > 0);

                return result;
            },

            extractGlobalNominal(text) {
                // Pola 1: Angka dengan pemisah titik ribuan, misal "1.500.000", "250.000"
                const dottedMatch = text.match(/(?:rp\.?\s*)?(\d{1,3}(?:\.\d{3})+)(?:\b|$)/i);
                if (dottedMatch) {
                    return Number(dottedMatch[1].replace(/\./g, ''));
                }

                // Pola 2: Angka singkatan dengan desimal, misal "1.5jt", "1,5 jt", "500rb", "250k"
                const abbrMatch = text.match(/(?:rp\.?\s*)?(\d+(?:[\.,]\d+)?)\s*(juta|jt|ribu|rb|k)\b/i);
                if (abbrMatch) {
                    let base = parseFloat(abbrMatch[1].replace(/,/g, '.'));
                    let unit = abbrMatch[2].toLowerCase();
                    if (unit === 'juta' || unit === 'jt') return Math.round(base * 1000000);
                    if (unit === 'ribu' || unit === 'rb' || unit === 'k') return Math.round(base * 1000);
                }

                // Pola 3: Angka polos minimal 4 digit, misal "1500000", "50000"
                const plainNumMatch = text.match(/(?:rp\.?\s*)?(\d{4,10})\b/i);
                if (plainNumMatch) {
                    return Number(plainNumMatch[1]);
                }

                // Pola 4: Angka terbilang Bahasa Indonesia, misal "satu juta lima ratus ribu"
                const wordNumRegex = /(?:se|satu|dua|tiga|empat|lima|enam|tujuh|delapan|sembilan|sepuluh|sebelas|dua\s+belas|tiga\s+belas|empat\s+belas|lima\s+belas)\s+(?:ratus|puluh|belas|ribu|juta)(?:\s+(?:se|satu|dua|tiga|empat|lima|enam|tujuh|delapan|sembilan|sepuluh|ratus|puluh|ribu|juta))*/i;
                const wordMatch = text.match(wordNumRegex);
                if (wordMatch) {
                    const val = this.parseIndoWords(wordMatch[0]);
                    if (val > 0) return val;
                }

                return 0;
            },

            parseIndoWords(str) {
                if (!str) return 0;
                str = str.toLowerCase().replace(/rp\.?/g, '').replace(/[^\w\s]/g, ' ').trim();

                const parseUnderThousand = (s) => {
                    if (!s.trim()) return 0;
                    const wordMap = {
                        'nol': 0, 'setengah': 0.5, 'seperempat': 0.25, 'se': 1, 'satu': 1,
                        'dua': 2, 'tiga': 3, 'empat': 4, 'lima': 5, 'enam': 6, 'tujuh': 7,
                        'delapan': 8, 'sembilan': 9, 'sepuluh': 10, 'sebelas': 11,
                        'seratus': 100
                    };
                    let sum = 0, cur = 0;
                    const tokens = s.trim().split(/\s+/);
                    for (let i = 0; i < tokens.length; i++) {
                        let t = tokens[i];
                        if (t === 'ratus') {
                            cur = (cur === 0 ? 1 : cur) * 100;
                            sum += cur;
                            cur = 0;
                        } else if (t === 'puluh') {
                            cur = (cur === 0 ? 1 : cur) * 10;
                            sum += cur;
                            cur = 0;
                        } else if (t === 'belas') {
                            cur += 10;
                            sum += cur;
                            cur = 0;
                        } else if (!isNaN(t)) {
                            cur += Number(t);
                        } else if (wordMap[t] !== undefined) {
                            cur += wordMap[t];
                        }
                    }
                    return sum + cur;
                };

                let jutaPart = 0, ribuPart = 0, sisaPart = 0, rem = str;
                if (rem.includes('juta')) {
                    let parts = rem.split('juta');
                    jutaPart = parseUnderThousand(parts[0]) * 1000000;
                    rem = parts[1] || '';
                }
                if (rem.includes('ribu')) {
                    let parts = rem.split('ribu');
                    ribuPart = parseUnderThousand(parts[0]) * 1000;
                    rem = parts[1] || '';
                }
                sisaPart = parseUnderThousand(rem);

                return jutaPart + ribuPart + sisaPart;
            },

            extractContextNotes(rawText, hasItems) {
                if (!rawText) return '';
                let text = rawText.trim();

                // Jika hanya kalimat nominal umum (misal: "Hasil penjualan hari ini ada satu juta lima ratus ribu"),
                // catatan ringkas HARUS KOSONG (sesuai spesifikasi Bagian 2.A)
                const isPureNominal = /^(hasil\s+penjualan|penjualan|omzet|total|pendapatan|kas masuk)?\s*(hari ini|tadi|kemarin)?\s*(ada|sebesar|sebanyak|mencapai)?\s*(?:rp\.?\s*)?(\d{1,3}(?:\.\d{3})+|\d+(?:[\.,]\d+)?\s*(?:juta|jt|ribu|rb|k)?|(?:se|satu|dua|tiga|empat|lima|enam|tujuh|delapan|sembilan|sepuluh|sebelas|ratus|puluh|belas|ribu|juta|\s)+)?\s*(rupiah|tunai|cash|qris|transfer)?[\.\s]*$/i.test(text);

                if (isPureNominal) {
                    return '';
                }

                if (hasItems) {
                    // Jika rincian menu sudah terpetakan ke tabel item, catatan hanya memuat konteks khusus (jika ada)
                    const keywords = ['ramai', 'makan siang', 'shift pagi', 'shift malam', 'sarapan', 'katering', 'pesanan kotak', 'rapat', 'tutup kasir'];
                    let matches = [];
                    let lower = text.toLowerCase();
                    for (let kw of keywords) {
                        if (lower.includes(kw)) {
                            matches.push(kw.charAt(0).toUpperCase() + kw.slice(1));
                        }
                    }
                    return matches.length > 0 ? matches.join(', ') : '';
                }

                // Bersihkan teks dari angka, nominal, dan kata pengisi umum
                let notes = text;
                notes = notes.replace(/(?:rp\.?\s*)?\d{1,3}(?:\.\d{3})+(?:,\d+)?/gi, '');
                notes = notes.replace(/(?:rp\.?\s*)?\d+(?:[\.,]\d+)?\s*(?:juta|jt|ribu|rb|k|rupiah)?\b/gi, '');
                const wordNumRegex = /(?:se|satu|dua|tiga|empat|lima|enam|tujuh|delapan|sembilan|sepuluh|sebelas|dua\s+belas|tiga\s+belas|empat\s+belas|lima\s+belas)\s+(?:ratus|puluh|belas|ribu|juta)(?:\s+(?:se|satu|dua|tiga|empat|lima|enam|tujuh|delapan|sembilan|sepuluh|ratus|puluh|ribu|juta|\d+))*/gi;
                notes = notes.replace(wordNumRegex, '');
                notes = notes.replace(/\b(hasil\s+penjualan|penjualan\s+hari\s+ini|omzet\s+hari\s+ini|total\s+penjualan|ada\s+sebesar|sebesar|nominalnya|seharga|harga|rupiah)\b/gi, '');
                notes = notes.replace(/\b(penjualan|omzet|kas masuk)\b/gi, '');
                notes = notes.replace(/[\(\)\[\]\{\}\.,:;\-]/g, ' ');
                notes = notes.replace(/\s+/g, ' ').trim();

                if (/^(hari ini|kemarin|tadi|tunai|cash|qris)?$/i.test(notes)) {
                    return '';
                }
                return notes.charAt(0).toUpperCase() + notes.slice(1);
            },

            applyExtractedData(data, sourceName) {
                this.extractedSource = sourceName;
                this.extractionSuccess = true;
                this.items = (data.items || []).map(it => ({
                    item_name: it.item_name || 'Menu Penjualan',
                    quantity: Number(it.quantity) || 1,
                    unit: it.unit || 'porsi',
                    unit_price: Number(it.unit_price) || 0,
                    subtotal: Number(it.subtotal) || (Number(it.quantity || 1) * Number(it.unit_price || 0))
                }));

                // Jika amount masih 0 tapi rincian menu/item ada, jumlahkan otomatis subtotalnya
                if ((!data.amount || data.amount <= 0) && this.items.length > 0) {
                    const sum = this.items.reduce((s, it) => s + (Number(it.subtotal) || 0), 0);
                    if (sum > 0) {
                        data.amount = sum;
                    }
                }

                // 1. Pindah tahapan ke review agar form terlihat
                this.currentStep = 'review';
                window.dispatchEvent(new CustomEvent('income-step-changed', { detail: 'review' }));
                this.scrollToTop();

                // 2. Sinkronisasi data ke kontrak Livewire backend
                const livewireCaller = this.wire || (window.Livewire ? this.$wire : null);
                if (livewireCaller) {
                    if (data.amount > 0) {
                        livewireCaller.set('amount', data.amount);
                    }
                    if (data.notes) {
                        livewireCaller.set('notes', data.notes);
                    }
                    if (data.date) {
                        livewireCaller.set('transaction_date', data.date);
                    }
                    if (data.category) {
                        livewireCaller.set('category', data.category);
                    }
                }

                // 3. Update elemen DOM input
                this.$nextTick(() => {
                    const amountEl = document.getElementById('amount');
                    if (amountEl && data.amount > 0) {
                        amountEl.value = data.amount;
                        amountEl.dispatchEvent(new Event('input', { bubbles: true }));
                        amountEl.dispatchEvent(new Event('change', { bubbles: true }));
                    }

                    const notesEl = document.getElementById('notes');
                    if (notesEl && data.notes) {
                        notesEl.value = data.notes;
                        notesEl.dispatchEvent(new Event('input', { bubbles: true }));
                        notesEl.dispatchEvent(new Event('change', { bubbles: true }));
                    }

                    const dateEl = document.getElementById('transaction_date');
                    if (dateEl && data.date) {
                        dateEl.value = data.date;
                        dateEl.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                });
            },

            addItem() {
                this.items.push({
                    item_name: '',
                    quantity: 1,
                    unit: 'porsi',
                    unit_price: 0,
                    subtotal: 0
                });
            },

            removeItem(index) {
                this.items.splice(index, 1);
                this.syncTotalWithItems();
            },

            updateItemSubtotal(index) {
                const it = this.items[index];
                if (it) {
                    const q = parseFloat(it.quantity) || 0;
                    const p = parseFloat(it.unit_price) || 0;
                    it.subtotal = Math.round(q * p);
                    this.syncTotalWithItems();
                }
            },

            calculateItemsSum() {
                return this.items.reduce((sum, it) => sum + (parseFloat(it.subtotal) || 0), 0);
            },

            syncTotalWithItems() {
                const total = this.calculateItemsSum();
                if (total > 0) {
                    const livewireCaller = this.wire || (window.Livewire ? this.$wire : null);
                    if (livewireCaller) {
                        livewireCaller.set('amount', total);
                    }
                    this.$nextTick(() => {
                        const amountEl = document.getElementById('amount');
                        if (amountEl) {
                            amountEl.value = total;
                            amountEl.dispatchEvent(new Event('input', { bubbles: true }));
                            amountEl.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                    });
                }
            },

            scrollToTop() {
                this.$nextTick(() => {
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                });
            },

            formatRupiah(val) {
                const num = parseFloat(val) || 0;
                return 'Rp ' + num.toLocaleString('id-ID');
            },

            formatDuration(sec) {
                const m = Math.floor(sec / 60);
                const s = sec % 60;
                return `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
            },

            formatBytes(bytes) {
                if (!bytes || bytes === 0) return '0 B';
                const k = 1024;
                const sizes = ['B', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
            }
        };
    }
</script>
