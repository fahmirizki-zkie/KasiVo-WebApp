<div class="py-6 sm:py-10" x-data="window.audioRecorder ? window.audioRecorder() : audioRecorder()">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Card Container Utama -->
        <div class="bg-white rounded-3xl shadow-sm border border-[#EEEEEE] p-6 sm:p-8">

            <!-- 1. Header Halaman & Breadcrumb Kontekstual -->
            <div class="mb-6 sm:mb-8 pb-5 border-b border-[#EEEEEE]">
                <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-[#FCEDEA] text-[#E87559] border border-[#E87559]/20 shadow-xs">
                        <svg class="w-3.5 h-3.5 text-[#E87559]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                        </svg>
                        <span>Kas Keluar (Pengeluaran)</span>
                    </span>

                    <a href="{{ route('transactions.create') }}" 
                       wire:navigate
                       class="text-xs text-[#777777] hover:text-[#E87559] flex items-center gap-1 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        <span>Ganti Jalur</span>
                    </a>
                </div>

                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#252525] tracking-tight">
                    Catat Pengeluaran via Suara
                </h1>
                <p class="text-xs sm:text-sm text-[#777777] mt-1.5 leading-relaxed">
                    Sebutkan belanja bahan baku, operasional, atau tagihan warungmu. AI KasiVo akan mengubah suara menjadi catatan transaksi otomatis.
                </p>
            </div>

            <!-- 2. Tab Switcher: Rekam Mikrofon vs Unggah Berkas -->
            <div class="flex p-1 bg-[#FBFBFC] rounded-2xl border border-[#EEEEEE] mb-6">
                <button
                    type="button"
                    @click="switchTab('record')"
                    :class="activeTab === 'record' ? 'bg-white text-[#252525] shadow-xs font-bold' : 'text-[#777777] hover:text-[#252525] font-medium'"
                    class="flex-1 py-2.5 px-3 rounded-xl text-xs sm:text-sm flex items-center justify-center gap-2 transition-all"
                >
                    <svg class="w-4 h-4 text-[#E87559]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                    </svg>
                    <span>Rekam Suara Langsung</span>
                </button>

                <button
                    type="button"
                    @click="switchTab('upload')"
                    :class="activeTab === 'upload' ? 'bg-white text-[#252525] shadow-xs font-bold' : 'text-[#777777] hover:text-[#252525] font-medium'"
                    class="flex-1 py-2.5 px-3 rounded-xl text-xs sm:text-sm flex items-center justify-center gap-2 transition-all"
                >
                    <svg class="w-4 h-4 text-[#777777]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    <span>Unggah File Audio</span>
                </button>
            </div>

            <!-- 3. Alert Pesan Error (Jika Ada) -->
            <template x-if="state === 'error' && errorMessage">
                <div class="mb-6 p-4 rounded-2xl bg-[#FCEDEA] border border-[#E87559]/20 text-[#252525] text-xs sm:text-sm flex items-start gap-3">
                    <div class="w-8 h-8 rounded-xl bg-white text-[#E87559] flex items-center justify-center shrink-0 shadow-xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="font-bold text-[#E87559]">Perhatian Akses</p>
                        <p class="mt-0.5 text-[#555555] leading-relaxed" x-text="errorMessage"></p>
                        <div class="mt-3 flex items-center gap-2">
                            <button
                                type="button"
                                @click="state = 'idle'; errorMessage = ''"
                                class="px-3 py-1.5 rounded-lg bg-white border border-[#EEEEEE] text-xs font-semibold text-[#252525] hover:bg-gray-50 transition"
                            >
                                Coba Lagi
                            </button>
                            <button
                                type="button"
                                @click="switchTab('upload')"
                                class="px-3 py-1.5 rounded-lg bg-[#FCEDEA] text-xs font-semibold text-[#E87559] hover:bg-[#E87559]/10 transition"
                            >
                                Gunakan Unggah File
                            </button>
                        </div>
                    </div>
                </div>
            </template>

            <!-- ======================================================== -->
            <!-- TAB 1: REKAM MIKROFON                                    -->
            <!-- ======================================================== -->
            <div x-show="activeTab === 'record'" x-cloak class="space-y-6">

                <!-- STATE A: IDLE (Siap Merekam) -->
                <div x-show="state === 'idle'" class="text-center py-6 sm:py-8 space-y-5">
                    <div class="relative inline-flex items-center justify-center">
                        <div class="absolute -inset-3 rounded-full bg-[#FCEDEA]/60 blur-md"></div>
                        <button
                            type="button"
                            @click="startRecording()"
                            class="relative w-24 h-24 sm:w-28 sm:h-28 rounded-full bg-gradient-to-br from-[#E87559] to-[#D66448] text-white shadow-lg shadow-[#E87559]/30 hover:scale-105 active:scale-95 transition-all flex flex-col items-center justify-center group focus:outline-hidden"
                            title="Klik untuk mulai merekam suara"
                        >
                            <svg class="w-10 h-10 sm:w-12 sm:h-12 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                            </svg>
                        </button>
                    </div>

                    <div>
                        <h2 class="text-base sm:text-lg font-bold text-[#252525]">
                            Tekan untuk Mulai Merekam
                        </h2>
                        <p class="text-xs text-[#777777] mt-1 max-w-sm mx-auto">
                            Izin mikrofon browser hanya akan diminta saat tombol ini ditekan.
                        </p>
                    </div>

                    <!-- Voice Prompt Guideline Box -->
                    <div class="bg-[#FBFBFC] rounded-2xl p-4 border border-[#EEEEEE] max-w-md mx-auto text-left space-y-2">
                        <div class="flex items-center gap-1.5 text-xs font-bold text-[#E87559]">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Tips Menyebutkan Belanjaan:</span>
                        </div>
                        <p class="text-xs text-[#555555] leading-relaxed italic">
                            "Beli cabai rawit merah dua kilo tujuh puluh ribu dan minyak goreng sawit dua liter tiga puluh empat ribu."
                        </p>
                        <div class="pt-2 border-t border-[#EEEEEE] flex items-center justify-between text-[11px] text-[#777777]">
                            <span>Batas durasi: <strong class="text-[#252525]" x-text="formattedMaxDuration">02:00</strong></span>
                            <span>Noise Suppression: Aktif</span>
                        </div>
                    </div>
                </div>

                <!-- STATE B: REQUESTING PERMISSION (Menunggu Izin Browser) -->
                <div x-show="state === 'requesting_permission'" class="text-center py-10 space-y-4">
                    <div class="w-20 h-20 rounded-full bg-[#FCEDEA] text-[#E87559] border border-[#E87559]/30 flex items-center justify-center mx-auto animate-pulse">
                        <svg class="w-10 h-10 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-[#252525]">Menghubungkan ke Mikrofon...</h3>
                        <p class="text-xs text-[#777777] mt-1 max-w-sm mx-auto">
                            Silakan pilih <strong>"Izinkan" (Allow)</strong> pada kotak konfirmasi browser Anda untuk melanjutkan perekaman.
                        </p>
                    </div>
                    <button
                        type="button"
                        @click="cancelRecording()"
                        class="text-xs font-semibold text-[#777777] hover:text-[#252525] underline pt-2"
                    >
                        Batalkan Permintaan
                    </button>
                </div>

                <!-- STATE C: RECORDING (Sedang Merekam Suara) -->
                <div x-show="state === 'recording'" class="py-6 sm:py-8 space-y-6 text-center">
                    
                    <!-- Pulsing Microphone Indicator -->
                    <div class="relative inline-flex items-center justify-center">
                        <div class="absolute -inset-4 rounded-full bg-[#E87559]/20 animate-ping"></div>
                        <div class="relative w-24 h-24 rounded-full bg-[#E87559] text-white flex items-center justify-center shadow-lg shadow-[#E87559]/30">
                            <svg class="w-10 h-10 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                            </svg>
                        </div>
                    </div>

                    <!-- Digital Timer Display -->
                    <div class="space-y-1">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#FCEDEA] text-[#E87559] text-xs font-bold">
                            <span class="w-2 h-2 rounded-full bg-[#E87559] animate-ping"></span>
                            <span>Merekam Audio</span>
                        </div>
                        <div class="text-3xl sm:text-4xl font-extrabold text-[#252525] font-mono tracking-tight" x-text="formattedDuration">
                            00:00
                        </div>
                        <p class="text-[11px] text-[#777777]">
                            Maksimal rekaman: <span x-text="formattedMaxDuration">02:00</span>
                        </p>
                    </div>

                    <!-- Real-Time Web Audio API Visualizer Bars -->
                    <div class="flex items-center justify-center gap-1.5 h-10 max-w-xs mx-auto py-1">
                        <template x-for="(barHeight, index) in visualizerBars" :key="index">
                            <div
                                class="w-2.5 rounded-full bg-gradient-to-t from-[#E87559] to-[#F7B09E] transition-all duration-75"
                                :style="`height: ${barHeight}%`"
                            ></div>
                        </template>
                    </div>

                    <!-- Control Action Buttons -->
                    <div class="flex items-center justify-center gap-3 pt-4 border-t border-[#EEEEEE]">
                        <button
                            type="button"
                            @click="cancelRecording()"
                            class="px-4 py-2.5 rounded-xl text-xs font-semibold text-[#777777] hover:text-[#252525] bg-gray-100 hover:bg-gray-200 transition flex items-center gap-1.5"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            <span>Batal</span>
                        </button>

                        <button
                            type="button"
                            @click="stopRecording()"
                            class="px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold text-white bg-[#E87559] hover:bg-[#D66448] shadow-sm transition active:scale-95 flex items-center gap-2"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"/>
                            </svg>
                            <span>Selesai Merekam</span>
                        </button>
                    </div>
                </div>

                <!-- STATE D: STOPPED (Preview Rekaman Selesai) -->
                <div x-show="state === 'stopped'" class="py-4 space-y-5">
                    
                    <!-- Success Header -->
                    <div class="flex items-center gap-2.5 p-3 rounded-xl bg-[#DDF1EC] border border-emerald-200/60 text-emerald-800 text-xs font-semibold">
                        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Rekaman suara berhasil disimpan. Dengarkan kembali sebelum diproses AI.</span>
                    </div>

                    <!-- Audio Player Box -->
                    <div class="p-4 sm:p-5 rounded-2xl bg-[#FBFBFC] border border-[#EEEEEE] space-y-3">
                        <div class="flex items-center justify-between text-xs text-[#777777]">
                            <span class="font-medium flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-[#E87559]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/>
                                </svg>
                                <span>Pemutar Hasil Rekaman</span>
                            </span>
                            <span class="font-semibold text-[#252525]" x-text="`Durasi: ${formattedDuration}`"></span>
                        </div>

                        <!-- Native HTML5 Audio Controls -->
                        <div class="w-full">
                            <audio :src="audioUrl" controls class="w-full rounded-lg focus:outline-hidden"></audio>
                        </div>

                        <!-- Metadata Info -->
                        <div class="flex flex-wrap items-center justify-between gap-2 pt-2 text-[11px] text-[#777777] border-t border-[#EEEEEE]">
                            <span class="truncate max-w-xs" x-text="fileName"></span>
                            <span class="px-2 py-0.5 rounded-md bg-white border border-[#EEEEEE] font-mono font-medium text-[#252525]" x-text="fileSizeFormatted"></span>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-wrap items-center justify-between gap-3 pt-3">
                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                @click="resetRecording()"
                                class="px-3.5 py-2 text-xs font-semibold rounded-xl text-[#252525] bg-gray-100 hover:bg-gray-200 transition flex items-center gap-1.5"
                            >
                                <svg class="w-3.5 h-3.5 text-[#777777]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                <span>Ulangi Rekaman</span>
                            </button>

                            <button
                                type="button"
                                @click="deleteAudio()"
                                class="px-3 py-2 text-xs font-semibold rounded-xl text-[#DC6464] hover:bg-[#FCEDEA] transition flex items-center gap-1"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                <span>Hapus</span>
                            </button>
                        </div>

                        <!-- CTA: Kirim untuk Diproses AI -->
                        <button
                            type="button"
                            @click="submitAudio()"
                            class="px-5 py-2.5 text-xs sm:text-sm font-bold text-white bg-[#E87559] hover:bg-[#D66448] rounded-xl shadow-xs transition active:scale-95 flex items-center gap-2"
                        >
                            <span>Kirim untuk Diproses AI</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </button>
                    </div>

                </div>

            </div>

            <!-- ======================================================== -->
            <!-- TAB 2: UNGGAH FILE AUDIO ALTERNATIF                      -->
            <!-- ======================================================== -->
            <div x-show="activeTab === 'upload'" x-cloak class="space-y-6">

                <!-- Hidden Native File Input -->
                <input
                    type="file"
                    accept="audio/*,.mp3,.wav,.m4a,.webm,.ogg,.aac"
                    class="sr-only"
                    x-ref="fileInput"
                    @change="handleFileUpload($event)"
                />

                <!-- Upload Dropzone (Ketika belum ada file yang dipilih) -->
                <div
                    x-show="state !== 'stopped'"
                    @click="triggerFileInput()"
                    class="border-2 border-dashed border-[#EEEEEE] hover:border-[#E87559] rounded-2xl p-8 sm:p-10 text-center cursor-pointer transition-colors bg-[#FBFBFC] hover:bg-[#FCEDEA]/20 group"
                >
                    <div class="w-14 h-14 rounded-2xl bg-[#FCEDEA] text-[#E87559] border border-[#E87559]/20 flex items-center justify-center mx-auto mb-3.5 group-hover:scale-105 transition-transform">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                    </div>

                    <h3 class="text-sm sm:text-base font-bold text-[#252525] group-hover:text-[#E87559] transition-colors">
                        Pilih Berkas Audio dari Perangkat
                    </h3>
                    <p class="text-xs text-[#777777] mt-1 max-w-sm mx-auto leading-relaxed">
                        Dukung rekaman pesan suara WhatsApp atau aplikasi perekam ponsel Anda.
                    </p>

                    <div class="mt-4 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white border border-[#EEEEEE] text-xs font-semibold text-[#252525] shadow-2xs">
                        <span>Pilih File Audio</span>
                        <span class="text-[#777777]">(MP3, WAV, M4A, WEBM, OGG)</span>
                    </div>

                    <p class="text-[11px] text-[#777777] mt-3">
                        Batas ukuran file maksimum: <strong>15 MB</strong>
                    </p>
                </div>

                <!-- Preview File Audio yang Diunggah -->
                <div x-show="state === 'stopped'" class="py-2 space-y-4">
                    <div class="p-5 rounded-2xl bg-[#FBFBFC] border border-[#EEEEEE] space-y-3">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-bold text-[#252525] flex items-center gap-2">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="truncate max-w-xs" x-text="fileName"></span>
                            </span>
                            <span class="px-2 py-0.5 rounded-md bg-white border border-[#EEEEEE] font-mono text-[11px] font-semibold text-[#252525]" x-text="fileSizeFormatted"></span>
                        </div>

                        <!-- Audio Player -->
                        <audio :src="audioUrl" controls class="w-full rounded-lg focus:outline-hidden"></audio>
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                @click="triggerFileInput()"
                                class="px-3.5 py-2 text-xs font-semibold rounded-xl text-[#252525] bg-gray-100 hover:bg-gray-200 transition"
                            >
                                Ganti File
                            </button>
                            <button
                                type="button"
                                @click="deleteAudio()"
                                class="px-3 py-2 text-xs font-semibold rounded-xl text-[#DC6464] hover:bg-[#FCEDEA] transition"
                            >
                                Hapus
                            </button>
                        </div>

                        <button
                            type="button"
                            @click="submitAudio()"
                            class="px-5 py-2.5 text-xs sm:text-sm font-bold text-white bg-[#E87559] hover:bg-[#D66448] rounded-xl shadow-xs transition active:scale-95 flex items-center gap-2"
                        >
                            <span>Kirim untuk Diproses AI</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </button>
                    </div>
                </div>

            </div>

        </div>

        <!-- 4. Kotak Tips & Panduan Pencatatan Pengeluaran -->
        <div class="mt-6 p-5 rounded-2xl bg-white border border-[#EEEEEE] shadow-2xs space-y-3">
            <h4 class="text-xs font-bold text-[#252525] flex items-center gap-1.5 uppercase tracking-wider">
                <svg class="w-4 h-4 text-[#E87559]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                </svg>
                <span>Panduan Suara KasiVo</span>
            </h4>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs text-[#777777]">
                <div class="p-3 rounded-xl bg-[#FBFBFC] border border-[#EEEEEE]/80 space-y-1">
                    <p class="font-semibold text-[#252525]">1. Tempat Tenang</p>
                    <p class="leading-relaxed">Gunakan di area yang tidak terlalu berisik agar artikulasi suara jelas ditangkap.</p>
                </div>
                <div class="p-3 rounded-xl bg-[#FBFBFC] border border-[#EEEEEE]/80 space-y-1">
                    <p class="font-semibold text-[#252525]">2. Sebut Rincian Lengkap</p>
                    <p class="leading-relaxed">Sebutkan nama barang, kuantitas/satuan, dan nominal harga belanjaan secara urut.</p>
                </div>
                <div class="p-3 rounded-xl bg-[#FBFBFC] border border-[#EEEEEE]/80 space-y-1">
                    <p class="font-semibold text-[#252525]">3. Tetap Bisa Dikoreksi</p>
                    <p class="leading-relaxed">Setelah AI menguraikan transaksi, kamu selalu dapat meninjau dan mengoreksi data sebelum tersimpan.</p>
                </div>
            </div>
        </div>

    </div>

    <!-- ======================================================== -->
    <!-- MODAL INTEGRASI BACKEND (Kesiapan Endpoint AI Whisper)    -->
    <!-- ======================================================== -->
    <div
        x-show="showIntegrationModal"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto bg-black/40 backdrop-blur-xs flex items-center justify-center p-4"
    >
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-[#EEEEEE] space-y-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-[#DDF1EC] text-[#43A58A] border border-emerald-200 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-[#252525]">Audio Siap Diproses</h3>
                    <p class="text-xs text-[#777777]">Komponen Frontend KV-027 berhasil disiapkan.</p>
                </div>
            </div>

            <div class="bg-[#FBFBFC] p-3.5 rounded-xl border border-[#EEEEEE] text-xs space-y-1.5 text-[#555555] leading-relaxed">
                <p>
                    Berkas audio berhasil direkam/dipilih dengan metadata valid:
                </p>
                <p class="font-mono text-[11px] text-[#252525] bg-white p-2 rounded-lg border border-[#EEEEEE]">
                    Berkas: <span x-text="fileName"></span> (<span x-text="fileSizeFormatted"></span>)
                </p>
                <p class="pt-1 text-[#777777]">
                    Pengiriman audio ke model Whisper STT saat ini menunggu finalisasi kontrak API multipart / Livewire File Upload dari Backend (Fahmi).
                </p>
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-2 border-t border-[#EEEEEE]">
                <button
                    type="button"
                    @click="showIntegrationModal = false"
                    class="px-4 py-2 text-xs font-bold text-white bg-[#E87559] hover:bg-[#D66448] rounded-xl shadow-xs transition"
                >
                    Saya Mengerti
                </button>
            </div>
        </div>
    </div>

    <!-- Script Audio Recorder Mandiri (Memastikan fungsi selalu aktif tanpa dependensi dev-server) -->
    <script>
        (function() {
            function initAudioRecorder() {
                return {
                    state: 'idle',
                    activeTab: 'record',
                    mediaStream: null,
                    mediaRecorder: null,
                    audioChunks: [],
                    audioBlob: null,
                    audioUrl: null,
                    mimeType: '',
                    audioContext: null,
                    analyser: null,
                    animationFrame: null,
                    visualizerBars: [15, 25, 45, 60, 40, 20],
                    seconds: 0,
                    maxDuration: 120,
                    timerInterval: null,
                    uploadedFile: null,
                    fileName: '',
                    fileSizeFormatted: '',
                    maxFileSizeBytes: 15 * 1024 * 1024,
                    errorMessage: '',
                    showIntegrationModal: false,

                    init() {
                        window.addEventListener('livewire:navigating', () => {
                            this.cleanup();
                            this.clearAudioPreview();
                        }, { once: true });

                        window.addEventListener('beforeunload', () => {
                            this.cleanup();
                        });
                    },

                    get formattedDuration() {
                        const mins = Math.floor(this.seconds / 60);
                        const secs = this.seconds % 60;
                        return `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
                    },

                    get formattedMaxDuration() {
                        const mins = Math.floor(this.maxDuration / 60);
                        const secs = this.maxDuration % 60;
                        return `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
                    },

                    switchTab(tab) {
                        if (this.state === 'recording') {
                            this.cancelRecording();
                        }
                        this.stopAllAudioPlayback();
                        this.activeTab = tab;
                        if (this.state !== 'stopped') {
                            this.state = 'idle';
                            this.errorMessage = '';
                        }
                    },

                    async startRecording() {
                        this.errorMessage = '';
                        this.clearAudioPreview();

                        // 1. Cek secure context
                        if (typeof window !== 'undefined' && !window.isSecureContext && window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1') {
                            this.state = 'error';
                            this.errorMessage = 'Perekaman mikrofon membutuhkan koneksi aman (HTTPS). Silakan akses aplikasi melalui URL HTTPS atau localhost.';
                            return;
                        }

                        // 2. Cek ketersediaan Web MediaDevices API
                        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                            this.state = 'error';
                            this.errorMessage = 'Browser Anda belum mendukung Web MediaDevices API. Silakan gunakan browser modern seperti Google Chrome, Microsoft Edge, atau Safari, atau gunakan opsi Unggah File Audio.';
                            return;
                        }

                        // 3. Cek MediaRecorder
                        if (typeof MediaRecorder === 'undefined') {
                            this.state = 'error';
                            this.errorMessage = 'Browser Anda belum mendukung MediaRecorder. Silakan gunakan opsi Unggah File Audio.';
                            return;
                        }

                        this.mimeType = this.getSupportedMimeType();
                        this.state = 'requesting_permission';

                        try {
                            this.mediaStream = await navigator.mediaDevices.getUserMedia({
                                audio: {
                                    echoCancellation: true,
                                    noiseSuppression: true,
                                    autoGainControl: true,
                                }
                            });

                            this.audioChunks = [];
                            const recorderOptions = this.mimeType ? { mimeType: this.mimeType } : {};
                            this.mediaRecorder = new MediaRecorder(this.mediaStream, recorderOptions);

                            this.mediaRecorder.ondataavailable = (event) => {
                                if (event.data && event.data.size > 0) {
                                    this.audioChunks.push(event.data);
                                }
                            };

                            this.mediaRecorder.onstop = () => {
                                const actualType = this.mimeType || (this.audioChunks[0] ? this.audioChunks[0].type : 'audio/webm');
                                this.audioBlob = new Blob(this.audioChunks, { type: actualType });
                                this.audioUrl = URL.createObjectURL(this.audioBlob);
                                this.fileName = `rekaman-suara-${new Date().toISOString().slice(0, 10)}.webm`;
                                this.fileSizeFormatted = this.formatBytes(this.audioBlob.size);
                                this.state = 'stopped';
                                this.cleanupMediaTracks();
                            };

                            this.setupRealVisualizer(this.mediaStream);

                            this.mediaRecorder.start(100);
                            this.seconds = 0;
                            this.state = 'recording';

                            this.timerInterval = setInterval(() => {
                                this.seconds++;
                                if (this.seconds >= this.maxDuration) {
                                    this.stopRecording();
                                }
                            }, 1000);

                        } catch (err) {
                            this.cleanup();
                            this.state = 'error';

                            if (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError') {
                                this.errorMessage = 'Izin mikrofon belum diberikan atau diblokir oleh browser. Silakan klik ikon setelan/gembok pada address bar browser untuk mengizinkan akses mikrofon lalu coba lagi.';
                            } else if (err.name === 'NotFoundError' || err.name === 'DevicesNotFoundError') {
                                this.errorMessage = 'Perangkat mikrofon tidak ditemukan. Pastikan mikrofon terpasang atau gunakan opsi Unggah File Audio.';
                            } else if (err.name === 'NotReadableError' || err.name === 'TrackStartError') {
                                this.errorMessage = 'Mikrofon sedang digunakan oleh aplikasi lain atau tidak dapat diakses.';
                            } else {
                                this.errorMessage = `Gagal mengaktifkan mikrofon: ${err.message || 'Terjadi kesalahan sistem'}.`;
                            }
                        }
                    },

                    setupRealVisualizer(stream) {
                        try {
                            const AudioCtxClass = window.AudioContext || window.webkitAudioContext;
                            if (!AudioCtxClass) return;

                            this.audioContext = new AudioCtxClass();
                            const source = this.audioContext.createMediaStreamSource(stream);
                            this.analyser = this.audioContext.createAnalyser();
                            this.analyser.fftSize = 32;
                            source.connect(this.analyser);

                            const dataArray = new Uint8Array(this.analyser.frequencyBinCount);

                            const updateWaveform = () => {
                                if (this.state !== 'recording') return;
                                this.analyser.getByteFrequencyData(dataArray);

                                const step = Math.max(1, Math.floor(dataArray.length / 6));
                                this.visualizerBars = [
                                    Math.max(15, Math.min(100, Math.round(dataArray[0 * step] / 2.55))),
                                    Math.max(15, Math.min(100, Math.round(dataArray[1 * step] / 2.55))),
                                    Math.max(15, Math.min(100, Math.round(dataArray[2 * step] / 2.55))),
                                    Math.max(15, Math.min(100, Math.round(dataArray[3 * step] / 2.55))),
                                    Math.max(15, Math.min(100, Math.round(dataArray[4 * step] / 2.55))),
                                    Math.max(15, Math.min(100, Math.round(dataArray[5 * step] / 2.55))),
                                ];

                                this.animationFrame = requestAnimationFrame(updateWaveform);
                            };

                            this.animationFrame = requestAnimationFrame(updateWaveform);
                        } catch (e) {}
                    },

                    stopRecording() {
                        if (this.timerInterval) {
                            clearInterval(this.timerInterval);
                            this.timerInterval = null;
                        }
                        if (this.animationFrame) {
                            cancelAnimationFrame(this.animationFrame);
                            this.animationFrame = null;
                        }
                        if (this.mediaRecorder && this.mediaRecorder.state !== 'inactive') {
                            this.mediaRecorder.stop();
                        } else {
                            this.cleanupMediaTracks();
                            this.state = 'stopped';
                        }
                    },

                    cancelRecording() {
                        this.stopAllAudioPlayback();
                        this.cleanup();
                        this.clearAudioPreview();
                        this.seconds = 0;
                        this.state = 'idle';
                    },

                    resetRecording() {
                        this.stopAllAudioPlayback();
                        this.cleanup();
                        this.clearAudioPreview();
                        this.seconds = 0;
                        this.state = 'idle';
                        this.startRecording();
                    },

                    deleteAudio() {
                        this.stopAllAudioPlayback();
                        this.cleanup();
                        this.clearAudioPreview();
                        this.seconds = 0;
                        this.state = 'idle';
                        this.errorMessage = '';
                    },

                    handleFileUpload(event) {
                        const file = event.target.files && event.target.files[0];
                        if (!file) return;

                        this.errorMessage = '';
                        this.clearAudioPreview();

                        if (file.size === 0) {
                            this.state = 'error';
                            this.errorMessage = 'Berkas audio yang dipilih kosong (0 bytes). Silakan pilih file audio lain.';
                            event.target.value = '';
                            return;
                        }

                        const validExtensions = ['mp3', 'wav', 'm4a', 'webm', 'ogg', 'aac', 'flac'];
                        const fileExt = file.name.split('.').pop().toLowerCase();
                        const isValidType = (file.type && file.type.startsWith('audio/')) || validExtensions.includes(fileExt);

                        if (!isValidType) {
                            this.state = 'error';
                            this.errorMessage = `Format berkas ".${fileExt}" tidak didukung. Harap pilih berkas audio seperti MP3, WAV, M4A, WEBM, atau OGG.`;
                            event.target.value = '';
                            return;
                        }

                        if (file.size > this.maxFileSizeBytes) {
                            this.state = 'error';
                            this.errorMessage = `Ukuran berkas (${this.formatBytes(file.size)}) melebihi batas maksimum 15 MB. Silakan pilih berkas yang lebih kecil.`;
                            event.target.value = '';
                            return;
                        }

                        this.uploadedFile = file;
                        this.audioBlob = file;
                        this.fileName = file.name;
                        this.fileSizeFormatted = this.formatBytes(file.size);
                        this.audioUrl = URL.createObjectURL(file);
                        this.seconds = 0;
                        this.state = 'stopped';
                        event.target.value = '';
                    },

                    triggerFileInput() {
                        if (this.$refs && this.$refs.fileInput) {
                            this.$refs.fileInput.click();
                        }
                    },

                    submitAudio() {
                        this.showIntegrationModal = true;
                    },

                    getSupportedMimeType() {
                        if (typeof MediaRecorder === 'undefined') return '';
                        const candidates = [
                            'audio/webm;codecs=opus',
                            'audio/webm',
                            'audio/mp4',
                            'audio/ogg',
                            'audio/wav',
                        ];
                        for (const candidate of candidates) {
                            if (MediaRecorder.isTypeSupported(candidate)) {
                                return candidate;
                            }
                        }
                        return '';
                    },

                    formatBytes(bytes) {
                        if (!bytes || bytes === 0) return '0 B';
                        const k = 1024;
                        const sizes = ['B', 'KB', 'MB', 'GB'];
                        const i = Math.floor(Math.log(bytes) / Math.log(k));
                        return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
                    },

                    stopAllAudioPlayback() {
                        try {
                            const audios = document.querySelectorAll('audio');
                            audios.forEach(audio => {
                                audio.pause();
                                audio.currentTime = 0;
                                audio.removeAttribute('src');
                                audio.load();
                            });
                        } catch (e) {}
                    },

                    clearAudioPreview() {
                        this.stopAllAudioPlayback();
                        if (this.audioUrl) {
                            URL.revokeObjectURL(this.audioUrl);
                            this.audioUrl = null;
                        }
                        this.audioBlob = null;
                        this.uploadedFile = null;
                        this.fileName = '';
                        this.fileSizeFormatted = '';
                    },

                    cleanupMediaTracks() {
                        if (this.mediaStream) {
                            this.mediaStream.getTracks().forEach(track => {
                                track.stop();
                            });
                            this.mediaStream = null;
                        }
                        if (this.audioContext && this.audioContext.state !== 'closed') {
                            try {
                                this.audioContext.close();
                            } catch (e) {}
                            this.audioContext = null;
                        }
                    },

                    cleanup() {
                        this.stopAllAudioPlayback();
                        this.cleanupMediaTracks();
                        if (this.timerInterval) {
                            clearInterval(this.timerInterval);
                            this.timerInterval = null;
                        }
                        if (this.animationFrame) {
                            cancelAnimationFrame(this.animationFrame);
                            this.animationFrame = null;
                        }
                    }
                };
            }

            window.audioRecorder = initAudioRecorder;

            document.addEventListener('alpine:init', () => {
                if (window.Alpine) {
                    window.Alpine.data('audioRecorder', initAudioRecorder);
                }
            });

            if (window.Alpine) {
                window.Alpine.data('audioRecorder', initAudioRecorder);
            }
        })();
    </script>
</div>

