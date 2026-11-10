@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-200 focus:border-[#E87559] focus:ring-[#E87559] rounded-xl shadow-sm text-sm text-[#252525]']) }}>
