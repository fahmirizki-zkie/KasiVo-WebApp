<div class="py-6 sm:py-10">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Card Container Utama -->
        <div class="bg-white rounded-3xl shadow-sm border border-[#EEEEEE] p-6 sm:p-8">

            <!-- 1. Header Halaman & Breadcrumb Kontekstual -->
            <div class="mb-6 sm:mb-8 pb-5 border-b border-[#EEEEEE]">
                <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-[#FCEDEA] text-[#E87559] border border-[#E87559]/20 shadow-xs">
                        <svg class="w-3.5 h-3.5 text-[#E87559]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                        </svg>
                        <span>Pencatatan Cepat Teks</span>
                    </span>

                    @include('livewire.partials.expense-mode-switcher', ['currentMode' => 'text'])
                </div>

                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#252525] tracking-tight">
                    Catat Transaksi via Teks Bebas
                </h1>
                <p class="text-xs sm:text-sm text-[#777777] mt-1.5 leading-relaxed">
                    Tuliskan catatan transaksi belanja warung atau omzet harianmu dengan kalimat santai. AI KasiVo akan mengubahnya menjadi catatan terstruktur otomatis.
                </p>
            </div>

            <!-- Tab Pintas Navigasi 3 Moda Pengeluaran -->
            @include('livewire.partials.expense-mode-tabs', ['currentMode' => 'text'])

            <!-- Pesan Error Backend / Livewire (Layout Responsif & Terstruktur) -->
            @if($errorMessage)
                @php
                    $isQuota = $errorType === 'quota_exceeded';
                    $isApiKeyMissing = str_contains(strtolower($errorMessage), 'api key') || str_contains(strtolower($errorMessage), 'groq_api_key');
                @endphp

                <div class="relative w-full mb-6 p-5 sm:p-6 rounded-2xl border transition-all shadow-xs {{ $isQuota ? 'bg-amber-50 border-amber-200' : 'bg-[#FCEDEA]/80 border-[#E87559]/30' }}"
                     role="alert">

                    <!-- Tombol Tutup di Kanan Atas -->
                    <button type="button"
                            wire:click="$set('errorMessage', null)"
                            class="absolute top-4 right-4 p-2 rounded-xl text-[#777777] hover:text-[#252525] hover:bg-black/5 transition-colors focus:outline-none"
                            title="Tutup pesan"
                            aria-label="Tutup pesan">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>

                    <!-- Konten Alert: Icon Kiri + Area Teks Utama -->
                    <div class="flex items-start gap-3.5 sm:gap-4 pr-8">
                        <!-- Icon Peringatan di Sebelah Kiri -->
                        <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-2xl flex items-center justify-center shrink-0 mt-0.5 shadow-2xs {{ $isQuota ? 'bg-amber-100 text-amber-700' : 'bg-white text-[#DC6464] border border-[#E87559]/20' }}">
                            <svg class="w-5 h-5 sm:w-5.5 sm:h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>

                        <!-- Area Konten Utama -->
                        <div class="flex-1 min-w-0">
                            <!-- Judul Alert -->
                            <h4 class="text-sm sm:text-base font-extrabold {{ $isQuota ? 'text-amber-900' : 'text-[#252525]' }}">
                                @if($isQuota)
                                    Kuota AI Anda Sudah Penuh / Token AI Anda Sudah Habis
                                @else
                                    Perhatian
                                @endif
                            </h4>

                            <!-- Deskripsi Pesan Error Ramah Pengguna -->
                            <div class="mt-1 text-xs sm:text-sm text-[#555555] leading-relaxed space-y-1">
                                @if($isQuota)
                                    <p>Layanan AI mencapai batas panggilan saat ini. Jangan khawatir, catatan teks Anda tidak hilang! Anda masih bisa mencatat transaksi ini secara langsung.</p>
                                @elseif($isApiKeyMissing)
                                    <p class="font-semibold text-[#252525]">AI Parsing belum dapat digunakan.</p>
                                    <p class="text-xs text-[#777777]">Konfigurasi GROQ_API_KEY belum tersedia di sistem. Catatan teks Anda tetap aman.</p>
                                @else
                                    <p class="font-semibold text-[#252525]">AI belum dapat memproses catatan teks ini.</p>
                                    <p class="text-xs text-[#777777]">{{ $errorMessage }}. Catatan teks Anda tetap aman.</p>
                                @endif
                            </div>

                            <!-- Tombol Aksi di Bawah Deskripsi (Tinggi Min 48px) -->
                            <div class="mt-4 pt-1 flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5">
                                <button type="button"
                                        wire:click="proceedToEmergencyReview"
                                        class="min-h-[48px] px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-bold rounded-xl text-xs sm:text-sm transition shadow-xs flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                    </svg>
                                    <span>Lanjut Catat Manual (Koreksi Nominal Langsung)</span>
                                </button>

                                <button type="button"
                                        wire:click="retryProcess"
                                        class="min-h-[48px] px-4 py-2.5 bg-white hover:bg-gray-50 active:bg-gray-100 border border-[#EEEEEE] hover:border-[#E87559]/30 text-[#252525] font-semibold rounded-xl text-xs sm:text-sm transition shadow-2xs flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4 text-[#777777]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                    <span>Coba Lagi Nanti</span>
                                </button>
                            </div>
                        </div>
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
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="space-y-1.5">
                        <h3 class="text-lg font-bold text-[#252525]">AI KasiVo Sedang Membaca Catatanmu...</h3>
                        <p class="text-xs sm:text-sm text-[#777777] max-w-md mx-auto leading-relaxed">
                            Menganalisis kalimat kasir, mengidentifikasi rincian barang, dan menghitung nominal transaksi secara instan.
                        </p>
                    </div>
                    <div class="flex items-center justify-center gap-1.5 text-xs text-[#E87559] font-medium">
                        <svg class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <span>Inference Groq LPU (< 1 detik)</span>
                    </div>
                </div>

            <!-- ======================================================== -->
            <!-- TAMPILAN 2: LAYAR KONFIRMASI REVIEW DRAFT HASIL AI       -->
            <!-- ======================================================== -->
            @elseif($step === 'review')
                <div class="space-y-6">

                    <!-- Bubble Catatan Asli Pengguna -->
                    @if($rawText)
                        <div class="p-4 rounded-2xl bg-[#FCEDEA]/50 border border-[#E87559]/20 flex items-start gap-3">
                            <div class="w-8 h-8 rounded-xl bg-[#E87559] text-white flex items-center justify-center shrink-0 shadow-xs">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <p class="text-xs font-bold text-[#E87559]">Catatan Kalimat Asli:</p>
                                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-semibold">Terekstrak AI</span>
                                </div>
                                <p class="text-xs sm:text-sm font-medium text-[#252525] mt-1 italic leading-relaxed">
                                    "{{ $rawText }}"
                                </p>
                            </div>
                        </div>
                    @endif

                    <!-- Warning jika data butuh perhatian -->
                    @if(!empty($draft['needs_review']))
                        <div class="p-3.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <p class="leading-relaxed">
                                <strong>Perlu Ditinjau:</strong> {{ $draft['review_reason'] ?: 'Pastikan nominal dan rincian belanja sudah sesuai dengan maksud Anda.' }}
                            </p>
                        </div>
                    @endif

                    <!-- Form Konfirmasi Transaksi -->
                    <div class="space-y-5 pt-2">

                        <!-- Pilihan Tipe Transaksi (Kas Keluar vs Kas Masuk) -->
                        <div>
                            <label class="block text-xs font-bold text-[#777777] uppercase tracking-wider mb-1.5">
                                Jenis Transaksi <span class="text-[#DC6464]">*</span>
                            </label>
                            <div class="grid grid-cols-2 gap-3">
                                <button
                                    type="button"
                                    wire:click="$set('draft.type', 'cash_out')"
                                    class="p-3 rounded-xl border text-left flex items-center gap-3 transition {{ ($draft['type'] ?? '') === 'cash_out' ? 'border-[#E87559] bg-[#FCEDEA] text-[#E87559] font-bold shadow-2xs' : 'border-[#EEEEEE] bg-white text-[#555555] hover:bg-gray-50' }}"
                                >
                                    <span class="w-3 h-3 rounded-full {{ ($draft['type'] ?? '') === 'cash_out' ? 'bg-[#E87559]' : 'bg-gray-300' }}"></span>
                                    <div>
                                        <p class="text-xs font-bold">Kas Keluar (Pengeluaran)</p>
                                        <p class="text-[10px] text-[#777777]">Belanja bahan baku, operasional, gaji</p>
                                    </div>
                                </button>

                                <button
                                    type="button"
                                    wire:click="$set('draft.type', 'cash_in')"
                                    class="p-3 rounded-xl border text-left flex items-center gap-3 transition {{ ($draft['type'] ?? '') === 'cash_in' ? 'border-emerald-500 bg-[#DDF1EC] text-emerald-800 font-bold shadow-2xs' : 'border-[#EEEEEE] bg-white text-[#555555] hover:bg-gray-50' }}"
                                >
                                    <span class="w-3 h-3 rounded-full {{ ($draft['type'] ?? '') === 'cash_in' ? 'bg-emerald-600' : 'bg-gray-300' }}"></span>
                                    <div>
                                        <p class="text-xs font-bold">Kas Masuk (Penjualan)</p>
                                        <p class="text-[10px] text-[#777777]">Omzet harian, pesanan katering, QRIS</p>
                                    </div>
                                </button>
                            </div>
                        </div>

                        <!-- Total Transaksi -->
                        <div>
                            <label class="block text-xs font-bold text-[#777777] uppercase tracking-wider mb-1.5">
                                Total Nominal (Rp) <span class="text-[#DC6464]">*</span>
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

                        <!-- Kategori Transaksi -->
                        <div>
                            <label class="block text-xs font-bold text-[#777777] uppercase tracking-wider mb-1.5">
                                Kategori <span class="text-[#DC6464]">*</span>
                            </label>
                            @if(($draft['type'] ?? '') === 'cash_out')
                                <div class="grid grid-cols-2 gap-3">
                                    <button
                                        type="button"
                                        wire:click="$set('draft.category', 'bahan_baku')"
                                        class="p-3 rounded-xl border text-left flex items-center gap-3 transition {{ ($draft['category'] ?? '') === 'bahan_baku' ? 'border-[#E87559] bg-[#FCEDEA] text-[#E87559] font-bold shadow-2xs' : 'border-[#EEEEEE] bg-white text-[#555555] hover:bg-gray-50' }}"
                                    >
                                        <span class="w-2.5 h-2.5 rounded-full {{ ($draft['category'] ?? '') === 'bahan_baku' ? 'bg-[#E87559]' : 'bg-gray-300' }}"></span>
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
                                        <span class="w-2.5 h-2.5 rounded-full {{ ($draft['category'] ?? '') === 'operasional' ? 'bg-[#E87559]' : 'bg-gray-300' }}"></span>
                                        <div>
                                            <p class="text-xs font-bold">Operasional</p>
                                            <p class="text-[10px] text-[#777777]">Gas LPG, listrik, es batu, galon</p>
                                        </div>
                                    </button>
                                </div>
                            @else
                                <div class="p-3 rounded-xl border border-emerald-500 bg-[#DDF1EC] text-emerald-800 flex items-center gap-3">
                                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-600"></span>
                                    <div>
                                        <p class="text-xs font-bold">Penjualan Harian Warung</p>
                                        <p class="text-[10px] text-emerald-700">Omzet uang masuk dari transaksi pelanggan</p>
                                    </div>
                                </div>
                            @endif
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
                                    value="{{ $draft['transaction_date'] ?? '' }}"
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-[#EEEEEE] focus:border-[#E87559] text-xs font-medium text-[#252525]"
                                />
                                @error('draft.transaction_date') <p class="text-xs text-[#DC6464] mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-[#777777] uppercase tracking-wider mb-1.5">
                                    Catatan / Tempat Belanja
                                </label>
                                <input
                                    type="text"
                                    wire:model="draft.notes"
                                    value="{{ $draft['notes'] ?? '' }}"
                                    placeholder="Contoh: Belanja di Toko Berkah"
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-[#EEEEEE] focus:border-[#E87559] text-xs font-medium text-[#252525]"
                                />
                            </div>
                        </div>

                        <!-- Rincian Barang Belanjaan (Jika Ada / Tipe Pengeluaran) -->
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
                                                value="{{ $item['item_name'] ?? '' }}"
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
                                                value="{{ $item['quantity'] ?? 1 }}"
                                                placeholder="Qty"
                                                class="w-full px-2 py-1.5 rounded-lg border border-[#EEEEEE] text-xs text-center focus:border-[#E87559]"
                                            />
                                        </div>
                                        <div class="w-16">
                                            <input
                                                type="text"
                                                wire:model="draft.items.{{ $index }}.unit"
                                                value="{{ $item['unit'] ?? 'pcs' }}"
                                                placeholder="Satuan"
                                                class="w-full px-2 py-1.5 rounded-lg border border-[#EEEEEE] text-xs text-center focus:border-[#E87559]"
                                            />
                                        </div>
                                        <div class="w-24">
                                            <input
                                                type="number"
                                                wire:model.lazy="draft.items.{{ $index }}.unit_price"
                                                wire:change="updateItemSubtotal({{ $index }})"
                                                value="{{ $item['unit_price'] ?? 0 }}"
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
                                        Tidak ada rincian per barang (dicatat sebagai transaksi umum).
                                    </p>
                                @endforelse
                            </div>

                            {{-- Banner Selisih Rekonsiliasi Matematika Item vs Total --}}
                            @if($mathReconciliation && !$mathReconciliation['is_matched'])
                                <div class="mt-3 p-3.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                        </svg>
                                        <div>
                                            <p class="font-bold">Total rincian barang berbeda dengan Total Transaksi</p>
                                            <p class="text-[11px] text-amber-800">
                                                Rincian barang: <strong>{{ $mathReconciliation['formatted_items_sum'] }}</strong> | Total Transaksi: <strong>{{ $mathReconciliation['formatted_total_amount'] }}</strong> (Selisih: {{ $mathReconciliation['formatted_difference'] }})
                                            </p>
                                        </div>
                                    </div>
                                    <button type="button" 
                                            wire:click="syncTotalWithItemsSum" 
                                            class="px-3 py-1.5 bg-amber-600 hover:bg-amber-700 active:bg-amber-800 text-white rounded-lg text-xs font-bold transition shadow-xs self-start sm:self-auto flex-shrink-0">
                                        Sesuaikan Total ke {{ $mathReconciliation['formatted_items_sum'] }}
                                    </button>
                                </div>
                            @endif
                        </div>

                    </div>

                    <!-- Tombol Aksi Konfirmasi Final -->
                    <div class="flex items-center justify-between gap-3 pt-6 border-t border-[#EEEEEE]">
                        <button
                            type="button"
                            wire:click="resetToInput"
                            class="px-4 py-2.5 rounded-xl border border-[#EEEEEE] text-xs font-semibold text-[#555555] hover:bg-gray-50 flex items-center gap-1.5 transition"
                        >
                            <svg class="w-4 h-4 text-[#777777]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                            </svg>
                            <span>Tulis Ulang</span>
                        </button>

                        <button
                            type="button"
                            wire:click="saveTransaction"
                            wire:loading.attr="disabled"
                            class="px-6 py-2.5 rounded-xl bg-[#E87559] hover:bg-[#D66448] text-xs sm:text-sm font-bold text-white shadow-xs flex items-center gap-2 transition active:scale-95 disabled:opacity-50"
                        >
                            <span wire:loading.remove wire:target="saveTransaction">Simpan Transaksi</span>
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
            <!-- TAMPILAN 3: FORMULIR INPUT TEKS BEBAS                     -->
            <!-- ======================================================== -->
            @else
                <form wire:submit="processText" class="space-y-6">

                    <!-- Textarea Input Utama -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label for="rawText" class="block text-xs font-bold text-[#777777] uppercase tracking-wider">
                                Tuliskan Catatan Transaksi <span class="text-[#DC6464]">*</span>
                            </label>
                            <span class="text-[11px] text-[#777777]">
                                Minimal 5 karakter
                            </span>
                        </div>

                        <div class="relative">
                            <textarea
                                id="rawText"
                                wire:model="rawText"
                                rows="5"
                                placeholder="Contoh: Tadi belanja telur ayam 2kg 56rb sama minyak goreng 2 liter 35rb di Toko Berkah"
                                class="w-full p-4 rounded-2xl border border-[#EEEEEE] focus:border-[#E87559] focus:ring-1 focus:ring-[#E87559] text-sm text-[#252525] placeholder:text-[#999999] leading-relaxed resize-none transition"
                            ></textarea>
                        </div>
                        @error('rawText') <p class="text-xs text-[#DC6464] mt-1.5">{{ $message }}</p> @enderror
                    </div>

                    <!-- Template Chips Cepat (Quick Example Chips) -->
                    <div class="space-y-2">
                        <p class="text-[11px] font-semibold text-[#777777] uppercase tracking-wider flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-[#E87559]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            <span>Klik Contoh Kalimat di Bawah untuk Coba Cepat:</span>
                        </p>
                        <div class="flex flex-wrap gap-2 text-xs">
                            <button
                                type="button"
                                wire:click="applyTemplate('Tadi sore beli telur ayam 2kg 56rb sama minyak goreng 2L 35rb di Toko Berkah')"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-[#FBFBFC] hover:bg-[#FCEDEA] border border-[#EEEEEE] hover:border-[#E87559]/30 text-[#555555] hover:text-[#E87559] font-medium transition text-left shadow-2xs"
                            >
                                <svg class="w-3.5 h-3.5 text-[#E87559] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2 11h20M3.5 11l1.6 7.4a2 2 0 002 1.6h9.8a2 2 0 002-1.6l1.6-7.4M4.5 15.5h15M5 11l4-7M19 11l-4-7M9 11l1 9M15 11l-1 9"/>
                                </svg>
                                <span>Telur & Minyak Goreng</span>
                            </button>

                            <button
                                type="button"
                                wire:click="applyTemplate('Beli gas elpiji 3kg 2 tabung total 44rb tadi pagi')"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-[#FBFBFC] hover:bg-[#FCEDEA] border border-[#EEEEEE] hover:border-[#E87559]/30 text-[#555555] hover:text-[#E87559] font-medium transition text-left shadow-2xs"
                            >
                                <svg class="w-3.5 h-3.5 text-[#E87559] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.5 14.5A2.5 2.5 0 0011 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 11-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 002.5 2.5z"/>
                                </svg>
                                <span>Gas Elpiji 3kg</span>
                            </button>

                            <button
                                type="button"
                                wire:click="applyTemplate('Beli cup plastik 16oz 1 dus 120rb sama sedotan 15rb')"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-[#FBFBFC] hover:bg-[#FCEDEA] border border-[#EEEEEE] hover:border-[#E87559]/30 text-[#555555] hover:text-[#E87559] font-medium transition text-left shadow-2xs"
                            >
                                <svg class="w-3.5 h-3.5 text-[#E87559] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m7.5 4.27 9 5.15M21 8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m3.3 7 8.7 5 8.7-5M12 22V12"/>
                                </svg>
                                <span>Cup Plastik & Sedotan</span>
                            </button>

                            <button
                                type="button"
                                wire:click="applyTemplate('Alhamdulillah penjualan warung hari ini tembus 850rb')"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-[#FBFBFC] hover:bg-[#DDF1EC] border border-[#EEEEEE] hover:border-emerald-300 text-[#555555] hover:text-emerald-800 font-medium transition text-left shadow-2xs"
                            >
                                <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12V7H5a2 2 0 010-4h14v4"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 5v14a2 2 0 002 2h16v-5"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 12a2 2 0 000 4h4v-4z"/>
                                </svg>
                                <span>Omzet Penjualan 850rb</span>
                            </button>
                        </div>
                    </div>

                    <!-- Tombol Submit Ekstraksi AI -->
                    <div class="pt-4 border-t border-[#EEEEEE] flex items-center justify-end">
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            class="w-full sm:w-auto px-6 py-3 rounded-2xl bg-[#E87559] hover:bg-[#D66448] text-xs sm:text-sm font-bold text-white shadow-sm hover:shadow-md transition active:scale-95 disabled:opacity-50 flex items-center justify-center gap-2"
                        >
                            <span wire:loading.remove wire:target="processText">Ekstrak Catatan dengan AI</span>
                            <span wire:loading wire:target="processText" class="flex items-center gap-2">
                                <svg class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                <span>Mengekstrak AI...</span>
                            </span>
                            <svg wire:loading.remove wire:target="processText" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </button>
                    </div>

                </form>
            @endif

        </div>

        <!-- 4. Kotak Tips & Panduan -->
        <div class="mt-6 p-5 rounded-2xl bg-white border border-[#EEEEEE] shadow-2xs space-y-3">
            <h4 class="text-xs font-bold text-[#252525] flex items-center gap-1.5 uppercase tracking-wider">
                <svg class="w-4 h-4 text-[#E87559]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                </svg>
                <span>Tips Menulis Catatan Kas KasiVo</span>
            </h4>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs text-[#777777]">
                <div class="p-3 rounded-xl bg-[#FBFBFC] border border-[#EEEEEE]/80 space-y-1">
                    <p class="font-semibold text-[#252525]">1. Bahasa Santai</p>
                    <p class="leading-relaxed">Tulis bebas seperti chat WhatsApp biasa: "50rb", "25k", "1.5jt", dll.</p>
                </div>
                <div class="p-3 rounded-xl bg-[#FBFBFC] border border-[#EEEEEE]/80 space-y-1">
                    <p class="font-semibold text-[#252525]">2. Rincian & Kuantitas</p>
                    <p class="leading-relaxed">Cantumkan jumlah barang bila ada (misal: "2kg", "3 tabung", "1 dus") agar dihitung otomatis.</p>
                </div>
                <div class="p-3 rounded-xl bg-[#FBFBFC] border border-[#EEEEEE]/80 space-y-1">
                    <p class="font-semibold text-[#252525]">3. Tetap Bisa Dikoreksi</p>
                    <p class="leading-relaxed">Sebelum tersimpan permanen ke kas, kamu selalu bisa meninjau dan mengubah angka di layar review.</p>
                </div>
            </div>
        </div>

    </div>

    {{-- Modal Peringatan Transaksi Kembar / Duplikasi --}}
    @include('livewire.partials.duplicate-warning-modal')
</div>

