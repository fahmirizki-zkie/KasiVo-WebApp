<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-5 py-2.5 bg-[#E87559] hover:bg-[#D66448] active:bg-[#C55338] border border-transparent rounded-xl font-semibold text-sm text-white tracking-wide shadow-sm shadow-[#E87559]/25 hover:shadow-md hover:shadow-[#E87559]/35 focus:outline-none focus:ring-2 focus:ring-[#E87559] focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
