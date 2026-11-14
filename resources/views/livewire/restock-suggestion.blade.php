<div class="space-y-6">

    <!-- Header & Status Bar Card -->
    <div class="p-6 sm:p-8 rounded-3xl bg-gradient-to-r from-white via-[#FCEDEA]/30 to-[#E1F0F7]/20 border border-[#EEEEEE] shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#FCEDEA] text-[#E87559] text-xs font-bold mb-2">
                <span class="w-1.5 h-1.5 rounded-full bg-[#E87559] animate-pulse"></span>
                <span>{{ $business ? $business->name : 'Sistem Restock KasiVo' }}</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#252525] tracking-tight">
                Saran Restock Bahan Baku
            </h1>
            <p class="text-sm text-[#777777] mt-1 max-w-2xl">
                Rekomendasi belanja berkala berbasis kebiasaan riil usaha Anda untuk mencegah kehabisan stok bahan tanpa menebak-nebak.
            </p>
        </div>

        <!-- Refresh & Timestamp Bar -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 shrink-0">
            <div class="text-left sm:text-right">
                <span class="block text-[11px] font-bold uppercase tracking-wider text-[#777777]">Terakhir Diperbarui</span>
                <span class="text-xs font-bold text-[#252525]">{{ $sufficiency['formatted_last_updated'] ?? '-' }}</span>
            </div>
            <button type="button"
                    wire:click="refreshData"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-white hover:bg-gray-50 text-[#252525] text-xs font-semibold border border-[#EEEEEE] shadow-sm transition-all">
                <svg wire:loading.remove class="w-4 h-4 text-[#777777]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <svg wire:loading class="w-4 h-4 text-[#E87559] animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                <span wire:loading.remove>Perbarui Analisis</span>
                <span wire:loading>Menganalisis...</span>
            </button>
        </div>
    </div>

    <!-- Banner Transparansi Resmi KasiVo (DoD SPS: Tidak Mengklaim Stok Fisik Gudang) -->
    <div class="p-4 sm:p-5 rounded-2xl bg-[#E1F0F7]/40 border border-[#BCE1F1] flex items-start gap-3.5">
        <div class="w-8 h-8 rounded-xl bg-[#0284C7]/10 text-[#0284C7] flex items-center justify-center shrink-0 mt-0.5">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <div class="flex-1 text-xs sm:text-sm text-[#0369A1] leading-relaxed">
            <span class="font-bold">Klausul Transparansi Sistem:</span>
            <span class="ml-1">{{ $disclaimer }}</span>
        </div>
    </div>

    {{-- KONDISI 1: DATA BELUM CUKUP (< 7 HARI) (DoD KV-046 & KV-049 Empty State) --}}
    @if (!$sufficiency['is_sufficient'])
        <div class="p-6 sm:p-10 rounded-3xl bg-white border border-amber-200 shadow-sm text-center">
            <div class="w-16 h-16 mx-auto rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>

            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-100 text-amber-800 text-xs font-bold mb-3">
                <span>{{ $sufficiency['status_label'] }}</span>
                <span>•</span>
                <span>Sisa {{ $sufficiency['remaining_days'] }} Hari Lagi</span>
            </div>

            <h2 class="text-xl sm:text-2xl font-extrabold text-[#252525] mb-2">
                Pencatatan Belanja Bahan Masih Membangun Pola
            </h2>

            <p class="text-sm text-[#777777] max-w-xl mx-auto leading-relaxed mb-6">
                {{ $sufficiency['guidance_message'] }}
            </p>

            <!-- Progress Indicator 7 Hari -->
            <div class="max-w-md mx-auto mb-8 bg-gray-50 p-4 rounded-2xl border border-gray-100">
                <div class="flex items-center justify-between text-xs font-bold text-[#252525] mb-2">
                    <span>Progres Pengumpulan Pola</span>
                    <span>{{ $sufficiency['current_span_days'] }} / {{ $sufficiency['threshold_days'] }} Hari</span>
                </div>
                <div class="w-full bg-gray-200 h-2.5 rounded-full overflow-hidden">
                    <div class="bg-amber-500 h-2.5 rounded-full transition-all duration-500"
                         style="width: {{ min(100, max(5, ($sufficiency['current_span_days'] / max(1, $sufficiency['threshold_days'])) * 100)) }}%;">
                    </div>
                </div>
                <span class="block text-[11px] text-[#777777] mt-2 text-left">
                    Ambang batas minimum KasiVo adalah 7 hari kalender riwayat belanja bahan agar prediksi siklus belanja akurat.
                </span>
            </div>

            <!-- Action Buttons for Empty State -->
            <div class="flex flex-wrap items-center justify-center gap-3">
                <a href="{{ route('transactions.expense') }}"
                   wire:navigate
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#E87559] hover:bg-[#D66448] text-white text-xs sm:text-sm font-semibold shadow-sm transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Catat Pembelian Bahan</span>
                </a>
                <a href="{{ route('transactions.photo') }}"
                   wire:navigate
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-white hover:bg-gray-50 text-[#252525] text-xs sm:text-sm font-semibold border border-[#EEEEEE] shadow-sm transition-all">
                    <svg class="w-4 h-4 text-[#777777]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                    </svg>
                    <span>Foto Nota Belanja</span>
                </a>
            </div>
        </div>

    {{-- KONDISI 2: DATA CUKUP (>= 7 HARI) (DoD KV-047, KV-048, KV-049) --}}
    @else

        <!-- Banner Ringkasan Narasi AI (DoD KV-048) -->
        <div class="p-6 sm:p-7 rounded-3xl bg-gradient-to-br from-white via-[#FCEDEA]/20 to-white border border-[#E87559]/20 shadow-sm space-y-3">
            <div class="flex items-center justify-between gap-2">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#FCEDEA] text-[#E87559] text-xs font-bold">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z" />
                    </svg>
                    <span>{{ $aiExplanation['is_ai_generated'] ? 'KasiVo AI Restock Narrative' : 'Ringkasan Cerdas Restock' }}</span>
                </div>

                @if ($recommendedCount > 0)
                    <span class="text-xs font-bold text-[#E87559]">
                        {{ $recommendedCount }} Bahan Perlu Tindakan
                    </span>
                @else
                    <span class="text-xs font-bold text-[#43A58A]">
                        Semua Bahan Aman
                    </span>
                @endif
            </div>

            <h3 class="text-lg sm:text-xl font-extrabold text-[#252525]">
                {{ $aiExplanation['summary_headline'] ?? 'Ringkasan Rekomendasi Belanja' }}
            </h3>

            <p class="text-sm text-[#555555] leading-relaxed">
                {{ $aiExplanation['ai_explanation'] ?? '' }}
            </p>

            @if (!empty($aiExplanation['actionable_advice']))
                <div class="pt-2 border-t border-gray-100 flex items-start gap-2 text-xs text-[#777777]">
                    <span class="font-bold text-[#252525]">Tips Belanja:</span>
                    <span>{{ $aiExplanation['actionable_advice'] }}</span>
                </div>
            @endif

            @if (!empty($aiExplanation['data_limitations']))
                <div class="p-3 rounded-xl bg-gray-50 border border-gray-200 text-[11px] text-[#777777] leading-relaxed">
                    <span class="font-bold text-[#252525]">Catatan Keterbatasan Data:</span>
                    <span class="ml-1">{{ $aiExplanation['data_limitations'] }}</span>
                </div>
            @endif
        </div>

        <!-- Metric Stat Cards (3 Kolom Responsif) -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <!-- Card 1: Bahan Perlu Belanja -->
            <div class="p-5 rounded-2xl bg-white border border-[#EEEEEE] shadow-sm flex items-center justify-between">
                <div>
                    <span class="block text-xs font-bold uppercase tracking-wider text-[#777777] mb-1">Perlu Restock</span>
                    <span class="text-2xl font-extrabold text-[#E87559]">{{ $recommendedCount }}</span>
                    <span class="text-xs text-[#777777] ml-1">bahan</span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-[#FCEDEA] text-[#E87559] flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                </div>
            </div>

            <!-- Card 2: Estimasi Total Anggaran Belanja -->
            <div class="p-5 rounded-2xl bg-white border border-[#EEEEEE] shadow-sm flex items-center justify-between">
                <div>
                    <span class="block text-xs font-bold uppercase tracking-wider text-[#777777] mb-1">Estimasi Anggaran</span>
                    <span class="text-2xl font-extrabold text-[#252525]">Rp {{ number_format($totalEstimatedBudget, 0, ',', '.') }}</span>
                    <span class="block text-[11px] text-[#777777] mt-0.5">Kebutuhan belanja prioritas</span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-[#E1F0F7] text-[#0284C7] flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>

            <!-- Card 3: Bahan Siklus Aman -->
            <div class="p-5 rounded-2xl bg-white border border-[#EEEEEE] shadow-sm flex items-center justify-between">
                <div>
                    <span class="block text-xs font-bold uppercase tracking-wider text-[#777777] mb-1">Siklus Aman</span>
                    <span class="text-2xl font-extrabold text-[#43A58A]">{{ $safeCount }}</span>
                    <span class="text-xs text-[#777777] ml-1">bahan</span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-[#E2F2ED] text-[#43A58A] flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- DAFTAR ITEM YANG DISARANKAN RESTOCK (Grid Responsif) -->
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg sm:text-xl font-extrabold text-[#252525]">
                        Bahan yang Perlu Restock
                    </h2>
                    <p class="text-xs text-[#777777]">
                        Bahan yang telah melewati atau mendekati siklus belanja rutin Anda.
                    </p>
                </div>
            </div>

            @if (empty($recommendedItems))
                <div class="p-8 rounded-3xl bg-white border border-[#EEEEEE] text-center">
                    <div class="w-12 h-12 mx-auto rounded-2xl bg-[#E2F2ED] text-[#43A58A] flex items-center justify-center mb-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-[#252525] mb-1">
                        Semua Bahan Masih Dalam Siklus Aman
                    </h3>
                    <p class="text-xs text-[#777777] max-w-md mx-auto">
                        Belum ada bahan baku yang mendesak untuk dibeli hari ini. Sistem akan terus memperbarui rekomendasi seiring berjalannya siklus belanja usaha Anda.
                    </p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach ($recommendedItems as $item)
                        <div class="p-5 rounded-2xl bg-white border border-[#EEEEEE] hover:border-gray-300 shadow-sm flex flex-col justify-between transition-all">
                            <div>
                                <!-- Header Item & Badge Urgensi -->
                                <div class="flex items-start justify-between gap-2 mb-3">
                                    <h3 class="font-extrabold text-[#252525] text-base leading-snug">
                                        {{ $item['item_name'] }}
                                    </h3>

                                    @if ($item['urgency'] === 'overdue')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-red-100 text-red-700 shrink-0">
                                            {{ $item['urgency_label'] }}
                                        </span>
                                    @elseif ($item['urgency'] === 'due_today')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 shrink-0">
                                            {{ $item['urgency_label'] }}
                                        </span>
                                    @elseif ($item['urgency'] === 'upcoming')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-yellow-100 text-yellow-800 shrink-0">
                                            {{ $item['urgency_label'] }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-gray-100 text-gray-700 shrink-0">
                                            {{ $item['urgency_label'] }}
                                        </span>
                                    @endif
                                </div>

                                <!-- Detail Saran Belanja -->
                                <div class="p-3.5 rounded-xl bg-gray-50 border border-gray-100 space-y-1.5 mb-3">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="text-[#777777]">Saran Belanja:</span>
                                        <span class="font-bold text-[#252525]">
                                            {{ (float) $item['suggested_quantity'] }} {{ $item['unit'] }}
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="text-[#777777]">Estimasi Biaya:</span>
                                        <span class="font-bold text-[#E87559]">
                                            Rp {{ number_format($item['estimated_cost'], 0, ',', '.') }}
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between text-xs pt-1 border-t border-gray-200">
                                        <span class="text-[#777777]">Siklus Rutin:</span>
                                        <span class="font-semibold text-[#252525]">
                                            Tiap {{ $item['average_interval_days'] }} hari
                                        </span>
                                    </div>
                                </div>

                                <!-- Alasan Pola Belanja (DoD KV-047 / KV-049) -->
                                <p class="text-xs text-[#666666] leading-relaxed mb-4">
                                    {{ $item['pattern_reason'] }}
                                </p>
                            </div>

                            <!-- Tombol Aksi Cepat Catat Belanja -->
                            <div class="pt-3 border-t border-gray-100">
                                <a href="{{ route('transactions.expense') }}"
                                   wire:navigate
                                   class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl bg-gray-100 hover:bg-[#FCEDEA] text-[#252525] hover:text-[#E87559] text-xs font-semibold transition-all">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                    </svg>
                                    <span>Catat Belanja {{ $item['item_name'] }}</span>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- DAFTAR ITEM SIKLUS AMAN (Safe Items) -->
        @if (!empty($safeItems))
            <div class="space-y-3 pt-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-extrabold text-[#252525]">
                            Bahan dalam Siklus Aman ({{ count($safeItems) }})
                        </h3>
                        <p class="text-xs text-[#777777]">
                            Bahan baku berikut masih memiliki jarak waktu lebih dari 2 hari menuju jadwal belanja berikutnya.
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    @foreach ($safeItems as $safeItem)
                        <div class="p-4 rounded-xl bg-white border border-[#EEEEEE] shadow-2xs">
                            <div class="flex items-center justify-between gap-2 mb-1.5">
                                <span class="font-bold text-xs text-[#252525] truncate">{{ $safeItem['item_name'] }}</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#E2F2ED] text-[#43A58A]">Aman</span>
                            </div>
                            <span class="block text-[11px] text-[#777777]">
                                Siklus tiap {{ $safeItem['average_interval_days'] }} hari
                            </span>
                            <span class="block text-[11px] text-[#777777]">
                                Jadwal: {{ \Carbon\Carbon::parse($safeItem['estimated_next_purchase_date'])->translatedFormat('d M Y') }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    @endif

</div>

