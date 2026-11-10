<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        <!-- 1. Header & Quick Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <span>📋</span> Riwayat Transaksi
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Catatan seluruh arus kas masuk (penjualan) dan kas keluar (pengeluaran) usahamu.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('transactions.income') }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg shadow-sm transition">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    + Catat Penjualan
                </a>
                <a href="{{ route('transactions.expense') }}" class="inline-flex items-center px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-sm font-medium rounded-lg shadow-sm transition">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                    </svg>
                    - Catat Pengeluaran
                </a>
            </div>
        </div>

        <!-- Flash Message -->
        @if (session()->has('message'))
            <div class="p-4 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 text-sm flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span>{{ session('message') }}</span>
                </div>
            </div>
        @endif

        @if (!$hasBusiness)
            <!-- State jika belum punya profil usaha -->
            <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-6 text-center">
                <p class="text-amber-800 dark:text-amber-200 font-medium">Kamu belum mendaftarkan profil usaha.</p>
                <p class="text-sm text-amber-600 dark:text-amber-400 mt-1">Lengkapi nama dan jenis usahamu terlebih dahulu untuk mulai mencatat transaksi.</p>
                <a href="{{ route('onboarding') }}" class="mt-4 inline-block px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-sm font-medium transition">
                    Mulai Onboarding Usaha
                </a>
            </div>
        @else

            <!-- 2. Mini Summary Cards (Realtime Reactive) -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <!-- Total Kas Masuk -->
                <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-xl p-4 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">Kas Masuk (Penjualan)</span>
                        <span class="p-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                            </svg>
                        </span>
                    </div>
                    <p class="text-xl font-bold text-gray-900 dark:text-white mt-2">
                        +Rp {{ number_format($totalCashIn, 0, ',', '.') }}
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5">Sesuai filter aktif</p>
                </div>

                <!-- Total Kas Keluar -->
                <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-xl p-4 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-rose-600 dark:text-rose-400 uppercase tracking-wider">Kas Keluar (Beban)</span>
                        <span class="p-1.5 rounded-lg bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"></path>
                            </svg>
                        </span>
                    </div>
                    <p class="text-xl font-bold text-gray-900 dark:text-white mt-2">
                        -Rp {{ number_format($totalCashOut, 0, ',', '.') }}
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5">Sesuai filter aktif</p>
                </div>

                <!-- Arus Kas Bersih -->
                <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-xl p-4 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold {{ $netCashFlow >= 0 ? 'text-indigo-600 dark:text-indigo-400' : 'text-rose-600' }} uppercase tracking-wider">
                            Arus Kas Bersih
                        </span>
                        <span class="p-1.5 rounded-lg {{ $netCashFlow >= 0 ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600' : 'bg-rose-50 text-rose-600' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </span>
                    </div>
                    <p class="text-xl font-bold {{ $netCashFlow >= 0 ? 'text-indigo-600 dark:text-indigo-400' : 'text-rose-600' }} mt-2">
                        {{ $netCashFlow >= 0 ? '+' : '' }}Rp {{ number_format($netCashFlow, 0, ',', '.') }}
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5">Selisih kas masuk & keluar</p>
                </div>
            </div>

            <!-- 3. Toolbar Filter & Pencarian -->
            <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-xl p-4 shadow-sm space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                    <!-- Search Input -->
                    <div class="md:col-span-2 relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="search"
                            placeholder="Cari transaksi atau nama bahan..."
                            class="block w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-900 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-indigo-500 focus:border-indigo-500"
                        />
                    </div>

                    <!-- Filter Rentang Tanggal -->
                    <div>
                        <select
                            wire:model.live="dateRange"
                            class="block w-full py-2 px-3 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500"
                        >
                            <option value="all">Semua Waktu</option>
                            <option value="today">Hari Ini</option>
                            <option value="last_7_days">7 Hari Terakhir</option>
                            <option value="this_month">Bulan Ini</option>
                        </select>
                    </div>

                    <!-- Filter Kategori -->
                    <div>
                        <select
                            wire:model.live="category"
                            class="block w-full py-2 px-3 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500"
                        >
                            <option value="all">Semua Kategori</option>
                            <optgroup label="Pengeluaran">
                                <option value="bahan_baku">Bahan Baku & Dapur</option>
                                <option value="operasional">Biaya Operasional</option>
                                <option value="gaji">Gaji / Upah Karyawan</option>
                                <option value="sewa">Sewa Tempat</option>
                            </optgroup>
                            <optgroup label="Penjualan">
                                <option value="penjualan_tunai">Penjualan Tunai</option>
                                <option value="penjualan_qris">Penjualan QRIS / Transfer</option>
                            </optgroup>
                            <option value="lainnya">Lainnya</option>
                        </select>
                    </div>
                </div>

                <!-- Tipe Kas Tabs/Pills -->
                <div class="flex items-center gap-2 pt-2 border-t border-gray-100 dark:border-gray-700">
                    <span class="text-xs font-medium text-gray-500 mr-2">Tipe Kas:</span>
                    <button
                        type="button"
                        wire:click="$set('type', 'all')"
                        class="px-3 py-1 text-xs font-medium rounded-full transition {{ $type === 'all' ? 'bg-indigo-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200' }}"
                    >
                        Semua
                    </button>
                    <button
                        type="button"
                        wire:click="$set('type', 'cash_in')"
                        class="px-3 py-1 text-xs font-medium rounded-full transition {{ $type === 'cash_in' ? 'bg-emerald-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200' }}"
                    >
                        + Kas Masuk (Penjualan)
                    </button>
                    <button
                        type="button"
                        wire:click="$set('type', 'cash_out')"
                        class="px-3 py-1 text-xs font-medium rounded-full transition {{ $type === 'cash_out' ? 'bg-rose-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200' }}"
                    >
                        - Kas Keluar (Pengeluaran)
                    </button>
                </div>
            </div>

            <!-- 4. Daftar Transaksi (Tabel / Card) -->
            <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden">
                @if ($transactions->isEmpty())
                    <!-- Empty State -->
                    <div class="p-12 text-center">
                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-400 mb-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                        </div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">Tidak ada transaksi ditemukan</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Coba ubah kata kunci pencarian atau sesuaikan rentang waktu filter.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-left text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-900/50 text-gray-500 dark:text-gray-400 text-xs uppercase font-semibold">
                                <tr>
                                    <th scope="col" class="px-6 py-3.5">Tanggal</th>
                                    <th scope="col" class="px-6 py-3.5">Deskripsi & Rincian</th>
                                    <th scope="col" class="px-6 py-3.5">Kategori</th>
                                    <th scope="col" class="px-6 py-3.5">Metode</th>
                                    <th scope="col" class="px-6 py-3.5 text-right">Nominal</th>
                                    <th scope="col" class="px-6 py-3.5 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700 text-gray-700 dark:text-gray-300">
                                @foreach ($transactions as $t)
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/50 transition">
                                        <!-- Tanggal -->
                                        <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400">
                                            <div class="font-medium text-gray-900 dark:text-white">
                                                {{ \Carbon\Carbon::parse($t->transaction_date)->translatedFormat('d M Y') }}
                                            </div>
                                            <div>{{ $t->created_at->format('H:i') }} WIB</div>
                                        </td>

                                        <!-- Deskripsi & Item -->
                                        <td class="px-6 py-4">
                                            <div class="font-medium text-gray-900 dark:text-white">
                                                {{ $t->description }}
                                            </div>
                                            @if ($t->items->isNotEmpty())
                                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 flex flex-wrap gap-1">
                                                    @foreach ($t->items as $item)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 text-[11px]">
                                                            {{ $item->item_name }} ({{ (float) $item->quantity }} {{ $item->unit }})
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </td>

                                        <!-- Kategori & Tipe Badge -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if ($t->type === 'cash_in')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                                                    {{ ucfirst(str_replace('_', ' ', $t->category)) }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300">
                                                    {{ ucfirst(str_replace('_', ' ', $t->category)) }}
                                                </span>
                                            @endif
                                        </td>

                                        <!-- Sumber Input (Multimodal) -->
                                        <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500">
                                            @if ($t->source_type === 'photo')
                                                <span class="inline-flex items-center gap-1 text-gray-600 dark:text-gray-400" title="Dari Foto Nota">
                                                    📸 Foto
                                                </span>
                                            @elseif ($t->source_type === 'voice')
                                                <span class="inline-flex items-center gap-1 text-gray-600 dark:text-gray-400" title="Dari Suara">
                                                    🎙️ Suara
                                                </span>
                                            @elseif ($t->source_type === 'digital_import')
                                                <span class="inline-flex items-center gap-1 text-gray-600 dark:text-gray-400" title="Impor Digital">
                                                    📥 Impor
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 text-gray-600 dark:text-gray-400" title="Ketik Teks">
                                                    ✍️ Teks
                                                </span>
                                            @endif
                                        </td>

                                        <!-- Nominal -->
                                        <td class="px-6 py-4 whitespace-nowrap text-right font-semibold">
                                            @if ($t->type === 'cash_in')
                                                <span class="text-emerald-600 dark:text-emerald-400">
                                                    +Rp {{ number_format($t->amount, 0, ',', '.') }}
                                                </span>
                                            @else
                                                <span class="text-rose-600 dark:text-rose-400">
                                                    -Rp {{ number_format($t->amount, 0, ',', '.') }}
                                                </span>
                                            @endif
                                        </td>

                                        <!-- Tombol Hapus (KV-018) -->
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <button
                                                type="button"
                                                wire:click="confirmDelete({{ $t->id }})"
                                                class="text-gray-400 hover:text-rose-600 dark:hover:text-rose-400 p-1 rounded transition"
                                                title="Hapus transaksi"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Links -->
                    <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">
                        {{ $transactions->links() }}
                    </div>
                @endif
            </div>

        @endif

    </div>

    <!-- 5. Modal Konfirmasi Hapus Transaksi (KV-018) -->
    @if ($confirmingDeletionId && $transactionToDelete)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-800 rounded-2xl max-w-md w-full p-6 shadow-xl border border-gray-100 dark:border-gray-700 space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-rose-100 dark:bg-rose-900/40 text-rose-600 flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Hapus Transaksi?</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Tindakan ini akan mempengaruhi perhitungan kas bersih.</p>
                    </div>
                </div>

                <div class="bg-gray-50 dark:bg-gray-900/50 p-3 rounded-lg text-xs space-y-1">
                    <p class="text-gray-500">Keterangan: <span class="font-semibold text-gray-900 dark:text-white">{{ $transactionToDelete->description }}</span></p>
                    <p class="text-gray-500">Nominal: <span class="font-semibold {{ $transactionToDelete->type === 'cash_in' ? 'text-emerald-600' : 'text-rose-600' }}">Rp {{ number_format($transactionToDelete->amount, 0, ',', '.') }}</span></p>
                    <p class="text-gray-500">Tanggal: <span class="font-medium text-gray-700 dark:text-gray-300">{{ \Carbon\Carbon::parse($transactionToDelete->transaction_date)->translatedFormat('d M Y') }}</span></p>
                </div>

                <p class="text-xs text-gray-500">
                    Apakah kamu yakin ingin menghapus transaksi ini dari pembukuan? Data yang sudah dihapus tidak dapat dipulihkan.
                </p>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button
                        type="button"
                        wire:click="cancelDelete"
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        wire:click="deleteTransaction"
                        class="px-4 py-2 text-sm font-medium text-white bg-rose-600 hover:bg-rose-700 rounded-lg shadow-sm transition"
                    >
                        Ya, Hapus Transaksi
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

