@props(['size' => 'md', 'showTagline' => false, 'iconOnly' => false])

@php
    $heights = [
        'sm' => 'h-7 sm:h-8',
        'md' => 'h-9 sm:h-10',
        'lg' => 'h-11 sm:h-12',
        'xl' => 'h-14 sm:h-16',
    ];
    $iconHeights = [
        'sm' => 'h-7',
        'md' => 'h-9',
        'lg' => 'h-11',
        'xl' => 'h-14',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'inline-flex items-center gap-2 select-none']) }}>
    @if($iconOnly)
        <img src="{{ asset('images/kasivo-icon.png') }}" 
             alt="KasiVo Icon" 
             class="{{ $iconHeights[$size] ?? $iconHeights['md'] }} w-auto object-contain">
    @else
        <div class="flex flex-col">
            <img src="{{ asset('images/kasivo-logo.png') }}" 
                 alt="KasiVo Logo" 
                 class="{{ $heights[$size] ?? $heights['md'] }} w-auto object-contain">
            @if($showTagline)
                <span class="text-[11px] text-[#777777] font-medium tracking-normal mt-1">Smart Business POS</span>
            @endif
        </div>
    @endif
</div>
