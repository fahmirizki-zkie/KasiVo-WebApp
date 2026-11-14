<div class="w-full flex-1 flex flex-col space-y-4" x-data="incomeRecorder()">
    <!-- Card Container Utama -->
    <div class="bg-white rounded-2xl sm:rounded-3xl shadow-sm border border-[#EEEEEE] p-5 sm:p-7 lg:p-8 flex-1 flex flex-col justify-between">

        <!-- 1. Header Halaman & Badge Kas Masuk -->
        <div class="mb-6 pb-5 border-b border-[#EEEEEE]">
            <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-[#DDF1EC] text-[#43A58A] border border-[#43A58A]/30 shadow-xs">
                    <svg class="w-3.5 h-3.5 text-[#43A58A]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Kas Masuk (Penjualan)</span>
                </span>

                <!-- Shortcut ke Impor Penjualan Digital (GoFood, Grab, Shopee, QRIS) -->
                <a href="{{ route('transactions.import-digital') }}"
                   wire:navigate
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold text-[#777777] hover:text-[#E87559] bg-[#FBFBFC] hover:bg-[#FCEDEA]/50 border border-[#EEEEEE] transition-all">
                    <svg class="w-3.5 h-3.5 text-[#777777]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    <span>Impor Digital (GoFood / Grab / Shopee)</span>
                </a>
            </div>

            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#252525] tracking-tight">
                Catat Penjualan Harian
            </h1>
            <p class="text-xs sm:text-sm text-[#777777] mt-1.5 leading-relaxed">
                Pilih salah satu metode pencatatan kas masuk: unggah/foto nota penjualan, rekam suara kasir, atau tulis deskripsi penjualan bebas.
            </p>
        </div>

        <!-- 2. Tab Pintas Navigasi 3 Moda Input Penjualan (Konsisten dengan Catat Pengeluaran) -->
        <div class="grid grid-cols-3 gap-1.5 p-1.5 rounded-2xl bg-gray-100/80 border border-[#EEEEEE] mb-6">
            <!-- Tab 1: Input Nota -->
            <button
                type="button"
                @click="activeTab = 'note'"
                :class="activeTab === 'note' ? 'bg-white text-[#E87559] font-bold shadow-xs border border-[#E87559]/20' : 'text-[#777777] hover:text-[#252525] hover:bg-white/60 font-semibold'"
                class="flex items-center justify-center gap-1.5 py-2.5 px-2 sm:px-3 rounded-xl text-xs sm:text-sm transition-all"
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
                @click="activeTab = 'voice'"
                :class="activeTab === 'voice' ? 'bg-white text-[#E87559] font-bold shadow-xs border border-[#E87559]/20' : 'text-[#777777] hover:text-[#252525] hover:bg-white/60 font-semibold'"
                class="flex items-center justify-center gap-1.5 py-2.5 px-2 sm:px-3 rounded-xl text-xs sm:text-sm transition-all"
            >
                <svg class="w-4 h-4" :class="activeTab === 'voice' ? 'text-[#E87559]' : 'text-[#777777]'" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                </svg>
                <span class="truncate">Suara</span>
            </button>

            <!-- Tab 3: Teks Bebas -->
            <button
                type="button"
                @click="activeTab = 'text'"
                :class="activeTab === 'text' ? 'bg-white text-[#E87559] font-bold shadow-xs border border-[#E87559]/20' : 'text-[#777777] hover:text-[#252525] hover:bg-white/60 font-semibold'"
                class="flex items-center justify-center gap-1.5 py-2.5 px-2 sm:px-3 rounded-xl text-xs sm:text-sm transition-all"
            >
                <svg class="w-4 h-4" :class="activeTab === 'text' ? 'text-[#E87559]' : 'text-[#777777]'" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span class="truncate">Teks Bebas</span>
            </button>
        </div>

        <!-- ======================================================== -->
        <!-- PANEL TAB 1: INPUT NOTA (KAMERA & UNGGAH BERKAS)         -->
        <!-- ======================================================== -->
        <div x-show="activeTab === 'note'" x-cloak class="space-y-5 mb-8">

            <!-- Hidden Inputs Kamera & File Browser -->
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

            <!-- Banner Notifikasi Error Format Tidak Didukung -->
            <template x-if="fileError">
                <div class="p-4 rounded-2xl bg-[#FFF5F5] border border-[#DC6464]/30 flex items-start gap-3 shadow-xs">
                    <div class="w-9 h-9 rounded-xl bg-[#FCEDEA] text-[#DC6464] flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="w-5 h-5 text-[#DC6464]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h4 class="text-xs sm:text-sm font-bold text-[#DC6464]">Berkas Tidak Dapat Diterima</h4>
                        <p class="text-xs text-[#DC6464]/90 mt-0.5 leading-relaxed" x-text="fileError"></p>
                    </div>
                    <button type="button" @click="fileError = null" class="p-1.5 rounded-lg text-[#DC6464] hover:bg-[#DC6464]/10 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </template>

            <!-- State 1: Belum Ada Berkas yang Dipilih -->
            <div x-show="!photoPreview && !photoMeta" class="space-y-4">
                <div
                    @dragover.prevent="isDragging = true"
                    @dragleave.prevent="isDragging = false"
                    @drop.prevent="handleFileDrop($event)"
                    :class="isDragging ? 'border-[#E87559] bg-[#FCEDEA]/20 scale-[1.01]' : 'border-[#EEEEEE] bg-[#FBFBFC]'"
                    class="p-6 sm:p-8 rounded-2xl border-2 border-dashed text-center transition-all"
                >
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Pilihan A: Ambil Foto dari Kamera Langsung -->
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
                                <p class="text-xs text-[#777777]">Ambil foto nota/bon penjualan langsung dengan kamera belakang ponsel</p>
                            </div>
                        </button>

                        <!-- Pilihan B: Unggah Berkas Gambar / Spreadsheet -->
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
                                <p class="text-xs text-[#777777]">Unggah berkas foto struk (<span class="font-semibold text-[#252525]">JPG, PNG, WEBP</span>) atau spreadsheet (<span class="font-semibold text-[#252525]">CSV, XLSX</span>)</p>
                            </div>
                        </button>
                    </div>

                    <div class="mt-4 pt-4 border-t border-[#EEEEEE]/80 flex flex-wrap items-center justify-center gap-2 text-[11px] text-[#777777]">
                        <span class="font-medium text-[#252525]">Format yang didukung:</span>
                        <span class="px-2 py-0.5 rounded-md bg-white border border-[#EEEEEE] font-semibold text-[#555555]">JPG, JPEG, PNG, WEBP (maks 5 MB)</span>
                        <span>&bull;</span>
                        <span class="px-2 py-0.5 rounded-md bg-white border border-[#EEEEEE] font-semibold text-[#555555]">CSV, XLSX, XLS (maks 10 MB)</span>
                    </div>
                </div>
            </div>

            <!-- State 2: Pratinjau Berkas Gambar / Foto Kamera -->
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
                <p class="text-[11px] text-[#777777] text-center">
                    Foto siap ditinjau. Isi rincian total nominal penjualan dan metode penerimaan pada form di bawah.
                </p>
            </div>

            <!-- State 3: Pratinjau Dokumen Spreadsheet (CSV / Excel) -->
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
                            onclick="document.getElementById('incomeFileInput').click()"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold text-[#777777] hover:text-[#252525] bg-white border border-[#EEEEEE] hover:bg-gray-50 transition"
                        >
                            Ganti Berkas
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
                <div class="p-3 rounded-xl bg-white/80 border border-[#BCE1F1]/60 text-xs text-[#0369A1] flex items-center justify-between">
                    <span>Dokumen tervalidasi. Untuk impor massal per baris otomatis, silakan gunakan modul Impor Digital.</span>
                    <a href="{{ route('transactions.import-digital') }}" wire:navigate class="font-bold underline ml-2 shrink-0">Buka Impor Digital &rarr;</a>
                </div>
            </div>

        </div>

        <!-- ======================================================== -->
        <!-- PANEL TAB 2: SUARA (REKAM SUARA & AUDIO UPLOAD)          -->
        <!-- ======================================================== -->
        <div x-show="activeTab === 'voice'" x-cloak class="space-y-6 mb-8">

            <!-- Switcher: Rekam Langsung vs Unggah Audio -->
            <div class="flex p-1 bg-[#FBFBFC] rounded-2xl border border-[#EEEEEE] max-w-lg mx-auto w-full">
                <button
                    type="button"
                    @click="voiceSubTab = 'record'"
                    :class="voiceSubTab === 'record' ? 'bg-white text-[#252525] shadow-xs font-bold' : 'text-[#777777] hover:text-[#252525] font-medium'"
                    class="flex-1 py-2 px-3 rounded-xl text-xs sm:text-sm flex items-center justify-center gap-2 transition-all"
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
                    class="flex-1 py-2 px-3 rounded-xl text-xs sm:text-sm flex items-center justify-center gap-2 transition-all"
                >
                    <svg class="w-4 h-4 text-[#777777]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    <span>Unggah Berkas Audio</span>
                </button>
            </div>

            <!-- Error Mikrofon -->
            <template x-if="voiceError">
                <div class="p-4 rounded-2xl bg-[#FFF5F5] border border-[#DC6464]/30 text-xs text-[#DC6464] flex items-center justify-between">
                    <span x-text="voiceError"></span>
                    <button type="button" @click="voiceError = null" class="font-bold underline ml-2">Tutup</button>
                </div>
            </template>

            <!-- SUBTAB 2A: REKAM LANGSUNG -->
            <div x-show="voiceSubTab === 'record'" class="text-center py-6 sm:py-8 space-y-5">
                <!-- State A: Idle -->
                <div x-show="voiceState === 'idle'" class="space-y-4">
                    <div class="relative inline-flex items-center justify-center">
                        <button
                            type="button"
                            @click="startVoiceRecording()"
                            class="w-24 h-24 sm:w-28 sm:h-28 rounded-full bg-[#E87559] hover:bg-[#D66448] text-white flex items-center justify-center shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105 active:scale-95 group focus:outline-hidden"
                            aria-label="Mulai merekam suara penjualan"
                        >
                            <svg class="w-10 h-10 sm:w-12 sm:h-12 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 18.5V22"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 22h8"/>
                            </svg>
                        </button>
                    </div>

                    <div>
                        <p class="text-base sm:text-lg font-bold text-[#252525]">Tekan untuk Mulai Merekam Penjualan</p>
                        <p class="text-xs text-[#777777] mt-1 max-w-sm mx-auto leading-relaxed">
                            Contoh: "Penjualan paket makan siang seratus delapan puluh ribu tunai sama es teh dua puluh ribu."
                        </p>
                    </div>
                </div>

                <!-- State B: Recording -->
                <div x-show="voiceState === 'recording'" class="space-y-5">
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-red-50 text-red-600 border border-red-200 text-xs font-semibold animate-pulse">
                        <span class="w-2 h-2 rounded-full bg-red-600"></span>
                        <span>Merekam Suara Penjualan...</span>
                        <span class="font-mono font-bold" x-text="formatDuration(voiceDuration)"></span>
                    </div>

                    <div class="flex items-center justify-center gap-1.5 py-2">
                        <span class="w-1.5 h-6 bg-[#E87559] rounded-full animate-bounce"></span>
                        <span class="w-1.5 h-10 bg-[#E87559] rounded-full animate-bounce [animation-delay:0.1s]"></span>
                        <span class="w-1.5 h-14 bg-[#E87559] rounded-full animate-bounce [animation-delay:0.2s]"></span>
                        <span class="w-1.5 h-8 bg-[#E87559] rounded-full animate-bounce [animation-delay:0.3s]"></span>
                        <span class="w-1.5 h-12 bg-[#E87559] rounded-full animate-bounce [animation-delay:0.4s]"></span>
                        <span class="w-1.5 h-5 bg-[#E87559] rounded-full animate-bounce [animation-delay:0.5s]"></span>
                    </div>

                    <div class="flex items-center justify-center gap-3">
                        <button
                            type="button"
                            @click="stopVoiceRecording()"
                            class="px-5 py-2.5 rounded-xl bg-[#DC6464] hover:bg-red-700 text-white text-xs sm:text-sm font-bold shadow-xs transition"
                        >
                            Selesai Merekam
                        </button>
                        <button
                            type="button"
                            @click="resetVoiceRecording()"
                            class="px-4 py-2.5 rounded-xl text-xs font-semibold text-[#777777] hover:text-[#252525] bg-gray-100 transition"
                        >
                            Batal
                        </button>
                    </div>
                </div>

                <!-- State C: Recorded (Audio Preview Ready) -->
                <div x-show="voiceState === 'recorded'" class="p-5 rounded-2xl bg-[#FCEDEA]/40 border border-[#E87559]/30 space-y-4 max-w-lg mx-auto">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-[#E87559] flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-[#E87559]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                            </svg>
                            <span>Rekaman Suara Siap Ditinjau</span>
                        </span>
                        <span class="text-xs font-mono font-bold text-[#777777]" x-text="formatDuration(voiceDuration)"></span>
                    </div>

                    <template x-if="voiceAudioUrl">
                        <audio :src="voiceAudioUrl" controls class="w-full h-10 rounded-lg"></audio>
                    </template>

                    <div class="flex items-center justify-center gap-2 pt-2">
                        <button
                            type="button"
                            @click="resetVoiceRecording()"
                            class="px-4 py-2 rounded-xl text-xs font-semibold text-[#777777] hover:text-[#252525] bg-white border border-[#EEEEEE] transition"
                        >
                            Rekam Ulang
                        </button>
                        <span class="text-xs text-[#777777] italic">Terapkan nominal transaksi pada form di bawah.</span>
                    </div>
                </div>
            </div>

            <!-- SUBTAB 2B: UNGGAH AUDIO -->
            <div x-show="voiceSubTab === 'upload'" class="p-6 rounded-2xl border-2 border-dashed border-[#EEEEEE] bg-[#FBFBFC] text-center space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-[#FCEDEA] text-[#E87559] flex items-center justify-center mx-auto">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-bold text-[#252525]">Unggah Berkas Rekaman Audio</p>
                    <p class="text-xs text-[#777777] mt-0.5">Mendukung format MP3, WAV, M4A, OGG (maksimal 15 MB)</p>
                </div>
                <input type="file" accept="audio/*" class="text-xs text-[#777777] mx-auto block file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#E87559] file:text-white hover:file:bg-[#D66448]" />
            </div>

        </div>

        <!-- ======================================================== -->
        <!-- PANEL TAB 3: TEKS BEBAS (DESKRIPSI PENJUALAN)            -->
        <!-- ======================================================== -->
        <div x-show="activeTab === 'text'" x-cloak class="space-y-4 mb-8">
            <div>
                <label class="block text-xs sm:text-sm font-semibold text-[#252525] mb-1.5">
                    Tuliskan Rincian Penjualan Bebas
                </label>
                <textarea
                    x-model="textInput"
                    rows="3"
                    placeholder="Contoh: Penjualan 15 porsi nasi goreng seharga Rp25.000 per porsi dan 5 gelas es teh seharga Rp5.000."
                    class="block w-full py-3 px-3.5 text-xs sm:text-sm rounded-xl border border-[#EEEEEE] text-[#252525] placeholder:text-[#BBBBBB] focus:ring-[#E87559]/20 focus:border-[#E87559] bg-[#FBFBFC] transition leading-relaxed"
                ></textarea>
            </div>

            <!-- Quick Preset Chips Penjualan -->
            <div class="flex flex-wrap items-center gap-1.5 text-xs">
                <span class="text-[#777777] font-medium mr-1">Contoh Cepat:</span>
                <button
                    type="button"
                    @click="applyTextExample('Omzet makan siang 20 porsi ayam bakar @25rb + es teh', 500000)"
                    class="px-2.5 py-1 rounded-lg bg-[#FBFBFC] hover:bg-[#DDF1EC] border border-[#EEEEEE] hover:border-emerald-300 text-[#252525] hover:text-emerald-800 transition"
                >
                    + Shift Makan Siang (Rp 500rb)
                </button>
                <button
                    type="button"
                    @click="applyTextExample('Pesanan katering nasi kotak rapat 35 box', 875000)"
                    class="px-2.5 py-1 rounded-lg bg-[#FBFBFC] hover:bg-[#DDF1EC] border border-[#EEEEEE] hover:border-emerald-300 text-[#252525] hover:text-emerald-800 transition"
                >
                    + Katering 35 Kotak (Rp 875rb)
                </button>
                <button
                    type="button"
                    @click="applyTextExample('Total rekap kasir POS tutup warung malam', 1450000)"
                    class="px-2.5 py-1 rounded-lg bg-[#FBFBFC] hover:bg-[#DDF1EC] border border-[#EEEEEE] hover:border-emerald-300 text-[#252525] hover:text-emerald-800 transition"
                >
                    + Tutup Kasir Malam (Rp 1.45jt)
                </button>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- FORM KONFIRMASI & PENYIMPANAN TRANSAKSI PENJUALAN        -->
        <!-- ======================================================== -->
        <div class="pt-6 border-t border-[#EEEEEE]">

            <div class="mb-5">
                <h3 class="text-base sm:text-lg font-bold text-[#252525]">
                    Konfirmasi & Rincian Kas Masuk
                </h3>
                <p class="text-xs text-[#777777] mt-0.5">
                    Periksa kembali nominal penjualan dan metode penerimaan sebelum disimpan permanen ke arus kas usaha.
                </p>
            </div>

            <form wire:submit="save" class="space-y-6">

                <!-- 1. Input Nominal Omzet Penjualan -->
                <div>
                    <label for="amount" class="block text-xs sm:text-sm font-semibold text-[#252525] mb-1.5">
                        Total Nominal Penjualan <span class="text-[#E87559]">*</span>
                    </label>
                    <div class="relative rounded-xl shadow-xs">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <span class="text-[#777777] font-extrabold text-lg">Rp</span>
                        </div>
                        <input
                            type="number"
                            id="amount"
                            wire:model="amount"
                            placeholder="0"
                            step="1000"
                            class="block w-full pl-14 pr-4 py-3.5 text-xl sm:text-2xl font-bold rounded-xl border {{ $errors->has('amount') ? 'border-[#DC6464] text-[#DC6464] focus:ring-[#DC6464]/20 focus:border-[#DC6464]' : 'border-[#EEEEEE] text-[#252525] focus:ring-[#E87559]/20 focus:border-[#E87559]' }} bg-[#FBFBFC] placeholder:text-[#BBBBBB] transition"
                        />
                    </div>
                    @error('amount')
                        <p class="mt-1.5 text-xs text-[#DC6464] font-medium flex items-center gap-1.5">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <span>{{ $message }}</span>
                        </p>
                    @enderror

                    <!-- Quick Add Pills -->
                    <div class="mt-3 flex flex-wrap items-center gap-1.5 sm:gap-2">
                        <span class="text-xs text-[#777777] font-medium mr-1">Tambah Cepat:</span>
                        <button type="button" wire:click="addAmount(50000)" class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-[#FBFBFC] hover:bg-[#DDF1EC] border border-[#EEEEEE] hover:border-emerald-300 text-[#252525] hover:text-emerald-800 transition-all active:scale-95">
                            +50rb
                        </button>
                        <button type="button" wire:click="addAmount(100000)" class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-[#FBFBFC] hover:bg-[#DDF1EC] border border-[#EEEEEE] hover:border-emerald-300 text-[#252525] hover:text-emerald-800 transition-all active:scale-95">
                            +100rb
                        </button>
                        <button type="button" wire:click="addAmount(500000)" class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-[#FBFBFC] hover:bg-[#DDF1EC] border border-[#EEEEEE] hover:border-emerald-300 text-[#252525] hover:text-emerald-800 transition-all active:scale-95">
                            +500rb
                        </button>
                        <button type="button" wire:click="addAmount(1000000)" class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-[#FBFBFC] hover:bg-[#DDF1EC] border border-[#EEEEEE] hover:border-emerald-300 text-[#252525] hover:text-emerald-800 transition-all active:scale-95">
                            +1jt
                        </button>
                        <button type="button" wire:click="setAmount(0)" class="px-2.5 py-1 text-xs font-semibold rounded-lg text-[#E87559] hover:bg-[#FCEDEA] border border-transparent hover:border-[#E87559]/30 transition-all">
                            Reset
                        </button>
                    </div>
                </div>

                <!-- 2. Pilihan Kategori / Metode Penerimaan Penjualan -->
                <div>
                    <label class="block text-xs sm:text-sm font-semibold text-[#252525] mb-2">
                        Metode Penerimaan Penjualan <span class="text-[#E87559]">*</span>
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <!-- Tunai -->
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

                        <!-- Rekap Gabungan -->
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

                <!-- 3 & 4. Tanggal Penjualan & Catatan Ringkas (Grid 2 Kolom Responsif) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 sm:gap-6">
                    <!-- 3. Tanggal Transaksi -->
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
                            Otomatis terisi tanggal hari ini. Tidak dapat memilih tanggal di masa depan.
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

                    <!-- 4. Catatan Tambahan (Opsional) -->
                    <div>
                        <label for="notes" class="block text-xs sm:text-sm font-semibold text-[#252525] mb-1.5">
                            Catatan Ringkas <span class="text-xs text-[#777777] font-normal">(Opsional)</span>
                        </label>
                        <textarea
                            id="notes"
                            wire:model="notes"
                            rows="2"
                            placeholder="Contoh: Omzet ramai shift makan siang, pesanan nasi kotak 20 porsi..."
                            class="block w-full py-2.5 px-3 text-xs sm:text-sm rounded-xl border border-[#EEEEEE] text-[#252525] placeholder:text-[#BBBBBB] focus:ring-[#E87559]/20 focus:border-[#E87559] bg-[#FBFBFC] transition"
                        ></textarea>
                        @error('notes')
                            <p class="mt-1 text-xs text-[#DC6464]">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- 5. Tombol Aksi Submit Penjualan -->
                <div class="pt-5 border-t border-[#EEEEEE] flex items-center justify-end gap-3">
                    <a
                        href="{{ route('transactions.create') }}"
                        wire:navigate
                        class="px-4 py-2.5 text-xs sm:text-sm font-semibold text-[#777777] hover:text-[#252525] bg-gray-100 hover:bg-gray-200 rounded-xl transition"
                    >
                        Batal
                    </a>
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 px-6 py-2.5 text-xs sm:text-sm font-bold text-white bg-[#E87559] hover:bg-[#D66448] active:bg-[#C25338] rounded-xl shadow-xs hover:shadow-md transition active:scale-95 disabled:opacity-50"
                    >
                        <span wire:loading.remove class="inline-flex items-center gap-1.5">
                            <span>Simpan Penjualan</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
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

            </form>

        </div>

    </div>

    <!-- Kotak Tips & Panduan Pencatatan Penjualan KasiVo -->
    <div class="p-5 rounded-2xl bg-white border border-[#EEEEEE] shadow-2xs space-y-3">
        <h4 class="text-xs font-bold text-[#252525] flex items-center gap-1.5 uppercase tracking-wider">
            <svg class="w-4 h-4 text-[#43A58A]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>Panduan Kas Masuk KasiVo</span>
        </h4>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs text-[#777777]">
            <div class="p-3 rounded-xl bg-[#FBFBFC] border border-[#EEEEEE]/80 space-y-1">
                <p class="font-semibold text-[#252525]">1. Catat Penerimaan Riil</p>
                <p class="leading-relaxed">Pastikan omzet yang dicatat adalah nilai uang yang benar-benar telah diterima di laci atau rekening.</p>
            </div>
            <div class="p-3 rounded-xl bg-[#FBFBFC] border border-[#EEEEEE]/80 space-y-1">
                <p class="font-semibold text-[#252525]">2. Pisahkan Tunai & QRIS</p>
                <p class="leading-relaxed">Memilih metode pembayaran yang tepat memudahkan rekonsiliasi kas harian saat tutup buku.</p>
            </div>
            <div class="p-3 rounded-xl bg-[#FBFBFC] border border-[#EEEEEE]/80 space-y-1">
                <p class="font-semibold text-[#252525]">3. Rekap Otomatis Tersedia</p>
                <p class="leading-relaxed">Jika memiliki laporan Excel/CSV dari merchant digital, gunakan tombol Impor Digital di kanan atas.</p>
            </div>
        </div>
    </div>
</div>

<script>
    function incomeRecorder() {
        return {
            activeTab: 'note', // 'note' | 'voice' | 'text'
            isDragging: false,
            photoPreview: null,
            photoMeta: null,
            fileError: null,

            // State Suara
            voiceSubTab: 'record', // 'record' | 'upload'
            voiceState: 'idle', // 'idle' | 'recording' | 'recorded'
            voiceDuration: 0,
            voiceTimer: null,
            voiceAudioUrl: null,
            voiceMediaRecorder: null,
            voiceAudioChunks: [],
            voiceError: null,

            // State Teks
            textInput: '',

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
                    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                        this.voiceError = 'Peramban ini tidak mendukung akses mikrofon rekaman audio.';
                        return;
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
                    this.voiceError = 'Izin akses mikrofon ditolak atau perangkat tidak terdeteksi.';
                    this.voiceState = 'idle';
                }
            },

            stopVoiceRecording() {
                if (this.voiceMediaRecorder && this.voiceState === 'recording') {
                    this.voiceMediaRecorder.stop();
                    clearInterval(this.voiceTimer);
                    this.voiceState = 'recorded';
                }
            },

            resetVoiceRecording() {
                if (this.voiceTimer) clearInterval(this.voiceTimer);
                this.voiceState = 'idle';
                this.voiceDuration = 0;
                this.voiceAudioUrl = null;
                this.voiceError = null;
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
            },

            applyTextExample(exampleText, amount) {
                this.textInput = exampleText;
                if (amount && window.Livewire) {
                    const el = document.getElementById('amount');
                    if (el) {
                        el.value = amount;
                        el.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                    const notesEl = document.getElementById('notes');
                    if (notesEl) {
                        notesEl.value = exampleText;
                        notesEl.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                }
            }
        };
    }
</script>
