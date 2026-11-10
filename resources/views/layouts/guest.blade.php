<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'KasiVo') }}</title>

        <!-- Favicon -->
        <link rel="icon" type="image/png" href="{{ asset('images/kasivo-icon.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-[#252525] antialiased bg-gradient-to-br from-[#FCEDEA]/60 via-white to-[#E1F0F7]/40 min-h-screen">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-8 sm:pt-0 px-4">
            <div class="mb-6">
                <a href="/" wire:navigate class="inline-block hover:opacity-90 transition transform hover:scale-[1.02]">
                    <x-application-logo class="h-12 sm:h-14 w-auto object-contain" />
                </a>
            </div>

            <div class="w-full sm:max-w-md px-6 py-8 bg-white border border-[#EEEEEE] shadow-card rounded-3xl overflow-hidden">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
