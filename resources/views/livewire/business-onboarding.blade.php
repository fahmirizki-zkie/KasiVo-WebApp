<div class="w-full flex-1 flex flex-col">
    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-2xl p-6 sm:p-8 w-full flex-1 flex flex-col justify-between">
            <div class="mb-6">
                <div class="flex items-center gap-2.5 mb-2">
                    <span class="w-8 h-8 rounded-xl bg-[#FCEDEA] text-[#E87559] flex items-center justify-center shrink-0 shadow-xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2M7 2v20M21 15V2a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3zm0 0v7"/>
                        </svg>
                    </span>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                        Kenalkan Usaha Kulinermu
                    </h2>
                </div>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Lengkapi profil usaha kulinermu agar KasiVo dapat menyesuaikan analisis keuangan dan rekomendasi restock bahan.
                </p>
            </div>

            @if (session()->has('message'))
                <div class="mb-4 p-4 text-sm text-green-700 bg-green-100 rounded-lg dark:bg-green-200 dark:text-green-800">
                    {{ session('message') }}
                </div>
            @endif

            <form wire:submit="save" class="space-y-6">
                <!-- Nama Usaha -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Nama Usaha / Warung <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="name"
                        wire:model="name"
                        placeholder="Contoh: Warung Makan Berkah, Kedai Kopi Senja"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    />
                    @error('name')
                        <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Jenis Usaha F&B -->
                <div>
                    <label for="business_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Jenis Usaha F&B <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="business_type"
                        wire:model="business_type"
                        placeholder="Contoh: Kedai Kopi, Pecel Lele, Warmindo, Seblak Prasmanan"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    />
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Bebas ketik sesuai jenis usaha kulinermu.
                    </p>
                    @error('business_type')
                        <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Deskripsi Singkat -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Deskripsi Singkat (Opsional)
                    </label>
                    <textarea
                        id="description"
                        wire:model="description"
                        rows="3"
                        placeholder="Contoh: Menyediakan aneka olahan ayam, bebek, dan sambal khas Lamongan sejak 2021."
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    ></textarea>
                    @error('description')
                        <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Tombol Simpan -->
                <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button
                        type="submit"
                        class="inline-flex justify-center py-2 px-5 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition"
                    >
                        Simpan & Lanjut ke Dashboard
                    </button>
                </div>
            </form>
        </div>
</div>
