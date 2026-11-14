<div class="w-full flex-1 flex flex-col">
    <div class="bg-white shadow-sm rounded-2xl sm:rounded-3xl border border-[#EEEEEE] p-6 sm:p-8 w-full flex-1 flex flex-col justify-between">
        <div class="max-w-2xl mx-auto w-full">
            <div class="mb-6 pb-5 border-b border-[#EEEEEE]">
                <div class="flex items-center gap-2.5 mb-2">
                    <span class="w-8 h-8 rounded-xl bg-[#FCEDEA] text-[#E87559] flex items-center justify-center shrink-0 shadow-xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2M7 2v20M21 15V2a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3zm0 0v7"/>
                        </svg>
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-[#252525] tracking-tight">
                        Kenalkan Usaha Kulinermu
                    </h2>
                </div>
                <p class="mt-1 text-xs sm:text-sm text-[#777777] leading-relaxed">
                    Lengkapi profil usaha kulinermu agar KasiVo dapat menyesuaikan analisis keuangan dan rekomendasi restock bahan.
                </p>
            </div>

            @if (session()->has('message'))
                <div class="mb-5 p-4 text-xs sm:text-sm font-medium text-emerald-800 bg-[#DDF1EC] border border-emerald-200 rounded-xl">
                    {{ session('message') }}
                </div>
            @endif

            <form wire:submit="save" class="space-y-5">
                <!-- Nama Usaha -->
                <div>
                    <label for="name" class="block text-xs sm:text-sm font-semibold text-[#252525] mb-1.5">
                        Nama Usaha / Warung <span class="text-[#DC6464]">*</span>
                    </label>
                    <input
                        type="text"
                        id="name"
                        wire:model="name"
                        placeholder="Contoh: Warung Makan Berkah, Kedai Kopi Senja"
                        class="block w-full py-2.5 px-3.5 text-xs sm:text-sm rounded-xl border border-[#EEEEEE] text-[#252525] placeholder:text-[#BBBBBB] focus:ring-[#E87559]/20 focus:border-[#E87559] bg-[#FBFBFC] transition"
                    />
                    @error('name')
                        <span class="text-xs text-[#DC6464] mt-1 block font-medium">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Jenis Usaha F&B -->
                <div>
                    <label for="business_type" class="block text-xs sm:text-sm font-semibold text-[#252525] mb-1.5">
                        Jenis Usaha F&B <span class="text-[#DC6464]">*</span>
                    </label>
                    <input
                        type="text"
                        id="business_type"
                        wire:model="business_type"
                        placeholder="Contoh: Kedai Kopi, Pecel Lele, Warmindo, Seblak Prasmanan"
                        class="block w-full py-2.5 px-3.5 text-xs sm:text-sm rounded-xl border border-[#EEEEEE] text-[#252525] placeholder:text-[#BBBBBB] focus:ring-[#E87559]/20 focus:border-[#E87559] bg-[#FBFBFC] transition"
                    />
                    <p class="mt-1 text-[11px] text-[#777777]">
                        Bebas ketik sesuai jenis usaha kulinermu.
                    </p>
                    @error('business_type')
                        <span class="text-xs text-[#DC6464] mt-1 block font-medium">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Deskripsi Singkat -->
                <div>
                    <label for="description" class="block text-xs sm:text-sm font-semibold text-[#252525] mb-1.5">
                        Deskripsi Singkat <span class="text-xs text-[#777777] font-normal">(Opsional)</span>
                    </label>
                    <textarea
                        id="description"
                        wire:model="description"
                        rows="3"
                        placeholder="Contoh: Menyediakan aneka olahan ayam, bebek, dan sambal khas Lamongan sejak 2021."
                        class="block w-full py-2.5 px-3.5 text-xs sm:text-sm rounded-xl border border-[#EEEEEE] text-[#252525] placeholder:text-[#BBBBBB] focus:ring-[#E87559]/20 focus:border-[#E87559] bg-[#FBFBFC] transition resize-none leading-relaxed"
                    ></textarea>
                    @error('description')
                        <span class="text-xs text-[#DC6464] mt-1 block font-medium">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Tombol Simpan -->
                <div class="flex items-center justify-end pt-5 border-t border-[#EEEEEE]">
                    <button
                        type="submit"
                        class="inline-flex items-center justify-center min-h-[44px] px-6 py-2.5 rounded-xl text-xs sm:text-sm font-bold text-white bg-[#E87559] hover:bg-[#D66448] active:bg-[#C25338] shadow-xs hover:shadow-md transition active:scale-95 cursor-pointer"
                    >
                        Simpan & Lanjut ke Dashboard &rarr;
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
