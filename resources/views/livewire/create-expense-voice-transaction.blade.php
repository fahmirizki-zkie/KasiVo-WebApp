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

            <!-- Pesan Error Backend / Livewire -->
            @if($errorMessage)
                <div class="mb-6 p-4 rounded-2xl bg-[#FCEDEA] border border-[#E87559]/20 text-[#252525] text-xs sm:text-sm flex items-start gap-3">
                    <div class="w-8 h-8 rounded-xl bg-white text-[#DC6464] flex items-center justify-center shrink-0 shadow-xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="font-bold text-[#DC6464]">Perhatian</p>
                        <p class="mt-0.5 text-[#555555] leading-relaxed">{{ $errorMessage }}</p>
                        <button type="button" wire:click="$set('errorMessage', null)" class="mt-2 text-xs text-[#E87559] hover:underline font-semibold">Tutup</button>
                    </div>
                </div>
            @endif

            <!-- ======================================================== -->
            <!-- TAMPILAN 1: PROSES AI SEDANG BERJALAN                    -->
            <!-- ======================================================== -->
            @if($isProcessing)
                <div class="py-12 text-center space-y-6">
                    <div class="relative inline-flex items-center justify-center">
                        <div class="w-20 h-20 rounded-full bg-[#FCEDEA] animate-ping absolute opacity-40"></div>
                        <div class="w-16 h-16 rounded-full bg-[#E87559] text-white flex items-center justify-center shadow-md relative z-10">
                            <svg class="w-8 h-8 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="space-y-1.5">
                        <h3 class="text-lg font-bold text-[#252525]">AI KasiVo Sedang Mendengarkan...</h3>
                        <p class="text-xs sm:text-sm text-[#777777] max-w-md mx-auto leading-relaxed">
                            Mentranskripsi suara dengan Groq Whisper STT dan menyusun rincian pengeluaran ke dalam format terstruktur.
                        </p>
                    </div>
                    <div class="flex items-center justify-center gap-1.5 text-xs text-[#E87559] font-medium">
                        <svg class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <span>Estimasi proses < 2 detik</span>
                    </div>
                </div>

            <!-- ======================================================== -->
            <!-- TAMPILAN 2: KONFIRMASI DRAFT HASIL AI (REVIEW SCREEN)     -->
            <!-- ======================================================== -->
            @elseif($step === 'review')
                <div class="space-y-6">

                    <!-- Audio Transcription Bubble -->
                    @if($transcriptionText)
                        <div class="p-4 rounded-2xl bg-[#FCEDEA]/50 border border-[#E87559]/20 flex items-start gap-3">
                            <div class="w-8 h-8 rounded-xl bg-[#E87559] text-white flex items-center justify-center shrink-0 shadow-xs">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <p class="text-xs font-bold text-[#E87559]">Transkripsi Suara (Whisper AI):</p>
                                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-semibold">Terekstrak</span>
                                </div>
                                <p class="text-xs sm:text-sm font-medium text-[#252525] mt-1 italic leading-relaxed">
                                    "{{ $transcriptionText }}"
                                </p>
                            </div>
                        </div>
                    @endif

                    <!-- Warning jika needs_review aktif -->
                    @if(!empty($draft['needs_review']))
                        <div class="p-3.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <p class="leading-relaxed">
                                <strong>Perlu Ditinjau:</strong> {{ $draft['review_reason'] ?: 'Pastikan nominal dan rincian belanja sudah sesuai dengan rekaman Anda.' }}
                            </p>
                        </div>
                    @endif

                    <!-- Form Konfirmasi Transaksi -->
                    <div class="space-y-5 pt-2">

                        <!-- Total Pengeluaran -->
                        <div>
                            <label class="block text-xs font-bold text-[#777777] uppercase tracking-wider mb-1.5">
                                Total Pengeluaran (Rp) <span class="text-[#DC6464]">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-lg font-bold text-[#777777]">Rp</span>
                                <input
                                    type="number"
                                    wire:model.live="draft.amount"
                                    value="{{ $draft['amount'] }}"
                                    class="w-full pl-12 pr-4 py-3 rounded-2xl border border-[#EEEEEE] focus:border-[#E87559] focus:ring-1 focus:ring-[#E87559] text-2xl font-extrabold text-[#252525]"
                                    placeholder="0"
                                    min="0"
                                />
                            </div>
                            @error('draft.amount') <p class="text-xs text-[#DC6464] mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Kategori Pengeluaran -->
                        <div>
                            <label class="block text-xs font-bold text-[#777777] uppercase tracking-wider mb-1.5">
                                Kategori <span class="text-[#DC6464]">*</span>
                            </label>
                            <div class="grid grid-cols-2 gap-3">
                                <button
                                    type="button"
                                    wire:click="$set('draft.category', 'bahan_baku')"
                                    class="p-3 rounded-xl border text-left flex items-center gap-3 transition {{ ($draft['category'] ?? '') === 'bahan_baku' ? 'border-[#E87559] bg-[#FCEDEA] text-[#E87559] font-bold shadow-2xs' : 'border-[#EEEEEE] bg-white text-[#555555] hover:bg-gray-50' }}"
                                >
                                    <span class="w-3 h-3 rounded-full {{ ($draft['category'] ?? '') === 'bahan_baku' ? 'bg-[#E87559]' : 'bg-gray-300' }}"></span>
                                    <div>
                                        <p class="text-xs font-bold">Bahan Baku</p>
                                        <p class="text-[10px] text-[#777777]">Sembako, sayur, daging, bumbu, cup</p>
                                    </div>
                                </button>

                                <button
                                    type="button"
                                    wire:click="$set('draft.category', 'operasional')"
                                    class="p-3 rounded-xl border text-left flex items-center gap-3 transition {{ ($draft['category'] ?? '') === 'operasional' ? 'border-[#E87559] bg-[#FCEDEA] text-[#E87559] font-bold shadow-2xs' : 'border-[#EEEEEE] bg-white text-[#555555] hover:bg-gray-50' }}"
                                >
                                    <span class="w-3 h-3 rounded-full {{ ($draft['category'] ?? '') === 'operasional' ? 'bg-[#E87559]' : 'bg-gray-300' }}"></span>
                                    <div>
                                        <p class="text-xs font-bold">Operasional</p>
                                        <p class="text-[10px] text-[#777777]">Gas LPG, listrik, es batu, galon</p>
                                    </div>
                                </button>
                            </div>
                            @error('draft.category') <p class="text-xs text-[#DC6464] mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Tanggal & Catatan -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-[#777777] uppercase tracking-wider mb-1.5">
                                    Tanggal Transaksi <span class="text-[#DC6464]">*</span>
                                </label>
                                <input
                                    type="date"
                                    wire:model="draft.transaction_date"
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-[#EEEEEE] focus:border-[#E87559] text-xs font-medium text-[#252525]"
                                />
                                @error('draft.transaction_date') <p class="text-xs text-[#DC6464] mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-[#777777] uppercase tracking-wider mb-1.5">
                                    Keterangan / Tempat Belanja
                                </label>
                                <input
                                    type="text"
                                    wire:model="draft.notes"
                                    placeholder="Contoh: Belanja di Toko Berkah"
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-[#EEEEEE] focus:border-[#E87559] text-xs font-medium text-[#252525]"
                                />
                            </div>
                        </div>

                        <!-- Rincian Barang Belanjaan -->
                        <div class="space-y-3 pt-3 border-t border-[#EEEEEE]">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-bold text-[#252525] uppercase tracking-wider flex items-center gap-1.5">
                                    <span>Rincian Barang</span>
                                    <span class="px-2 py-0.5 rounded-full bg-gray-100 text-[10px] font-semibold text-[#777777]">
                                        {{ count($draft['items'] ?? []) }} item
                                    </span>
                                </label>
                                <button
                                    type="button"
                                    wire:click="addItem"
                                    class="text-xs text-[#E87559] hover:underline font-bold flex items-center gap-1"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    <span>Tambah Barang</span>
                                </button>
                            </div>

                            <div class="space-y-2">
                                @forelse($draft['items'] ?? [] as $index => $item)
                                    <div class="p-3 rounded-xl bg-[#FBFBFC] border border-[#EEEEEE] flex flex-wrap items-center gap-2 text-xs">
                                        <div class="flex-1 min-w-[130px]">
                                            <input
                                                type="text"
                                                wire:model="draft.items.{{ $index }}.item_name"
                                                value="{{ $item['item_name'] }}"
                                                placeholder="Nama barang"
                                                class="w-full px-2.5 py-1.5 rounded-lg border border-[#EEEEEE] text-xs font-medium focus:border-[#E87559]"
                                            />
                                        </div>
                                        <div class="w-16">
                                            <input
                                                type="number"
                                                step="0.1"
                                                wire:model.lazy="draft.items.{{ $index }}.quantity"
                                                wire:change="updateItemSubtotal({{ $index }})"
                                                value="{{ $item['quantity'] }}"
                                                placeholder="Qty"
                                                class="w-full px-2 py-1.5 rounded-lg border border-[#EEEEEE] text-xs text-center focus:border-[#E87559]"
                                            />
                                        </div>
                                        <div class="w-16">
                                            <input
                                                type="text"
                                                wire:model="draft.items.{{ $index }}.unit"
                                                value="{{ $item['unit'] }}"
                                                placeholder="Satuan"
                                                class="w-full px-2 py-1.5 rounded-lg border border-[#EEEEEE] text-xs text-center focus:border-[#E87559]"
                                            />
                                        </div>
                                        <div class="w-24">
                                            <input
                                                type="number"
                                                wire:model.lazy="draft.items.{{ $index }}.unit_price"
                                                wire:change="updateItemSubtotal({{ $index }})"
                                                value="{{ $item['unit_price'] }}"
                                                placeholder="Harga"
                                                class="w-full px-2 py-1.5 rounded-lg border border-[#EEEEEE] text-xs text-right focus:border-[#E87559]"
                                            />
                                        </div>
                                        <div class="w-24 text-right font-bold text-[#252525]">
                                            Rp {{ number_format((float) ($item['subtotal'] ?? 0), 0, ',', '.') }}
                                        </div>
                                        <button
                                            type="button"
                                            wire:click="removeItem({{ $index }})"
                                            class="p-1.5 text-gray-400 hover:text-[#DC6464] hover:bg-red-50 rounded-lg transition"
                                            title="Hapus baris ini"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                @empty
                                    <p class="text-xs text-[#777777] italic py-2">
                                        Tidak ada rincian per barang (biaya dicatat sebagai pengeluaran umum).
                                    </p>
                                @endforelse
                            </div>
                        </div>

                    </div>

                    <!-- Tombol Aksi Konfirmasi Final -->
                    <div class="flex items-center justify-between gap-3 pt-6 border-t border-[#EEEEEE]">
                        <button
                            type="button"
                            wire:click="resetToRecord"
                            class="px-4 py-2.5 rounded-xl border border-[#EEEEEE] text-xs font-semibold text-[#555555] hover:bg-gray-50 flex items-center gap-1.5 transition"
                        >
                            <svg class="w-4 h-4 text-[#777777]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                            </svg>
                            <span>Rekam Ulang</span>
                        </button>

                        <button
                            type="button"
                            wire:click="saveTransaction"
                            wire:loading.attr="disabled"
                            class="px-6 py-2.5 rounded-xl bg-[#E87559] hover:bg-[#D66448] text-xs sm:text-sm font-bold text-white shadow-xs flex items-center gap-2 transition active:scale-95 disabled:opacity-50"
                        >
                            <span wire:loading.remove wire:target="saveTransaction">Simpan Pengeluaran</span>
                            <span wire:loading wire:target="saveTransaction" class="flex items-center gap-1.5">
                                <svg class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                <span>Menyimpan...</span>
                            </span>
                            <svg wire:loading.remove wire:target="saveTransaction" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                        </button>
                    </div>

                </div>

            <!-- ======================================================== -->
            <!-- TAMPILAN 3: INTERFACE PEREKAMAN SUARA & UNGGAH BERKAS    -->
            <!-- ======================================================== -->
            @else

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

                <!-- Alert Pesan Error Audio Recorder (Jika Ada) -->
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

                <!-- TAB 1: REKAM MIKROFON -->
                <div x-show="activeTab === 'record'" x-cloak class="space-y-6">

                    <!-- STATE A: IDLE (Siap Merekam) -->
                    <div x-show="state === 'idle'" class="text-center py-6 sm:py-8 space-y-5">
                        <div class="relative inline-flex items-center justify-center">
                            <button
                                type="button"
                                @click="startRecording()"
                                class="w-24 h-24 sm:w-28 sm:h-28 rounded-full bg-[#E87559] hover:bg-[#D66448] text-white flex items-center justify-center shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105 active:scale-95 group focus:outline-hidden"
                            >
                                <svg class="w-10 h-10 sm:w-12 sm:h-12 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 18.5V22"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 22h8"/>
                                </svg>
                            </button>
                        </div>

                        <div>
                            <p class="text-base sm:text-lg font-bold text-[#252525]">Tekan untuk Mulai Merekam</p>
                            <p class="text-xs text-[#777777] mt-1 max-w-sm mx-auto">
                                Contoh: "Beli telur ayam dua kilo lima puluh enam ribu sama minyak goreng dua liter tiga puluh lima ribu."
                            </p>
                        </div>
                    </div>

                    <!-- STATE B: REQUESTING PERMISSION -->
                    <div x-show="state === 'requesting_permission'" class="text-center py-8 space-y-3">
                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-[#FCEDEA] text-[#E87559] animate-pulse">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <p class="text-sm font-bold text-[#252525]">Meminta Izin Akses Mikrofon...</p>
                        <p class="text-xs text-[#777777] max-w-xs mx-auto">
                            Klik "Izinkan" atau "Allow" pada jendela pop-up peramban Anda untuk mengaktifkan rekaman.
                        </p>
                    </div>

                    <!-- STATE C: RECORDING (Sedang Merekam Suara) -->
                    <div x-show="state === 'recording'" class="py-6 sm:py-8 space-y-6">
                        <div class="text-center space-y-2">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-red-50 text-red-600 border border-red-200/60 text-xs font-semibold">
                                <span class="w-2 h-2 rounded-full bg-red-600 animate-ping"></span>
                                <span>Merekam Suara Pengguna...</span>
                            </div>

                            <div class="text-4xl sm:text-5xl font-mono font-extrabold text-[#252525] tracking-wider pt-2" x-text="formattedDuration">
                                00:00
                            </div>
                            <p class="text-[11px] text-[#777777]" x-text="`Batas maksimum durasi: ${formattedMaxDuration}`"></p>
                        </div>

                        <!-- Audio Visualizer Real-Time Waveform -->
                        <div class="flex items-center justify-center gap-1.5 h-16 px-4 bg-[#FBFBFC] rounded-2xl border border-[#EEEEEE]">
                            <template x-for="(height, idx) in visualizerBars" :key="idx">
                                <div
                                    class="w-1.5 bg-[#E87559] rounded-full transition-all duration-75"
                                    :style="`height: ${Math.max(8, height)}%; opacity: ${0.4 + (height / 100) * 0.6};`"
                                ></div>
                            </template>
                        </div>

                        <!-- Action Buttons saat Merekam -->
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

                            <div class="w-full">
                                <audio :src="audioUrl" controls class="w-full rounded-lg focus:outline-hidden"></audio>
                            </div>

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

                <!-- TAB 2: UNGGAH FILE AUDIO ALTERNATIF -->
                <div x-show="activeTab === 'upload'" x-cloak class="space-y-6">

                    <!-- Hidden Native File Input -->
                    <input
                        type="file"
                        accept="audio/*,.mp3,.wav,.m4a,.webm,.ogg,.aac"
                        class="sr-only"
                        x-ref="fileInput"
                        @change="handleFileUpload($event)"
                    />

                    <!-- Dropzone Saat Belum Ada File -->
                    <div
                        x-show="state !== 'stopped'"
                        @click="triggerFileInput()"
                        @dragover.prevent="$el.classList.add('border-[#E87559]')"
                        @dragleave.prevent="$el.classList.remove('border-[#E87559]')"
                        @drop.prevent="$el.classList.remove('border-[#E87559]'); handleFileUpload({ target: { files: $event.dataTransfer.files } })"
                        class="border-2 border-dashed border-[#EEEEEE] hover:border-[#E87559] rounded-2xl p-8 sm:p-10 text-center cursor-pointer transition-all bg-[#FBFBFC] hover:bg-white space-y-3"
                    >
                        <div class="w-14 h-14 rounded-2xl bg-[#FCEDEA] text-[#E87559] flex items-center justify-center mx-auto shadow-2xs">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                        </div>
                        <div class="space-y-1">
                            <p class="text-sm font-bold text-[#252525]">Pilih Berkas Audio dari Perangkat</p>
                            <p class="text-xs text-[#777777]">atau seret dan lepas berkas audio rekaman ke area ini</p>
                        </div>
                        <p class="text-[11px] text-[#777777] font-mono">Format didukung: MP3, WAV, M4A, WEBM, OGG (Maks. 15 MB)</p>
                    </div>

                    <!-- Audio Preview Saat File Diunggah -->
                    <div x-show="state === 'stopped'" class="py-4 space-y-5">
                        <div class="flex items-center gap-2.5 p-3 rounded-xl bg-[#DDF1EC] border border-emerald-200/60 text-emerald-800 text-xs font-semibold">
                            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>Berkas audio berhasil dipilih. Dengarkan sebelum dikirim ke AI.</span>
                        </div>

                        <div class="p-4 sm:p-5 rounded-2xl bg-[#FBFBFC] border border-[#EEEEEE] space-y-3">
                            <div class="flex items-center justify-between text-xs text-[#777777]">
                                <span class="font-medium flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-[#E87559]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                                    </svg>
                                    <span>Berkas Audio Terpilih</span>
                                </span>
                            </div>

                            <div class="w-full">
                                <audio :src="audioUrl" controls class="w-full rounded-lg focus:outline-hidden"></audio>
                            </div>

                            <div class="flex flex-wrap items-center justify-between gap-2 pt-2 text-[11px] text-[#777777] border-t border-[#EEEEEE]">
                                <span class="truncate max-w-xs font-semibold text-[#252525]" x-text="fileName"></span>
                                <span class="px-2 py-0.5 rounded-md bg-white border border-[#EEEEEE] font-mono text-[#252525]" x-text="fileSizeFormatted"></span>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-3 pt-3">
                            <button
                                type="button"
                                @click="deleteAudio()"
                                class="px-3.5 py-2 text-xs font-semibold rounded-xl text-[#DC6464] hover:bg-[#FCEDEA] transition flex items-center gap-1"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                <span>Ganti Berkas</span>
                            </button>

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

            @endif

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

                        if (typeof window !== 'undefined' && !window.isSecureContext && window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1') {
                            this.state = 'error';
                            this.errorMessage = 'Perekaman mikrofon membutuhkan koneksi aman (HTTPS). Silakan akses aplikasi melalui URL HTTPS atau localhost.';
                            return;
                        }

                        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                            this.state = 'error';
                            this.errorMessage = 'Browser Anda belum mendukung Web MediaDevices API. Silakan gunakan browser modern seperti Google Chrome, Edge, atau gunakan opsi Unggah File Audio.';
                            return;
                        }

                        if (typeof MediaRecorder === 'undefined') {
                            this.state = 'error';
                            this.errorMessage = 'Browser Anda belum mendukung MediaRecorder. Silakan gunakan opsi Unggah File Audio di bawah.';
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

                            this.mediaRecorder.start(250);
                            this.state = 'recording';
                            this.seconds = 0;

                            this.timerInterval = setInterval(() => {
                                this.seconds++;
                                if (this.seconds >= this.maxDuration) {
                                    this.stopRecording();
                                }
                            }, 1000);

                            this.setupVisualizer(this.mediaStream);
                        } catch (err) {
                            this.state = 'error';
                            if (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError') {
                                this.errorMessage = 'Izin akses mikrofon ditolak oleh pengguna atau sistem. Harap berikan izin mikrofon pada ikon gembok di bilah alamat browser.';
                            } else if (err.name === 'NotFoundError' || err.name === 'DevicesNotFoundError') {
                                this.errorMessage = 'Perangkat mikrofon tidak ditemukan. Harap sambungkan mikrofon atau gunakan opsi Unggah File Audio.';
                            } else {
                                this.errorMessage = `Gagal mengaktifkan mikrofon: ${err.message || 'Kesalahan sistem tidak dikenal.'}`;
                            }
                            this.cleanup();
                        }
                    },

                    setupVisualizer(stream) {
                        try {
                            const AudioContextClass = window.AudioContext || window.webkitAudioContext;
                            if (!AudioContextClass) return;

                            this.audioContext = new AudioContextClass();
                            const source = this.audioContext.createMediaStreamSource(stream);
                            this.analyser = this.audioContext.createAnalyser();
                            this.analyser.fftSize = 32;
                            source.connect(this.analyser);

                            const bufferLength = this.analyser.frequencyBinCount;
                            const dataArray = new Uint8Array(bufferLength);

                            const updateBars = () => {
                                if (this.state !== 'recording' || !this.analyser) return;
                                this.analyser.getByteFrequencyData(dataArray);

                                const step = Math.floor(bufferLength / 6);
                                const newBars = [];
                                for (let i = 0; i < 6; i++) {
                                    const val = dataArray[i * step] || 0;
                                    const pct = Math.min(100, Math.max(10, Math.round((val / 255) * 100)));
                                    newBars.push(pct);
                                }
                                this.visualizerBars = newBars;
                                this.animationFrame = requestAnimationFrame(updateBars);
                            };
                            updateBars();
                        } catch (e) {
                            this.visualizerBars = [20, 45, 60, 40, 25, 15];
                        }
                    },

                    stopRecording() {
                        if (this.mediaRecorder && this.state === 'recording') {
                            try {
                                this.mediaRecorder.stop();
                            } catch (e) {}
                        }
                        if (this.timerInterval) {
                            clearInterval(this.timerInterval);
                            this.timerInterval = null;
                        }
                        if (this.animationFrame) {
                            cancelAnimationFrame(this.animationFrame);
                            this.animationFrame = null;
                        }
                    },

                    cancelRecording() {
                        this.stopRecording();
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
                        if (!this.audioBlob && !this.uploadedFile) {
                            this.state = 'error';
                            this.errorMessage = 'Belum ada rekaman suara atau berkas audio yang dipilih.';
                            return;
                        }

                        const rawBlob = this.audioBlob || this.uploadedFile;
                        const fileName = this.fileName || 'rekaman-suara.webm';
                        const file = rawBlob instanceof File 
                            ? rawBlob 
                            : new File([rawBlob], fileName, { type: rawBlob.type || 'audio/webm' });

                        this.state = 'uploading';
                        this.errorMessage = '';

                        const livewire = this.$wire;
                        if (livewire && typeof livewire.upload === 'function') {
                            livewire.upload(
                                'audioFile',
                                file,
                                () => {
                                    this.state = 'idle';
                                    livewire.processVoice();
                                },
                                (err) => {
                                    this.state = 'error';
                                    this.errorMessage = 'Gagal mengunggah berkas audio: ' + (err || 'Terjadi kesalahan');
                                }
                            );
                        } else {
                            this.state = 'error';
                            this.errorMessage = 'Komponen Livewire tidak terdeteksi. Silakan muat ulang halaman.';
                        }
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
