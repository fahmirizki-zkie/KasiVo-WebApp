<div class="py-6 sm:py-10">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Card Container Utama -->
        <div class="bg-white rounded-3xl shadow-sm border border-[#EEEEEE] p-6 sm:p-8">

            <!-- 1. Header Halaman & Breadcrumb Kontekstual -->
            <div class="mb-6 sm:mb-8 pb-5 border-b border-[#EEEEEE]">
                <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-[#FCEDEA] text-[#E87559] border border-[#E87559]/20 shadow-xs">
                        <svg class="w-3.5 h-3.5 text-[#E87559]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span>Pencatatan Bukti Foto</span>
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
                    Catat Pengeluaran via Foto Nota
                </h1>
                <p class="text-xs sm:text-sm text-[#777777] mt-1.5 leading-relaxed">
                    Potret langsung nota belanjaan dari kamera ponsel atau unggah struk. Vision AI KasiVo akan membaca nama barang, harga, dan total belanja secara otomatis.
                </p>
            </div>

            <!-- Pesan Error Backend / Livewire -->
            @if($errorMessage)
                @if($errorType === 'quota_exceeded')
                    <div class="mb-6 p-4 rounded-2xl bg-red-50 border-2 border-red-200 text-[#252525] text-xs sm:text-sm flex flex-col sm:flex-row items-start justify-between gap-4 shadow-sm">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-xl bg-red-100 text-red-600 flex items-center justify-center shrink-0">
                                <span class="text-base">⚠️</span>
                            </div>
                            <div class="flex-1">
                                <p class="font-extrabold text-red-700 text-sm">Kuota AI Anda Sudah Penuh / Token AI Anda Sudah Habis</p>
                                <p class="mt-1 text-red-900 leading-relaxed text-xs">
                                    Layanan AI mencapai batas panggilan saat ini. Jangan khawatir, foto nota Anda tidak hilang! Klik tombol di samping untuk mencatat transaksi ini secara langsung.
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 w-full sm:w-auto shrink-0 pt-2 sm:pt-0">
                            <button type="button" 
                                    wire:click="proceedToEmergencyReview"
                                    class="flex-1 sm:flex-none px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-bold rounded-xl text-xs transition shadow-xs flex items-center justify-center gap-1.5">
                                <span>✍️</span>
                                <span>Koreksi Nominal Langsung</span>
                            </button>
                            <button type="button" 
                                    wire:click="retryProcess"
                                    class="px-3 py-2.5 bg-white hover:bg-gray-50 border border-red-300 text-red-700 font-semibold rounded-xl text-xs transition">
                                🔄 Coba Ulang
                            </button>
                        </div>
                    </div>
                @else
                    <div class="mb-6 p-4 rounded-2xl bg-[#FCEDEA] border border-[#E87559]/20 text-[#252525] text-xs sm:text-sm flex flex-col sm:flex-row items-start justify-between gap-4">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-xl bg-white text-[#DC6464] flex items-center justify-center shrink-0 shadow-xs">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="font-bold text-[#DC6464]">Perhatian</p>
                                <p class="mt-0.5 text-[#555555] leading-relaxed">{{ $errorMessage }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 w-full sm:w-auto shrink-0">
                            <button type="button" 
                                    wire:click="proceedToEmergencyReview"
                                    class="flex-1 sm:flex-none px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs transition">
                                ✍️ Koreksi Nominal Langsung
                            </button>
                            <button type="button" 
                                    wire:click="$set('errorMessage', null)" 
                                    class="px-3 py-2 text-xs text-[#777777] hover:underline font-semibold">Tutup</button>
                        </div>
                    </div>
                @endif
            @endif

            <!-- ======================================================== -->
            <!-- TAMPILAN 1: PROSES VISION AI SEDANG MEMINDAI NOTA        -->
            <!-- ======================================================== -->
            @if($isProcessing)
                <div class="py-12 text-center space-y-6">
                    <div class="relative inline-flex items-center justify-center">
                        <div class="w-20 h-20 rounded-full bg-[#FCEDEA] animate-ping absolute opacity-40"></div>
                        <div class="w-16 h-16 rounded-full bg-[#E87559] text-white flex items-center justify-center shadow-md relative z-10">
                            <svg class="w-8 h-8 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="space-y-1.5">
                        <h3 class="text-lg font-bold text-[#252525]">Vision AI Sedang Memindai Nota...</h3>
                        <p class="text-xs sm:text-sm text-[#777777] max-w-md mx-auto leading-relaxed">
                            Membaca teks nota belanjaan, mengekstrak item barang, dan memverifikasi kejelasan angka total (< 2 detik).
                        </p>
                    </div>
                    <div class="flex items-center justify-center gap-1.5 text-xs text-[#E87559] font-medium">
                        <svg class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <span>Multimodal Groq LPU</span>
                    </div>
                </div>

            <!-- ======================================================== -->
            <!-- TAMPILAN 2: LAYAR KONFIRMASI REVIEW HASIL VISION AI      -->
            <!-- ======================================================== -->
            @elseif($step === 'review')
                <div class="space-y-6">

                    <!-- Pratinjau Thumbnail Foto Struk Asli -->
                    <div class="p-4 rounded-2xl bg-[#FBFBFC] border border-[#EEEEEE] flex flex-col sm:flex-row items-center gap-4">
                        @if($photo && method_exists($photo, 'isPreviewable') && $photo->isPreviewable())
                            <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-xl overflow-hidden bg-gray-100 border border-[#EEEEEE] shrink-0 shadow-2xs">
                                <img src="{{ $photo->temporaryUrl() }}" alt="Pratinjau Nota" class="w-full h-full object-cover" />
                            </div>
                        @endif
                        <div class="flex-1 text-center sm:text-left space-y-1">
                            <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2">
                                <span class="text-xs font-bold text-[#252525]">Bukti Struk Belanja</span>
                                <span class="text-[10px] px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-semibold">Terekstrak Vision AI</span>
                            </div>
                            <p class="text-xs text-[#777777] leading-relaxed">
                                Foto nota ini akan otomatis disimpan sebagai bukti transaksi sah untuk laporan keuangan dan pajak usaha Anda.
                            </p>
                        </div>
                    </div>

                    <!-- Warning Deteksi Struk Buram / Needs Review (KV-031) -->
                    @if(!empty($draft['needs_review']))
                        <div class="p-3.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <div class="leading-relaxed">
                                <strong class="font-semibold text-amber-800">Perhatian Kualitas Nota:</strong>
                                <p class="mt-0.5">{{ $draft['review_reason'] ?: 'Tulisan nota atau angka belanjaan membutuhkan konfirmasi manual Anda.' }}</p>
                            </div>
                        </div>
                    @endif

                    <!-- Form Konfirmasi Transaksi -->
                    <div class="space-y-5 pt-2">

                        <!-- Total Pengeluaran Nota -->
                        <div>
                            <label class="block text-xs font-bold text-[#777777] uppercase tracking-wider mb-1.5">
                                Total Pengeluaran Struk (Rp) <span class="text-[#DC6464]">*</span>
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
                                        <p class="text-[10px] text-[#777777]">Sembako, daging, sayur, bumbu, cup</p>
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

                        <!-- Tanggal & Catatan / Nama Toko -->
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
                                    Nama Toko / Keterangan
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

                        <!-- Rincian Barang Belanjaan dari Nota -->
                        <div class="space-y-3 pt-3 border-t border-[#EEEEEE]">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-bold text-[#252525] uppercase tracking-wider flex items-center gap-1.5">
                                    <span>Rincian Barang dari Nota</span>
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
                                        Tidak ada rincian item individual dari nota (dicatat sebagai pengeluaran umum).
                                    </p>
                                @endforelse
                            </div>

                            {{-- Banner Selisih Rekonsiliasi Matematika Item vs Total --}}
                            @if($mathReconciliation && !$mathReconciliation['is_matched'])
                                <div class="mt-3 p-3.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
                                    <div class="flex items-center gap-2">
                                        <span class="text-base">⚠️</span>
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
                            wire:click="resetToUpload"
                            class="px-4 py-2.5 rounded-xl border border-[#EEEEEE] text-xs font-semibold text-[#555555] hover:bg-gray-50 flex items-center gap-1.5 transition"
                        >
                            <svg class="w-4 h-4 text-[#777777]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span>Foto Ulang</span>
                        </button>

                        <button
                            type="button"
                            wire:click="saveTransaction"
                            wire:loading.attr="disabled"
                            class="px-6 py-2.5 rounded-xl bg-[#E87559] hover:bg-[#D66448] text-xs sm:text-sm font-bold text-white shadow-xs flex items-center gap-2 transition active:scale-95 disabled:opacity-50"
                        >
                            <span wire:loading.remove wire:target="saveTransaction">Simpan Transaksi & Bukti</span>
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
            <!-- TAMPILAN 3: INTERFACE KAMERA & UNGGAH BERKAS             -->
            <!-- ======================================================== -->
            @else
                <div class="space-y-6">

                    <!-- Hidden Inputs untuk Kamera dan File Picker -->
                    <input
                        type="file"
                        accept="image/jpeg,image/png,image/jpg,image/webp"
                        capture="environment"
                        class="sr-only"
                        id="cameraInput"
                        wire:model="photo"
                    />

                    <input
                        type="file"
                        accept="image/jpeg,image/png,image/jpg,image/webp"
                        class="sr-only"
                        id="galleryInput"
                        wire:model="photo"
                    />

                    <!-- Status Jika Belum Ada Foto yang Dipilih -->
                    @if(!$photo || (method_exists($photo, 'isPreviewable') && !$photo->isPreviewable()))
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Opsi 1: Kamera HP Langsung -->
                            <button
                                type="button"
                                onclick="document.getElementById('cameraInput').click()"
                                class="p-6 sm:p-8 rounded-2xl border-2 border-dashed border-[#EEEEEE] hover:border-[#E87559] bg-[#FBFBFC] hover:bg-white text-center cursor-pointer transition-all space-y-3 group"
                            >
                                <div class="w-14 h-14 rounded-2xl bg-[#FCEDEA] text-[#E87559] flex items-center justify-center mx-auto shadow-2xs group-hover:scale-105 transition-transform">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </div>
                                <div class="space-y-1">
                                    <p class="text-sm font-bold text-[#252525] group-hover:text-[#E87559] transition-colors">Ambil Foto via Kamera</p>
                                    <p class="text-xs text-[#777777]">Jepret langsung struk fisik dengan kamera ponsel</p>
                                </div>
                            </button>

                            <!-- Opsi 2: Unggah Berkas / Galeri -->
                            <button
                                type="button"
                                onclick="document.getElementById('galleryInput').click()"
                                class="p-6 sm:p-8 rounded-2xl border-2 border-dashed border-[#EEEEEE] hover:border-[#E87559] bg-[#FBFBFC] hover:bg-white text-center cursor-pointer transition-all space-y-3 group"
                            >
                                <div class="w-14 h-14 rounded-2xl bg-gray-100 text-[#777777] flex items-center justify-center mx-auto shadow-2xs group-hover:scale-105 transition-transform">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <div class="space-y-1">
                                    <p class="text-sm font-bold text-[#252525] group-hover:text-[#E87559] transition-colors">Pilih dari Galeri</p>
                                    <p class="text-xs text-[#777777]">Unggah tangkapan layar struk (JPG, PNG, WEBP)</p>
                                </div>
                            </button>
                        </div>
                        @error('photo') <p class="text-xs text-[#DC6464] text-center mt-2">{{ $message }}</p> @enderror

                    <!-- Status Jika Foto Sudah Dipilih (Preview Card) -->
                    @else
                        <div class="p-4 sm:p-5 rounded-2xl bg-[#FBFBFC] border border-[#EEEEEE] space-y-4">
                            <div class="flex items-center justify-between text-xs text-[#777777]">
                                <span class="font-bold text-[#252525] flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span>Foto Nota Siap Diproses</span>
                                </span>
                                <span>Maks. 5 MB</span>
                            </div>

                            <!-- Image Preview Frame -->
                            <div class="w-full max-h-80 rounded-xl overflow-hidden bg-black/5 border border-[#EEEEEE] flex items-center justify-center">
                                <img src="{{ $photo->temporaryUrl() }}" alt="Preview Nota" class="max-h-80 w-auto object-contain rounded-lg" />
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex flex-wrap items-center justify-between gap-3 pt-2 border-t border-[#EEEEEE]">
                                <button
                                    type="button"
                                    onclick="document.getElementById('galleryInput').click()"
                                    class="px-3.5 py-2 text-xs font-semibold rounded-xl text-[#252525] bg-gray-100 hover:bg-gray-200 transition flex items-center gap-1.5"
                                >
                                    <svg class="w-3.5 h-3.5 text-[#777777]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                    <span>Ganti Foto</span>
                                </button>

                                <button
                                    type="button"
                                    wire:click="processPhoto"
                                    wire:loading.attr="disabled"
                                    class="px-5 py-2.5 text-xs sm:text-sm font-bold text-white bg-[#E87559] hover:bg-[#D66448] rounded-xl shadow-xs transition active:scale-95 disabled:opacity-50 flex items-center gap-2"
                                >
                                    <span wire:loading.remove wire:target="processPhoto">Ekstrak Nota dengan AI</span>
                                    <span wire:loading wire:target="processPhoto" class="flex items-center gap-1.5">
                                        <svg class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                        </svg>
                                        <span>Memproses...</span>
                                    </span>
                                    <svg wire:loading.remove wire:target="processPhoto" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @endif

                </div>
            @endif

        </div>

        <!-- 4. Kotak Tips & Panduan Pemotretan Struk -->
        <div class="mt-6 p-5 rounded-2xl bg-white border border-[#EEEEEE] shadow-2xs space-y-3">
            <h4 class="text-xs font-bold text-[#252525] flex items-center gap-1.5 uppercase tracking-wider">
                <svg class="w-4 h-4 text-[#E87559]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                </svg>
                <span>Panduan Foto Struk KasiVo</span>
            </h4>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs text-[#777777]">
                <div class="p-3 rounded-xl bg-[#FBFBFC] border border-[#EEEEEE]/80 space-y-1">
                    <p class="font-semibold text-[#252525]">1. Cahaya Cukup</p>
                    <p class="leading-relaxed">Pastikan nota tidak terkena bayangan gelap agar teks terbaca jelas oleh Vision AI.</p>
                </div>
                <div class="p-3 rounded-xl bg-[#FBFBFC] border border-[#EEEEEE]/80 space-y-1">
                    <p class="font-semibold text-[#252525]">2. Total Nota Terlihat</p>
                    <p class="leading-relaxed">Pastikan bagian bawah struk yang memuat total pembayaran tidak terpotong.</p>
                </div>
                <div class="p-3 rounded-xl bg-[#FBFBFC] border border-[#EEEEEE]/80 space-y-1">
                    <p class="font-semibold text-[#252525]">3. Tetap Bisa Dikoreksi</p>
                    <p class="leading-relaxed">Jika nota buram, AI akan memberi tanda dan kamu bisa mengoreksi angka di layar review.</p>
                </div>
            </div>
        </div>

    </div>

    {{-- Modal Peringatan Transaksi Kembar / Duplikasi --}}
    @include('livewire.partials.duplicate-warning-modal')
</div>
