<div class="py-8">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Navigasi Balik -->
        <div class="mb-6">
            <a href="{{ route('transactions.create') }}" class="inline-flex items-center text-xs font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali ke Pilihan Jalur
            </a>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 sm:p-8">

            <!-- Header Form -->
            <div class="mb-6 flex items-start justify-between">
                <div>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300 mb-2">
                        + Kas Masuk (Penjualan)
                    </span>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        Catat Penjualan Harian 💰
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Masukkan total omzet penjualan warung yang benar-benar sudah kamu terima.
                    </p>
                </div>
            </div>

            <!-- Form Pencatatan -->
            <form wire:submit="save" class="space-y-6">

                <!-- 1. Input Nominal Omzet -->
                <div>
                    <label for="amount" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                        Total Nominal Penjualan <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <span class="text-gray-500 dark:text-gray-400 font-bold text-lg">Rp</span>
                        </div>
                        <input
                            type="number"
                            id="amount"
                            wire:model="amount"
                            placeholder="0"
                            step="1000"
                            class="block w-full pl-14 pr-4 py-3.5 text-xl font-bold rounded-xl border {{ $errors->has('amount') ? 'border-rose-500 text-rose-900 focus:ring-rose-500 focus:border-rose-500' : 'border-gray-300 dark:border-gray-600 focus:ring-emerald-500 focus:border-emerald-500' }} bg-white dark:bg-gray-900 text-gray-900 dark:text-white transition"
                        />
                    </div>
                    @error('amount')
                        <p class="mt-1.5 text-xs text-rose-600 dark:text-rose-400 font-medium flex items-center gap-1">
                            <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            {{ $message }}
                        </p>
                    @enderror

                    <!-- Quick Add Pills -->
                    <div class="mt-2.5 flex flex-wrap items-center gap-1.5">
                        <span class="text-xs text-gray-400 mr-1">Tambah Cepat:</span>
                        <button type="button" wire:click="addAmount(50000)" class="px-2.5 py-1 text-xs font-medium rounded-lg bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 transition">
                            +50rb
                        </button>
                        <button type="button" wire:click="addAmount(100000)" class="px-2.5 py-1 text-xs font-medium rounded-lg bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 transition">
                            +100rb
                        </button>
                        <button type="button" wire:click="addAmount(500000)" class="px-2.5 py-1 text-xs font-medium rounded-lg bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 transition">
                            +500rb
                        </button>
                        <button type="button" wire:click="addAmount(1000000)" class="px-2.5 py-1 text-xs font-medium rounded-lg bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 transition">
                            +1jt
                        </button>
                        <button type="button" wire:click="setAmount(0)" class="px-2 py-1 text-xs font-medium rounded-lg text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/30 transition">
                            Reset
                        </button>
                    </div>
                </div>

                <!-- 2. Pilihan Kategori / Metode Penerimaan -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        Metode Penerimaan Penjualan <span class="text-rose-500">*</span>
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <!-- Tunai -->
                        <label class="relative flex flex-col p-3 rounded-xl border cursor-pointer transition {{ $category === 'penjualan_tunai' ? 'border-emerald-500 bg-emerald-50/40 dark:bg-emerald-950/20 ring-2 ring-emerald-500' : 'border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50' }}">
                            <input type="radio" wire:model.live="category" value="penjualan_tunai" class="sr-only">
                            <span class="text-base mb-1">💵</span>
                            <span class="text-xs font-bold text-gray-900 dark:text-white">Uang Tunai</span>
                            <span class="text-[11px] text-gray-500 dark:text-gray-400">Cash di laci warung</span>
                        </label>

                        <!-- QRIS / Transfer -->
                        <label class="relative flex flex-col p-3 rounded-xl border cursor-pointer transition {{ $category === 'penjualan_qris' ? 'border-emerald-500 bg-emerald-50/40 dark:bg-emerald-950/20 ring-2 ring-emerald-500' : 'border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50' }}">
                            <input type="radio" wire:model.live="category" value="penjualan_qris" class="sr-only">
                            <span class="text-base mb-1">📱</span>
                            <span class="text-xs font-bold text-gray-900 dark:text-white">QRIS / Transfer</span>
                            <span class="text-[11px] text-gray-500 dark:text-gray-400">Non-tunai bank/e-wallet</span>
                        </label>

                        <!-- Rekap Gabungan -->
                        <label class="relative flex flex-col p-3 rounded-xl border cursor-pointer transition {{ $category === 'penjualan' ? 'border-emerald-500 bg-emerald-50/40 dark:bg-emerald-950/20 ring-2 ring-emerald-500' : 'border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50' }}">
                            <input type="radio" wire:model.live="category" value="penjualan" class="sr-only">
                            <span class="text-base mb-1">🧾</span>
                            <span class="text-xs font-bold text-gray-900 dark:text-white">Rekap Umum</span>
                            <span class="text-[11px] text-gray-500 dark:text-gray-400">Gabungan seluruh omzet</span>
                        </label>
                    </div>
                    @error('category')
                        <p class="mt-1.5 text-xs text-rose-600 dark:text-rose-400 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- 3. Tanggal Transaksi -->
                <div>
                    <label for="transaction_date" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                        Tanggal Penjualan <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="date"
                        id="transaction_date"
                        wire:model="transaction_date"
                        max="{{ date('Y-m-d') }}"
                        class="block w-full py-2.5 px-3 text-sm rounded-xl border {{ $errors->has('transaction_date') ? 'border-rose-500 text-rose-900 focus:ring-rose-500 focus:border-rose-500' : 'border-gray-300 dark:border-gray-600 focus:ring-emerald-500 focus:border-emerald-500' }} bg-white dark:bg-gray-900 text-gray-900 dark:text-white transition"
                    />
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Otomatis terisi tanggal hari ini. Tidak dapat memilih tanggal di masa depan.
                    </p>
                    @error('transaction_date')
                        <p class="mt-1.5 text-xs text-rose-600 dark:text-rose-400 font-medium flex items-center gap-1">
                            <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- 4. Catatan Tambahan (Opsional) -->
                <div>
                    <label for="notes" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                        Catatan Ringkas <span class="text-xs text-gray-400 font-normal">(Opsional)</span>
                    </label>
                    <textarea
                        id="notes"
                        wire:model="notes"
                        rows="2"
                        placeholder="Contoh: Omzet ramai shift makan siang, pesanan nasi kotak 20 porsi..."
                        class="block w-full py-2.5 px-3 text-sm rounded-xl border border-gray-300 dark:border-gray-600 focus:ring-emerald-500 focus:border-emerald-500 bg-white dark:bg-gray-900 text-gray-900 dark:text-white transition"
                    ></textarea>
                    @error('notes')
                        <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- 5. Tombol Aksi Submit -->
                <div class="pt-4 border-t border-gray-100 dark:border-gray-700 flex items-center justify-end gap-3">
                    <a
                        href="{{ route('transactions.create') }}"
                        class="px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-xl transition"
                    >
                        Batal
                    </a>
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center px-5 py-2.5 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-md transition disabled:opacity-50"
                    >
                        <span wire:loading.remove>
                            Simpan Penjualan ➔
                        </span>
                        <span wire:loading class="inline-flex items-center gap-1.5">
                            <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            Menyimpan...
                        </span>
                    </button>
                </div>

            </form>

        </div>

    </div>
</div>

