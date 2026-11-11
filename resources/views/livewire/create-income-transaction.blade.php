<div class="py-6 sm:py-10">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="bg-white rounded-2xl shadow-sm border border-[#EEEEEE] p-6 sm:p-8">

            <!-- Header Form -->
            <div class="mb-6 sm:mb-8 pb-5 border-b border-[#EEEEEE]">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-[#DDF1EC] text-emerald-700 border border-emerald-200/50 mb-3 shadow-xs">
                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Kas Masuk (Penjualan)</span>
                </span>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#252525] tracking-tight">
                    Catat Penjualan Harian
                </h1>
                <p class="text-xs sm:text-sm text-[#777777] mt-1.5 leading-relaxed">
                    Masukkan total omzet penjualan warung yang benar-benar sudah kamu terima.
                </p>
            </div>

            <!-- Form Pencatatan -->
            <form wire:submit="save" class="space-y-6">

                <!-- 1. Input Nominal Omzet -->
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
                            class="block w-full pl-14 pr-4 py-3.5 text-xl sm:text-2xl font-bold rounded-xl border {{ $errors->has('amount') ? 'border-[#E87559] text-[#E87559] focus:ring-[#E87559]/20 focus:border-[#E87559]' : 'border-[#EEEEEE] text-[#252525] focus:ring-emerald-500/20 focus:border-emerald-500' }} bg-[#FBFBFC] placeholder:text-[#BBBBBB] transition"
                        />
                    </div>
                    @error('amount')
                        <p class="mt-1.5 text-xs text-[#E87559] font-medium flex items-center gap-1.5">
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

                <!-- 2. Pilihan Kategori / Metode Penerimaan -->
                <div>
                    <label class="block text-xs sm:text-sm font-semibold text-[#252525] mb-2">
                        Metode Penerimaan Penjualan <span class="text-[#E87559]">*</span>
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <!-- Tunai -->
                        <label class="relative flex flex-col p-3.5 rounded-xl border cursor-pointer transition-all {{ $category === 'penjualan_tunai' ? 'border-emerald-500 bg-[#DDF1EC]/40 ring-1 ring-emerald-500' : 'border-[#EEEEEE] bg-white hover:bg-[#FBFBFC]' }}">
                            <input type="radio" wire:model.live="category" value="penjualan_tunai" class="sr-only">
                            <div class="w-8 h-8 rounded-lg {{ $category === 'penjualan_tunai' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-[#777777]' }} flex items-center justify-center mb-2 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                            <span class="text-xs font-bold text-[#252525]">Uang Tunai</span>
                            <span class="text-[11px] text-[#777777] mt-0.5">Cash di laci warung</span>
                        </label>

                        <!-- QRIS / Transfer -->
                        <label class="relative flex flex-col p-3.5 rounded-xl border cursor-pointer transition-all {{ $category === 'penjualan_qris' ? 'border-emerald-500 bg-[#DDF1EC]/40 ring-1 ring-emerald-500' : 'border-[#EEEEEE] bg-white hover:bg-[#FBFBFC]' }}">
                            <input type="radio" wire:model.live="category" value="penjualan_qris" class="sr-only">
                            <div class="w-8 h-8 rounded-lg {{ $category === 'penjualan_qris' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-[#777777]' }} flex items-center justify-center mb-2 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <span class="text-xs font-bold text-[#252525]">QRIS / Transfer</span>
                            <span class="text-[11px] text-[#777777] mt-0.5">Non-tunai bank/e-wallet</span>
                        </label>

                        <!-- Rekap Gabungan -->
                        <label class="relative flex flex-col p-3.5 rounded-xl border cursor-pointer transition-all {{ $category === 'penjualan' ? 'border-emerald-500 bg-[#DDF1EC]/40 ring-1 ring-emerald-500' : 'border-[#EEEEEE] bg-white hover:bg-[#FBFBFC]' }}">
                            <input type="radio" wire:model.live="category" value="penjualan" class="sr-only">
                            <div class="w-8 h-8 rounded-lg {{ $category === 'penjualan' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-[#777777]' }} flex items-center justify-center mb-2 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z"/>
                                </svg>
                            </div>
                            <span class="text-xs font-bold text-[#252525]">Rekap Umum</span>
                            <span class="text-[11px] text-[#777777] mt-0.5">Gabungan seluruh omzet</span>
                        </label>
                    </div>
                    @error('category')
                        <p class="mt-1.5 text-xs text-[#E87559] font-medium flex items-center gap-1.5">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <span>{{ $message }}</span>
                        </p>
                    @enderror
                </div>

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
                        class="block w-full py-2.5 px-3 text-xs sm:text-sm rounded-xl border {{ $errors->has('transaction_date') ? 'border-[#E87559] text-[#E87559] focus:ring-[#E87559]/20 focus:border-[#E87559]' : 'border-[#EEEEEE] text-[#252525] focus:ring-emerald-500/20 focus:border-emerald-500' }} bg-[#FBFBFC] transition"
                    />
                    <p class="mt-1.5 text-[11px] text-[#777777]">
                        Otomatis terisi tanggal hari ini. Tidak dapat memilih tanggal di masa depan.
                    </p>
                    @error('transaction_date')
                        <p class="mt-1.5 text-xs text-[#E87559] font-medium flex items-center gap-1.5">
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
                        class="block w-full py-2.5 px-3 text-xs sm:text-sm rounded-xl border border-[#EEEEEE] text-[#252525] placeholder:text-[#BBBBBB] focus:ring-emerald-500/20 focus:border-emerald-500 bg-[#FBFBFC] transition"
                    ></textarea>
                    @error('notes')
                        <p class="mt-1 text-xs text-[#E87559]">{{ $message }}</p>
                    @enderror
                </div>

                <!-- 5. Tombol Aksi Submit -->
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
                        class="inline-flex items-center gap-2 px-5 py-2.5 text-xs sm:text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-sm transition active:scale-95 disabled:opacity-50"
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
                            <span>Menyimpan...</span>
                        </span>
                    </button>
                </div>

            </form>

        </div>

    </div>
</div>
