<div class="w-full space-y-6">
    <!-- 1. Header & Quick Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-xl bg-[#FCEDEA] text-[#E87559] flex items-center justify-center shrink-0 shadow-xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z"/>
                        </svg>
                    </span>
                    <span>Riwayat Transaksi</span>
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Catatan seluruh arus kas masuk (penjualan) dan kas keluar (pengeluaran) usahamu.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('transactions.income') }}" wire:navigate class="inline-flex items-center px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-semibold rounded-xl shadow-xs transition active:scale-95">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>Catat Penjualan</span>
                </a>
                <a href="{{ route('transactions.expense') }}" wire:navigate class="inline-flex items-center px-3.5 py-2 bg-[#E87559] hover:bg-[#D66448] text-white text-xs sm:text-sm font-semibold rounded-xl shadow-xs transition active:scale-95">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4"></path>
                    </svg>
                    <span>Catat Pengeluaran</span>
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
                                                <span class="inline-flex items-center gap-1.5 text-gray-600 dark:text-gray-400 font-medium" title="Dari Foto Nota">
                                                    <svg class="w-3.5 h-3.5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    </svg>
                                                    <span>Foto</span>
                                                </span>
                                            @elseif ($t->source_type === 'voice')
                                                <span class="inline-flex items-center gap-1.5 text-gray-600 dark:text-gray-400 font-medium" title="Dari Suara">
                                                    <svg class="w-3.5 h-3.5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                                                    </svg>
                                                    <span>Suara</span>
                                                </span>
                                            @elseif ($t->source_type === 'digital_import')
                                                <span class="inline-flex items-center gap-1.5 text-gray-600 dark:text-gray-400 font-medium" title="Impor Digital">
                                                    <svg class="w-3.5 h-3.5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                                    </svg>
                                                    <span>Impor</span>
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 text-gray-600 dark:text-gray-400 font-medium" title="Ketik Teks">
                                                    <svg class="w-3.5 h-3.5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                    </svg>
                                                    <span>Teks</span>
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

                                        <!-- Tombol Aksi (Detail/Koreksi & Hapus) -->
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <div class="inline-flex items-center gap-1.5">
                                                <button
                                                    type="button"
                                                    wire:click="showDetail({{ $t->id }})"
                                                    class="text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 p-1 rounded transition"
                                                    title="Lihat Detail & Koreksi"
                                                >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                    </svg>
                                                </button>
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
                                            </div>
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

    <!-- 5. Modal Konfirmasi Hapus Transaksi (KV-018) -->
    @if ($confirmingDeletionId && $transactionToDelete)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-black/40 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-[#EEEEEE] space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-[#FCEDEA] text-[#E87559] border border-[#E87559]/20 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base sm:text-lg font-bold text-[#252525]">Hapus Transaksi?</h3>
                        <p class="text-xs text-[#777777]">Tindakan ini akan mempengaruhi perhitungan kas bersih.</p>
                    </div>
                </div>

                <div class="bg-[#FBFBFC] p-3.5 rounded-xl border border-[#EEEEEE] text-xs space-y-1.5">
                    <p class="text-[#777777]">Keterangan: <span class="font-semibold text-[#252525]">{{ $transactionToDelete->description }}</span></p>
                    <p class="text-[#777777]">Nominal: <span class="font-bold {{ $transactionToDelete->type === 'cash_in' ? 'text-emerald-600' : 'text-[#E87559]' }}">Rp {{ number_format($transactionToDelete->amount, 0, ',', '.') }}</span></p>
                    <p class="text-[#777777]">Tanggal: <span class="font-medium text-[#252525]">{{ \Carbon\Carbon::parse($transactionToDelete->transaction_date)->translatedFormat('d M Y') }}</span></p>
                </div>

                <p class="text-xs text-[#777777] leading-relaxed">
                    Apakah kamu yakin ingin menghapus transaksi ini dari pembukuan? Data yang sudah dihapus tidak dapat dipulihkan.
                </p>

                <div class="flex items-center justify-end gap-2.5 pt-2 border-t border-[#EEEEEE]">
                    <button
                        type="button"
                        wire:click="cancelDelete"
                        class="px-4 py-2 text-xs font-semibold text-[#777777] hover:text-[#252525] bg-gray-100 hover:bg-gray-200 rounded-xl transition"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        wire:click="deleteTransaction"
                        class="px-4 py-2 text-xs font-bold text-white bg-[#E87559] hover:bg-[#D66448] rounded-xl shadow-xs transition active:scale-95"
                    >
                        Ya, Hapus Transaksi
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- 6. Modal Detail & Koreksi Transaksi (KV-017) -->
    @if ($selectedTransactionId && $selectedTransaction)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-black/40 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl max-w-xl w-full p-6 shadow-xl border border-[#EEEEEE] space-y-5">

                <!-- Modal Header -->
                <div class="flex items-center justify-between pb-3.5 border-b border-[#EEEEEE]">
                    <div class="flex items-center gap-2">
                        @if ($selectedTransaction->type === 'cash_in')
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-[#DDF1EC] text-emerald-700 border border-emerald-200/50">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                                </svg>
                                <span>Kas Masuk</span>
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-[#FCEDEA] text-[#E87559] border border-[#E87559]/20">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                                </svg>
                                <span>Kas Keluar</span>
                            </span>
                        @endif
                        <h3 class="text-base sm:text-lg font-bold text-[#252525]">
                            {{ $isEditing ? 'Koreksi Data Transaksi' : 'Rincian Transaksi' }}
                        </h3>
                    </div>
                    <button
                        type="button"
                        wire:click="cancelDetail"
                        class="w-8 h-8 rounded-lg flex items-center justify-center text-[#777777] hover:text-[#252525] hover:bg-gray-100 transition-colors"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                @if (!$isEditing)
                    <!-- MODE PREVIEW RINCIAN TRANSAKSI -->
                    <div class="space-y-4">
                        <!-- Nominal & Tanggal -->
                        <div class="bg-[#FBFBFC] p-4 rounded-xl border border-[#EEEEEE] flex items-center justify-between">
                            <div>
                                <span class="text-[11px] text-[#777777] font-semibold uppercase tracking-wider">Total Nominal</span>
                                <div class="text-2xl font-extrabold {{ $selectedTransaction->type === 'cash_in' ? 'text-emerald-600' : 'text-[#E87559]' }}">
                                    {{ $selectedTransaction->type === 'cash_in' ? '+' : '-' }}Rp {{ number_format($selectedTransaction->amount, 0, ',', '.') }}
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="text-[11px] text-[#777777] font-semibold uppercase tracking-wider">Tanggal</span>
                                <div class="text-xs sm:text-sm font-bold text-[#252525]">
                                    {{ \Carbon\Carbon::parse($selectedTransaction->transaction_date)->translatedFormat('d F Y') }}
                                </div>
                            </div>
                        </div>

                        <!-- Info Kategori & Sumber Input -->
                        <div class="grid grid-cols-2 gap-3 text-xs">
                            <div class="p-3 rounded-xl border border-[#EEEEEE] bg-[#FBFBFC]">
                                <span class="text-[#777777] font-medium">Kategori:</span>
                                <p class="font-bold text-[#252525] mt-0.5">
                                    {{ ucfirst(str_replace('_', ' ', $selectedTransaction->category)) }}
                                </p>
                            </div>
                            <div class="p-3 rounded-xl border border-[#EEEEEE] bg-[#FBFBFC]">
                                <span class="text-[#777777] font-medium">Metode Input:</span>
                                <p class="font-bold text-[#252525] mt-0.5 flex items-center gap-1.5">
                                    @if ($selectedTransaction->source_type === 'photo')
                                        <svg class="w-3.5 h-3.5 text-[#777777]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        <span>Foto Nota / Struk</span>
                                    @elseif ($selectedTransaction->source_type === 'voice')
                                        <svg class="w-3.5 h-3.5 text-[#777777]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                                        </svg>
                                        <span>Pesan Suara</span>
                                    @elseif ($selectedTransaction->source_type === 'digital_import')
                                        <svg class="w-3.5 h-3.5 text-[#777777]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                        </svg>
                                        <span>Impor Digital</span>
                                    @else
                                        <svg class="w-3.5 h-3.5 text-[#777777]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                        </svg>
                                        <span>Ketik Teks</span>
                                    @endif
                                </p>
                            </div>
                        </div>

                        <!-- Catatan Transaksi -->
                        <div>
                            <span class="text-[11px] font-semibold text-[#777777] uppercase tracking-wider">Keterangan / Catatan:</span>
                            <p class="text-xs sm:text-sm text-[#252525] mt-1 bg-[#FBFBFC] p-3 rounded-xl border border-[#EEEEEE] leading-relaxed">
                                {{ $selectedTransaction->notes ?: 'Tidak ada catatan tambahan.' }}
                            </p>
                        </div>

                        <!-- Rincian Item Belanja (jika ada) -->
                        @if ($selectedTransaction->items->isNotEmpty())
                            <div>
                                <span class="text-[11px] font-semibold text-[#777777] uppercase tracking-wider">Rincian Barang Dapur:</span>
                                <div class="mt-1 border border-[#EEEEEE] rounded-xl overflow-hidden shadow-xs">
                                    <table class="min-w-full divide-y divide-[#EEEEEE] text-xs">
                                        <thead class="bg-[#FBFBFC] text-[#777777]">
                                            <tr>
                                                <th class="px-3 py-2 text-left font-semibold">Barang</th>
                                                <th class="px-3 py-2 text-center font-semibold">Jumlah</th>
                                                <th class="px-3 py-2 text-right font-semibold">Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-[#EEEEEE]">
                                            @foreach ($selectedTransaction->items as $item)
                                                <tr class="hover:bg-[#FBFBFC]/50 transition-colors">
                                                    <td class="px-3 py-2 font-medium text-[#252525]">{{ $item->item_name }}</td>
                                                    <td class="px-3 py-2 text-center text-[#777777]">{{ (float) $item->quantity }} {{ $item->unit }}</td>
                                                    <td class="px-3 py-2 text-right font-semibold text-[#252525]">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                        <!-- Bukti Foto Nota (jika ada) -->
                        @if ($selectedTransaction->attachments->isNotEmpty())
                            <div>
                                <span class="text-[11px] font-semibold text-[#777777] uppercase tracking-wider">Bukti Foto Struk / Nota:</span>
                                <div class="mt-2 flex gap-3">
                                    @foreach ($selectedTransaction->attachments as $att)
                                        <a href="{{ $att->url }}" target="_blank" class="group relative block w-20 h-20 sm:w-24 sm:h-24 rounded-xl overflow-hidden border border-[#EEEEEE] shadow-xs" title="Klik untuk perbesar">
                                            <img src="{{ $att->url }}" alt="{{ $att->file_name }}" class="w-full h-full object-cover group-hover:scale-105 transition" />
                                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition">
                                                <span class="text-white text-[10px] font-semibold flex items-center gap-1">
                                                    <span>Buka</span>
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                                    </svg>
                                                </span>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Jejak Audit Waktu (Audit Timestamp) -->
                        <div class="pt-3 border-t border-[#EEEEEE] text-[11px] text-[#777777] space-y-1">
                            <p class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-[#777777]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"/>
                                    <polyline points="12 6 12 12 16 14"/>
                                </svg>
                                <span>Dicatat pada: {{ $selectedTransaction->created_at->format('d M Y, H:i') }} WIB</span>
                            </p>
                            @if ($selectedTransaction->updated_at->gt($selectedTransaction->created_at))
                                <p class="flex items-center gap-1.5 text-amber-700 font-medium">
                                    <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    <span>Terakhir dikoreksi: {{ $selectedTransaction->updated_at->format('d M Y, H:i') }} WIB</span>
                                </p>
                            @endif
                        </div>

                        <!-- Footer Preview -->
                        <div class="pt-3.5 border-t border-[#EEEEEE] flex items-center justify-end gap-2.5">
                            <button
                                type="button"
                                wire:click="cancelDetail"
                                class="px-4 py-2 text-xs font-semibold text-[#777777] hover:text-[#252525] bg-gray-100 hover:bg-gray-200 rounded-xl transition"
                            >
                                Tutup
                            </button>
                            <button
                                type="button"
                                wire:click="startEditing"
                                class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-bold text-white bg-[#E87559] hover:bg-[#D66448] rounded-xl shadow-xs transition active:scale-95"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                </svg>
                                <span>Koreksi Transaksi Ini</span>
                            </button>
                        </div>
                    </div>

                @else
                    <!-- MODE FORM KOREKSI TRANSAKSI (KV-017) -->
                    <form wire:submit="updateTransaction" class="space-y-4">
                        <!-- Nominal Koreksi -->
                        <div>
                            <label class="block text-xs font-semibold text-[#252525] mb-1">
                                Nominal Transaksi (Rp) <span class="text-[#E87559]">*</span>
                            </label>
                            <input
                                type="number"
                                wire:model="edit_amount"
                                step="1000"
                                class="block w-full py-2 px-3 text-xs sm:text-sm rounded-xl border {{ $errors->has('edit_amount') ? 'border-[#E87559] text-[#E87559] focus:ring-[#E87559]/20' : 'border-[#EEEEEE] text-[#252525] focus:border-[#E87559] focus:ring-[#E87559]/20' }} bg-[#FBFBFC]"
                            />
                            @error('edit_amount')
                                <p class="text-[11px] text-[#E87559] mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Kategori Koreksi -->
                        <div>
                            <label class="block text-xs font-semibold text-[#252525] mb-1">
                                Kategori <span class="text-[#E87559]">*</span>
                            </label>
                            <select
                                wire:model="edit_category"
                                class="block w-full py-2 px-3 text-xs sm:text-sm rounded-xl border border-[#EEEEEE] bg-[#FBFBFC] text-[#252525] focus:border-[#E87559] focus:ring-[#E87559]/20"
                            >
                                @if ($selectedTransaction->type === 'cash_in')
                                    <option value="penjualan_tunai">Penjualan Tunai</option>
                                    <option value="penjualan_qris">Penjualan QRIS / Transfer</option>
                                    <option value="penjualan">Rekap Penjualan Umum</option>
                                @else
                                    <option value="bahan_baku">Bahan Baku & Dapur</option>
                                    <option value="operasional">Biaya Operasional</option>
                                    <option value="gaji">Gaji Karyawan</option>
                                    <option value="sewa">Sewa Tempat</option>
                                @endif
                                <option value="lainnya">Lainnya</option>
                            </select>
                            @error('edit_category')
                                <p class="text-[11px] text-[#E87559] mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Tanggal Koreksi -->
                        <div>
                            <label class="block text-xs font-semibold text-[#252525] mb-1">
                                Tanggal Transaksi <span class="text-[#E87559]">*</span>
                            </label>
                            <input
                                type="date"
                                wire:model="edit_transaction_date"
                                max="{{ date('Y-m-d') }}"
                                class="block w-full py-2 px-3 text-xs sm:text-sm rounded-xl border {{ $errors->has('edit_transaction_date') ? 'border-[#E87559] text-[#E87559] focus:ring-[#E87559]/20' : 'border-[#EEEEEE] text-[#252525] focus:border-[#E87559] focus:ring-[#E87559]/20' }} bg-[#FBFBFC]"
                            />
                            @error('edit_transaction_date')
                                <p class="text-[11px] text-[#E87559] mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Catatan Koreksi -->
                        <div>
                            <label class="block text-xs font-semibold text-[#252525] mb-1">
                                Catatan / Keterangan
                            </label>
                            <textarea
                                wire:model="edit_notes"
                                rows="2"
                                class="block w-full py-2 px-3 text-xs sm:text-sm rounded-xl border border-[#EEEEEE] bg-[#FBFBFC] text-[#252525] focus:border-[#E87559] focus:ring-[#E87559]/20"
                            ></textarea>
                            @error('edit_notes')
                                <p class="text-[11px] text-[#E87559] mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Tombol Submit Koreksi -->
                        <div class="pt-3.5 border-t border-[#EEEEEE] flex items-center justify-end gap-2.5">
                            <button
                                type="button"
                                wire:click="$set('isEditing', false)"
                                class="px-4 py-2 text-xs font-semibold text-[#777777] hover:text-[#252525] bg-gray-100 hover:bg-gray-200 rounded-xl transition"
                            >
                                Batal
                            </button>
                            <button
                                type="submit"
                                class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-xs transition active:scale-95"
                            >
                                <span>Simpan Perubahan</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                </svg>
                            </button>
                        </div>
                    </form>
                @endif

            </div>
        </div>
    @endif
</div>

