<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-[#FBFBFC]">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'KasiVo') }} — Smart Business POS</title>

        <!-- Favicon -->
        <link rel="icon" type="image/png" href="{{ asset('images/kasivo-icon.png') }}">

        <!-- Fonts: Plus Jakarta Sans & Inter -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="h-full font-sans antialiased text-[#252525] bg-[#FBFBFC] selection:bg-[#E87559] selection:text-white overflow-x-hidden">
        <div class="min-h-screen flex flex-col">
            <!-- Responsive Navigation System (Desktop Sidebar, Topbar, Mobile Drawer, Mobile Bottom Nav) -->
            <livewire:layout.navigation />

            <!-- Main Content Area: Offset on Desktop by lg:pl-64, Padded on Mobile for Bottom Nav -->
            <div class="lg:pl-64 flex-1 flex flex-col min-h-screen">
                @if (isset($header))
                    <div class="px-3 sm:px-5 lg:px-6 pt-3 sm:pt-4 pb-1 w-full">
                        {{ $header }}
                    </div>
                @endif

                <main class="flex-1 px-3 sm:px-5 lg:px-6 pt-3 sm:pt-4 pb-20 lg:pb-6 w-full flex flex-col">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
