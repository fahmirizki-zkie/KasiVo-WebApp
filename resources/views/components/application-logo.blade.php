@props(['iconOnly' => false])

@if($iconOnly)
    <img src="{{ asset('images/kasivo-icon.png') }}" 
         alt="{{ config('app.name', 'KasiVo') }}" 
         {{ $attributes->merge(['class' => 'h-9 w-auto object-contain select-none']) }} />
@else
    <img src="{{ asset('images/kasivo-logo.png') }}" 
         alt="{{ config('app.name', 'KasiVo') }}" 
         {{ $attributes->merge(['class' => 'h-9 w-auto object-contain select-none']) }} />
@endif
