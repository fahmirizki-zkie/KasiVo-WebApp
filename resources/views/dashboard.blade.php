<x-app-layout>
    <div class="space-y-6">

        <!-- Welcome Banner Card -->
        <div class="p-6 sm:p-8 rounded-3xl bg-gradient-to-r from-white via-[#FCEDEA]/30 to-[#E1F0F7]/20 border border-[#EEEEEE] shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#FCEDEA] text-[#E87559] text-xs font-bold mb-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#E87559] animate-pulse"></span>
                    <span>Sistem POS & Kasir Online</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#252525] tracking-tight">
                    Selamat datang kembali, {{ auth()->user()->name }} 👋
                </h1>
                <p class="text-sm text-[#777777] mt-1 max-w-xl">
                    Pantau kinerja penjualan, stok bahan baku, dan riwayat pesanan usaha Anda hari ini dalam satu platform terpadu.
                </p>
            </div>

            <!-- Quick Action Shortcut Buttons -->
            <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                <a href="{{ route('transactions.expense') }}" 
                   wire:navigate
                   class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-[#E87559] hover:bg-[#D66448] text-white text-xs font-semibold shadow-sm shadow-[#E87559]/25 hover:shadow-md transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    <span>Catat Pengeluaran</span>
                </a>
                <a href="{{ route('transactions.income') }}" 
                   wire:navigate
                   class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-white hover:bg-gray-50 text-[#252525] text-xs font-semibold border border-[#EEEEEE] shadow-sm transition-all">
                    <span>Catat Penjualan</span>
                </a>
            </div>
        </div>

        <!-- 4 Top KPI Metrics Row -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            
            <!-- Card 1: Penjualan Hari Ini -->
            <div class="p-5 rounded-2xl bg-white border border-[#EEEEEE] shadow-sm hover:border-[#E87559]/30 transition">
                <div class="flex items-center justify-between text-xs text-[#777777]">
                    <span class="font-medium">Total Penjualan Hari Ini</span>
                    <span class="px-2 py-0.5 rounded-full bg-[#DDF1EC] text-[#43A58A] font-bold text-[10px]">+18.4%</span>
                </div>
                <div class="mt-2 text-2xl font-extrabold text-[#252525]">
                    Rp 14.850.000
                </div>
                <div class="mt-1 text-xs text-[#777777] flex items-center gap-1">
                    <span class="text-[#43A58A] font-semibold">↑ Rp 2.3jt</span> vs hari kemarin
                </div>
            </div>

            <!-- Card 2: Total Tiket Transaksi -->
            <div class="p-5 rounded-2xl bg-white border border-[#EEEEEE] shadow-sm hover:border-[#E87559]/30 transition">
                <div class="flex items-center justify-between text-xs text-[#777777]">
                    <span class="font-medium">Total Transaksi</span>
                    <span class="px-2 py-0.5 rounded-full bg-[#E1F0F7] text-[#252525] font-bold text-[10px]">184 Order</span>
                </div>
                <div class="mt-2 text-2xl font-extrabold text-[#252525]">
                    184 Tiket
                </div>
                <div class="mt-1 text-xs text-[#777777]">
                    Rata-rata: <span class="font-semibold text-[#252525]">Rp 80.700</span> / order
                </div>
            </div>

            <!-- Card 3: Status Stok & Restock -->
            <div class="p-5 rounded-2xl bg-white border border-[#EEEEEE] shadow-sm hover:border-[#E87559]/30 transition">
                <div class="flex items-center justify-between text-xs text-[#777777]">
                    <span class="font-medium">Status Persediaan</span>
                    <a href="{{ route('restock') }}" wire:navigate class="px-2 py-0.5 rounded-full bg-[#FCEDEA] text-[#E87559] font-bold text-[10px] hover:underline">
                        2 Perlu Restock →
                    </a>
                </div>
                <div class="mt-2 text-2xl font-extrabold text-[#252525]">
                    94% Aman
                </div>
                <div class="mt-1 text-xs text-[#E87559] font-medium truncate">
                    Arabika Beans sisa 1.2kg
                </div>
            </div>

            <!-- Card 4: Health Score AI -->
            <div class="p-5 rounded-2xl bg-white border border-[#EEEEEE] shadow-sm hover:border-[#E87559]/30 transition">
                <div class="flex items-center justify-between text-xs text-[#777777]">
                    <span class="font-medium">Skor Kesehatan Bisnis</span>
                    <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-[#43A58A] font-bold text-[10px]">AI VERIFIED</span>
                </div>
                <div class="mt-2 text-2xl font-extrabold text-[#252525]">
                    96 / 100
                </div>
                <div class="mt-1 text-xs text-[#43A58A] font-medium">
                    Cash flow stabil & margin sehat
                </div>
            </div>

        </div>

        <!-- Middle Quick Navigation Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            
            <!-- Quick Feature 1: Transaksi -->
            <div class="p-6 rounded-3xl bg-white border border-[#EEEEEE] shadow-sm flex flex-col justify-between">
                <div>
                    <div class="w-10 h-10 rounded-xl bg-[#FCEDEA] text-[#E87559] flex items-center justify-center font-bold mb-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-[#252525]">Kelola Transaksi</h3>
                    <p class="text-xs text-[#777777] mt-1.5 leading-relaxed">
                        Catat pemasukan dan pengeluaran harian via scan struk OCR, perintah suara, atau kasir POS.
                    </p>
                </div>
                <div class="mt-6 pt-4 border-t border-gray-100 flex items-center justify-between">
                    <a href="{{ route('transactions.index') }}" wire:navigate class="text-xs font-bold text-[#E87559] hover:underline flex items-center gap-1">
                        <span>Buka Riwayat</span>
                        <span>→</span>
                    </a>
                    <a href="{{ route('transactions.expense') }}" wire:navigate class="text-xs font-semibold text-[#777777] hover:text-[#252525]">
                        + Pengeluaran
                    </a>
                </div>
            </div>

            <!-- Quick Feature 2: Restock -->
            <div class="p-6 rounded-3xl bg-white border border-[#EEEEEE] shadow-sm flex flex-col justify-between">
                <div>
                    <div class="w-10 h-10 rounded-xl bg-[#DDF1EC] text-[#43A58A] flex items-center justify-center font-bold mb-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-[#252525]">Saran Restock Bahan</h3>
                    <p class="text-xs text-[#777777] mt-1.5 leading-relaxed">
                        Prediksi kebutuhan bahan baku F&B berdasarkan histori penjualan mingguan untuk mencegah kehabisan stok.
                    </p>
                </div>
                <div class="mt-6 pt-4 border-t border-gray-100 flex items-center justify-between">
                    <a href="{{ route('restock') }}" wire:navigate class="text-xs font-bold text-[#E87559] hover:underline flex items-center gap-1">
                        <span>Lihat Saran Bahan</span>
                        <span>→</span>
                    </a>
                    <span class="text-[11px] font-semibold text-[#E87559] bg-[#FCEDEA] px-2 py-0.5 rounded-full">
                        2 Menipis
                    </span>
                </div>
            </div>

            <!-- Quick Feature 3: Profil Usaha -->
            <div class="p-6 rounded-3xl bg-white border border-[#EEEEEE] shadow-sm flex flex-col justify-between">
                <div>
                    <div class="w-10 h-10 rounded-xl bg-[#E1F0F7] text-[#252525] flex items-center justify-center font-bold mb-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-[#252525]">Profil Usaha & Ekspor</h3>
                    <p class="text-xs text-[#777777] mt-1.5 leading-relaxed">
                        Data identitas bisnis, performa omzet, dan unduh laporan keuangan terformat PDF untuk pengajuan pendanaan.
                    </p>
                </div>
                <div class="mt-6 pt-4 border-t border-gray-100 flex items-center justify-between">
                    <a href="{{ route('business-profile') }}" wire:navigate class="text-xs font-bold text-[#E87559] hover:underline flex items-center gap-1">
                        <span>Kelola Profil</span>
                        <span>→</span>
                    </a>
                    <span class="text-[11px] font-semibold text-[#43A58A] bg-[#DDF1EC] px-2 py-0.5 rounded-full">
                        Siap Ekspor
                    </span>
                </div>
            </div>

        </div>

    </div>
</x-app-layout>
