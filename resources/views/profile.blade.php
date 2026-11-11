<x-app-layout>
    <div class="max-w-4xl mx-auto space-y-6">

        <!-- Profile Header Intro -->
        <div class="p-6 rounded-3xl bg-white border border-[#EEEEEE] shadow-sm flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-[#FCEDEA] text-[#E87559] flex items-center justify-center font-extrabold text-xl shadow-sm border border-[#E87559]/20">
                    {{ strtoupper(substr(auth()->user()->name ?? 'K', 0, 2)) }}
                </div>
                <div>
                    <h1 class="text-xl font-extrabold text-[#252525]">
                        {{ auth()->user()->name }}
                    </h1>
                    <p class="text-xs text-[#777777]">
                        {{ auth()->user()->email }} • Pengelola Akun
                    </p>
                </div>
            </div>

            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#DDF1EC] text-[#43A58A] text-xs font-semibold">
                <span class="w-1.5 h-1.5 rounded-full bg-[#43A58A]"></span>
                <span>Terverifikasi</span>
            </span>
        </div>

        <!-- 1. Informasi Profil -->
        <div class="p-6 sm:p-8 bg-white border border-[#EEEEEE] shadow-sm rounded-3xl">
            <div class="max-w-xl">
                <livewire:profile.update-profile-information-form />
            </div>
        </div>

        <!-- 2. Perbarui Password -->
        <div class="p-6 sm:p-8 bg-white border border-[#EEEEEE] shadow-sm rounded-3xl">
            <div class="max-w-xl">
                <livewire:profile.update-password-form />
            </div>
        </div>

        <!-- 3. Hapus Akun -->
        <div class="p-6 sm:p-8 bg-white border border-[#EEEEEE] shadow-sm rounded-3xl">
            <div class="max-w-xl">
                <livewire:profile.delete-user-form />
            </div>
        </div>
    </div>
</x-app-layout>
