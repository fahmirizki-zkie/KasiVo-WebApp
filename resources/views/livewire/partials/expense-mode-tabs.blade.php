@props([
    'currentMode' => 'voice', // 'voice' | 'photo' | 'text'
])

<div class="max-w-xl mx-auto w-full mb-6 sm:mb-8">
    <div class="grid grid-cols-3 gap-1.5 p-1.5 rounded-2xl bg-gray-100/80 border border-[#EEEEEE]">
        <a href="{{ route('transactions.photo') }}"
           wire:navigate
           class="flex items-center justify-center gap-1.5 py-2.5 px-2 sm:px-3 rounded-xl text-xs sm:text-sm font-semibold transition-all {{ $currentMode === 'photo' ? 'bg-white text-[#E87559] font-bold shadow-xs border border-[#E87559]/20' : 'text-[#777777] hover:text-[#252525] hover:bg-white/60' }}">
            <svg class="w-4 h-4 {{ $currentMode === 'photo' ? 'text-[#E87559]' : 'text-[#777777]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span class="truncate">Foto Nota</span>
        </a>

        <a href="{{ route('transactions.expense') }}"
           wire:navigate
           class="flex items-center justify-center gap-1.5 py-2.5 px-2 sm:px-3 rounded-xl text-xs sm:text-sm font-semibold transition-all {{ $currentMode === 'voice' ? 'bg-white text-[#E87559] font-bold shadow-xs border border-[#E87559]/20' : 'text-[#777777] hover:text-[#252525] hover:bg-white/60' }}">
            <svg class="w-4 h-4 {{ $currentMode === 'voice' ? 'text-[#E87559]' : 'text-[#777777]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
            </svg>
            <span class="truncate">Suara</span>
        </a>

        <a href="{{ route('transactions.text') }}"
           wire:navigate
           class="flex items-center justify-center gap-1.5 py-2.5 px-2 sm:px-3 rounded-xl text-xs sm:text-sm font-semibold transition-all {{ $currentMode === 'text' ? 'bg-white text-[#E87559] font-bold shadow-xs border border-[#E87559]/20' : 'text-[#777777] hover:text-[#252525] hover:bg-white/60' }}">
            <svg class="w-4 h-4 {{ $currentMode === 'text' ? 'text-[#E87559]' : 'text-[#777777]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <span class="truncate">Teks</span>
        </a>
    </div>
</div>

