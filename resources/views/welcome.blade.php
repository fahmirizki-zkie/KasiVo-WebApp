<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="KasiVo — AI-Powered Cashier & Business Management Platform. Kelola transaksi, stok persediaan, dan analisa bisnis F&B dengan bantuan kecerdasan buatan.">

    <title>KasiVo — AI-Powered Cashier & Business Management Platform</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/kasivo-icon.png') }}">

    <!-- Google Fonts: Plus Jakarta Sans & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Styles & Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Plus Jakarta Sans', Inter, -apple-system, BlinkMacSystemFont, sans-serif;
            color: #252525;
            background-color: #FFFFFF;
        }
        .bg-grid-pattern {
            background-size: 32px 32px;
            background-image:
                linear-gradient(to right, rgba(0, 0, 0, 0.02) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(0, 0, 0, 0.02) 1px, transparent 1px);
        }
    </style>
</head>
<body class="antialiased bg-white text-[#252525] selection:bg-[#E87559] selection:text-white">

    <!-- ======================================================== -->
    <!-- SECTION A: NAVBAR                                        -->
    <!-- ======================================================== -->
    <header class="sticky top-0 z-50 bg-white/85 backdrop-blur-md border-b border-[#EEEEEE] transition-all">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <!-- Brand Logo -->
                <a href="#hero" class="flex items-center gap-2 group">
                    <x-kasivo-logo size="md" :showTagline="false" />
                </a>

                <!-- Desktop Navigation Links -->
                <nav class="hidden lg:flex items-center gap-1 lg:gap-2">
                    <a href="#hero" class="px-3.5 py-2 text-sm font-medium text-[#252525] hover:text-[#E87559] rounded-xl hover:bg-[#FCEDEA]/50 transition-colors">
                        Home
                    </a>
                    <a href="#features" class="px-3.5 py-2 text-sm font-medium text-[#777777] hover:text-[#252525] rounded-xl hover:bg-[#FCEDEA]/50 transition-colors">
                        Features
                    </a>
                    <a href="#why-us" class="px-3.5 py-2 text-sm font-medium text-[#777777] hover:text-[#252525] rounded-xl hover:bg-[#FCEDEA]/50 transition-colors">
                        Why KasiVo
                    </a>
                    <a href="#how-it-works" class="px-3.5 py-2 text-sm font-medium text-[#777777] hover:text-[#252525] rounded-xl hover:bg-[#FCEDEA]/50 transition-colors">
                        How It Works
                    </a>
                    <a href="#testimonials" class="px-3.5 py-2 text-sm font-medium text-[#777777] hover:text-[#252525] rounded-xl hover:bg-[#FCEDEA]/50 transition-colors">
                        Testimonials
                    </a>
                </nav>

                <!-- Auth / Action Buttons -->
                <div class="hidden sm:flex items-center gap-3">
                    @auth
                        <a href="{{ Route::has('dashboard') ? route('dashboard') : url('/dashboard') }}"
                           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#E87559] hover:bg-[#D66448] text-white text-sm font-semibold shadow-sm shadow-[#E87559]/25 hover:shadow-md hover:shadow-[#E87559]/35 transition-all">
                            <span>Open Dashboard</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    @else
                        @if (Route::has('login'))
                            <a href="{{ route('login') }}"
                               class="px-4 py-2 text-sm font-semibold text-[#252525] hover:text-[#E87559] rounded-xl transition-colors">
                                Login
                            </a>
                        @endif

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}"
                               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#E87559] hover:bg-[#D66448] text-white text-sm font-semibold shadow-sm shadow-[#E87559]/25 hover:shadow-md hover:shadow-[#E87559]/35 transition-all transform active:scale-95">
                                <span>Get Started</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        @endif
                    @endauth
                </div>

                <!-- Mobile & Tablet Hamburger Button -->
                <div class="flex lg:hidden">
                    <button type="button"
                            id="mobile-menu-btn"
                            aria-label="Toggle Navigation Menu"
                            class="p-2 rounded-xl text-[#252525] hover:bg-[#FCEDEA] transition-colors focus:outline-none">
                        <svg id="menu-icon" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                        <svg id="close-icon" class="w-6 h-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Mobile & Tablet Navigation Drawer -->
            <div id="mobile-menu" class="hidden lg:hidden py-4 border-t border-[#EEEEEE] space-y-2">
                <a href="#hero" class="mobile-nav-link block px-3 py-2 text-base font-medium text-[#252525] rounded-lg hover:bg-[#FCEDEA]">Home</a>
                <a href="#features" class="mobile-nav-link block px-3 py-2 text-base font-medium text-[#777777] hover:text-[#252525] rounded-lg hover:bg-[#FCEDEA]">Features</a>
                <a href="#why-us" class="mobile-nav-link block px-3 py-2 text-base font-medium text-[#777777] hover:text-[#252525] rounded-lg hover:bg-[#FCEDEA]">Why KasiVo</a>
                <a href="#how-it-works" class="mobile-nav-link block px-3 py-2 text-base font-medium text-[#777777] hover:text-[#252525] rounded-lg hover:bg-[#FCEDEA]">How It Works</a>
                <a href="#testimonials" class="mobile-nav-link block px-3 py-2 text-base font-medium text-[#777777] hover:text-[#252525] rounded-lg hover:bg-[#FCEDEA]">Testimonials</a>

                <div class="pt-4 border-t border-[#EEEEEE] flex flex-col gap-2">
                    @auth
                        <a href="{{ Route::has('dashboard') ? route('dashboard') : url('/dashboard') }}" class="w-full text-center px-4 py-2.5 rounded-xl bg-[#E87559] text-white text-sm font-semibold">
                            Open Dashboard
                        </a>
                    @else
                        @if (Route::has('login'))
                            <a href="{{ route('login') }}" class="w-full text-center px-4 py-2 rounded-xl text-sm font-semibold text-[#252525] border border-[#EEEEEE]">
                                Login
                            </a>
                        @endif
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="w-full text-center px-4 py-2.5 rounded-xl bg-[#E87559] text-white text-sm font-semibold shadow-sm">
                                Get Started
                            </a>
                        @endif
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <main>
        <!-- ======================================================== -->
        <!-- SECTION B: HERO SECTION                                  -->
        <!-- ======================================================== -->
        <section id="hero" class="relative isolate pt-12 pb-20 lg:pt-20 lg:pb-32 overflow-hidden bg-grid-pattern">
            <!-- ======================================================== -->
            <!-- REFINED SUBTLE ABSTRACT BACKGROUND & CORAL ACCENTS       -->
            <!-- ======================================================== -->
            <!-- A. Top Right Hero: Soft Coral / Orange Radial Glow Blob -->
            <div class="absolute -top-12 sm:-top-20 -right-12 sm:-right-20 lg:-right-10 w-[320px] sm:w-[460px] lg:w-[580px] h-[320px] sm:h-[460px] lg:h-[580px] rounded-full bg-coral-glow filter blur-[60px] lg:blur-[85px] pointer-events-none z-0 select-none"></div>

            <!-- B1. Left Diagonal Hero: Directional Coral / Orange Gradient Flow (Contained & Non-intrusive) -->
            <div class="absolute -top-16 sm:-top-24 -left-16 sm:-left-24 lg:-left-12 w-[300px] sm:w-[420px] lg:w-[500px] h-[360px] sm:h-[480px] lg:h-[560px] rounded-[60px] bg-left-diagonal-coral filter blur-[50px] sm:blur-[75px] pointer-events-none z-0 select-none"></div>

            <!-- B2. Mid/Lower Left Soft Peach Anchor Glow -->
            <div class="absolute top-[48%] sm:top-[52%] -left-12 sm:-left-20 lg:-left-6 w-[260px] sm:w-[360px] lg:w-[420px] h-[260px] sm:h-[360px] lg:h-[420px] rounded-full bg-coral-warm-glow filter blur-[55px] lg:blur-[75px] pointer-events-none z-0 select-none"></div>

            <!-- C. Soft Ambient Background Glow (Central whitespace preserved) -->
            <div class="absolute top-4 left-1/2 -translate-x-1/2 w-[700px] h-[360px] bg-gradient-to-b from-[#FCEDEA]/50 via-[#FCEDEA]/20 to-transparent rounded-full blur-3xl -z-10 pointer-events-none"></div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">

                <!-- Main Hero Headline -->
                <h1 class="text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-extrabold text-[#252525] tracking-tight leading-[1.12] max-w-4xl mx-auto">
                    Manage Your Business <br class="hidden sm:inline" />
                    <span class="relative inline-block text-[#E87559]">
                        Smarter
                        <!-- Subtle wave underline in coral -->
                        <svg class="absolute -bottom-2 left-0 w-full h-3 text-[#E87559]/30" viewBox="0 0 200 8" fill="none" preserveAspectRatio="none">
                            <path d="M1 5.5C40 2 60 7 100 4.5C140 2 160 6.5 199 4" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                        </svg>
                    </span>
                    with KasiVo
                </h1>

                <!-- Supporting Text -->
                <p class="mt-6 text-base sm:text-lg md:text-xl text-[#777777] max-w-2xl mx-auto leading-relaxed">
                    Catat transaksi lebih mudah, kelola stok secara praktis, dan pahami perkembangan bisnis melalui teknologi AI dalam satu platform.
                </p>

                <!-- CTA Action Buttons -->
                <div class="mt-8 sm:mt-10 flex flex-col sm:flex-row items-center justify-center gap-4 max-w-md mx-auto sm:max-w-none">
                    @auth
                        <a href="{{ Route::has('dashboard') ? route('dashboard') : url('/dashboard') }}"
                           class="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 px-8 py-4 rounded-2xl bg-[#E87559] hover:bg-[#D66448] text-white font-semibold text-base shadow-lg shadow-[#E87559]/25 hover:shadow-xl hover:shadow-[#E87559]/35 transition-all transform active:scale-95">
                            <span>Open Dashboard</span>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                        <a href="#features"
                           class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-4 rounded-2xl bg-white hover:bg-gray-50 text-[#252525] font-semibold text-base border border-[#EEEEEE] hover:border-[#E87559]/30 shadow-sm transition-all">
                            <span>Explore Features</span>
                            <svg class="w-4 h-4 text-[#777777]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </a>
                    @else
                        <a href="{{ Route::has('register') ? route('register') : url('/register') }}"
                           class="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 px-8 py-4 rounded-2xl bg-[#E87559] hover:bg-[#D66448] text-white font-semibold text-base shadow-lg shadow-[#E87559]/25 hover:shadow-xl hover:shadow-[#E87559]/35 transition-all transform active:scale-95">
                            <span>Get Started Free</span>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>

                        <!-- Mobile CTA: Tombol Login Langsung (Memudahkan Pengguna Mobile) -->
                        <a href="{{ Route::has('login') ? route('login') : url('/login') }}"
                           class="sm:hidden w-full inline-flex items-center justify-center gap-2 px-8 py-4 rounded-2xl bg-white hover:bg-gray-50 text-[#252525] font-semibold text-base border border-[#EEEEEE] hover:border-[#E87559]/30 shadow-sm transition-all">
                            <span>Login ke Akun</span>
                            <svg class="w-4 h-4 text-[#777777]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                            </svg>
                        </a>

                        <!-- Desktop CTA: Explore Features (karena tombol Login sudah ada di navbar atas) -->
                        <a href="#features"
                           class="hidden sm:inline-flex w-auto items-center justify-center gap-2 px-8 py-4 rounded-2xl bg-white hover:bg-gray-50 text-[#252525] font-semibold text-base border border-[#EEEEEE] hover:border-[#E87559]/30 shadow-sm transition-all">
                            <span>Explore Features</span>
                            <svg class="w-4 h-4 text-[#777777]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </a>
                    @endauth
                </div>

                <!-- Target F&B Business Fit (Authentic & Modern SaaS Aesthetic) -->
                <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-2.5 sm:gap-3 text-xs text-[#777777]">
                    <span class="font-medium text-[#777777] flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#E87559]"></span>
                        <span>Dirancang khusus untuk operasional:</span>
                    </span>
                    <div class="flex flex-wrap items-center justify-center gap-2">
                        <span class="px-3.5 py-1.5 rounded-full bg-white border border-[#EEEEEE] shadow-sm font-semibold text-[#252525] hover:border-[#E87559]/30 transition-all flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-[#E87559]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18 8h1a4 4 0 0 1 0 8h-1M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8zM6 1v3M10 1v3M14 1v3"/></svg>
                            <span>Coffee Shop</span>
                        </span>
                        <span class="px-3.5 py-1.5 rounded-full bg-white border border-[#EEEEEE] shadow-sm font-semibold text-[#252525] hover:border-[#E87559]/30 transition-all flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-[#E87559]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2M7 2v20M21 15V2a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3zm0 0v7"/></svg>
                            <span>Resto & Cafe</span>
                        </span>
                        <span class="px-3.5 py-1.5 rounded-full bg-white border border-[#EEEEEE] shadow-sm font-semibold text-[#252525] hover:border-[#E87559]/30 transition-all flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-[#E87559]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 2a5 5 0 0 1 5 5v1H7V7a5 5 0 0 1 5-5zM4 14a8 8 0 0 0 16 0v-3H4v3zM2 19h20M7 19v3M17 19v3"/></svg>
                            <span>Bakery & Pastry</span>
                        </span>
                        <span class="px-3.5 py-1.5 rounded-full bg-white border border-[#EEEEEE] shadow-sm font-semibold text-[#252525] hover:border-[#E87559]/30 transition-all flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-[#E87559]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2zM9 22V12h6v10"/></svg>
                            <span>Cloud Kitchen</span>
                        </span>
                    </div>
                </div>

                <!-- ======================================================== -->
                <!-- PRODUCT DASHBOARD MOCKUP (Centerpiece Visual)            -->
                <!-- ======================================================== -->
                <div class="relative max-w-6xl mx-auto mt-14 sm:mt-16 lg:mt-20">
                    <!-- Ambient Glow Behind Mockup -->
                    <div class="absolute -inset-3 sm:-inset-6 bg-gradient-to-r from-[#FCEDEA] via-[#E1F0F7] to-[#DDF1EC] rounded-3xl blur-2xl opacity-70 -z-10"></div>
                    <!-- Subtle Coral Brand Glow Behind Mockup -->
                    <div class="absolute -top-12 sm:-top-20 left-1/2 -translate-x-1/2 w-[85%] sm:w-[92%] h-[280px] sm:h-[380px] bg-mockup-coral-glow rounded-full filter blur-[65px] sm:blur-[90px] pointer-events-none -z-10 animate-coral-mockup-glow"></div>

                    <!-- Dashboard Window Surface -->
                    <div class="bg-white rounded-3xl border border-[#EEEEEE] shadow-2xl shadow-black/5 overflow-hidden text-left">

                        <!-- Top Window Header / App Bar -->
                        <div class="px-5 py-3.5 bg-gray-50/80 border-b border-[#EEEEEE] flex flex-wrap items-center justify-between gap-3">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-[#E87559]"></span>
                                <span class="w-3 h-3 rounded-full bg-[#E7A34A]"></span>
                                <span class="w-3 h-3 rounded-full bg-[#43A58A]"></span>
                                <div class="hidden sm:flex items-center gap-2 ml-4 px-3 py-1 bg-white rounded-lg border border-[#EEEEEE] text-xs text-[#777777]">
                                    <svg class="w-3.5 h-3.5 text-[#43A58A]" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                                    <span>kasivo.app/fnb/pos — Cabang Senopati</span>
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-[#DDF1EC] text-[#43A58A] text-xs font-semibold">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#43A58A] animate-pulse"></span>
                                    <span>Shift Siang: Aktif</span>
                                </span>
                                <span class="text-xs text-[#777777] hidden md:inline">Kasir: Putri R.</span>
                            </div>
                        </div>

                        <!-- Dashboard Interior -->
                        <div class="p-4 sm:p-6 lg:p-8 space-y-6 bg-gradient-to-b from-white to-[#FCEDEA]/20">

                            <!-- 1. Top Metrics Summary Row -->
                            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                                <!-- Card 1: Total Sales -->
                                <div class="bg-white p-3.5 sm:p-5 rounded-2xl border border-[#EEEEEE] shadow-sm hover:border-[#E87559]/30 transition">
                                    <div class="flex items-center justify-between text-[#777777] text-[11px] sm:text-xs font-medium">
                                        <span>Total Penjualan Hari Ini</span>
                                        <span class="px-2 py-0.5 rounded-full bg-[#DDF1EC] text-[#43A58A] font-semibold text-[10px] sm:text-[11px]">+18.4%</span>
                                    </div>
                                    <div class="mt-2 text-xl sm:text-2xl font-extrabold text-[#252525]">
                                        Rp 14.850.000
                                    </div>
                                    <div class="mt-1 text-[11px] sm:text-xs text-[#777777] flex items-center gap-1">
                                        <span class="text-[#43A58A] font-semibold">↑ Rp 2.3jt</span> vs hari kemarin
                                    </div>
                                </div>

                                <!-- Card 2: Total Transactions -->
                                <div class="bg-white p-3.5 sm:p-5 rounded-2xl border border-[#EEEEEE] shadow-sm hover:border-[#E87559]/30 transition">
                                    <div class="flex items-center justify-between text-[#777777] text-[11px] sm:text-xs font-medium">
                                        <span>Total Transaksi</span>
                                        <span class="px-2 py-0.5 rounded-full bg-[#E1F0F7] text-[#252525] font-semibold text-[10px] sm:text-[11px]">184 Order</span>
                                    </div>
                                    <div class="mt-2 text-xl sm:text-2xl font-extrabold text-[#252525]">
                                        184 Tiket
                                    </div>
                                    <div class="mt-1 text-[11px] sm:text-xs text-[#777777]">
                                        Rata-rata: <span class="font-semibold text-[#252525]">Rp 80.700</span> / order
                                    </div>
                                </div>

                                <!-- Card 3: Inventory Status -->
                                <div class="bg-white p-3.5 sm:p-5 rounded-2xl border border-[#EEEEEE] shadow-sm hover:border-[#E87559]/30 transition">
                                    <div class="flex items-center justify-between text-[#777777] text-[11px] sm:text-xs font-medium">
                                        <span>Status Persediaan</span>
                                        <span class="px-2 py-0.5 rounded-full bg-[#FCEDEA] text-[#E87559] font-semibold text-[10px] sm:text-[11px]">2 Perlu Restock</span>
                                    </div>
                                    <div class="mt-2 text-xl sm:text-2xl font-extrabold text-[#252525]">
                                        94% Aman
                                    </div>
                                    <div class="mt-1 text-[11px] sm:text-xs text-[#E87559] font-medium flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 shrink-0 text-[#E87559]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                                        <span class="truncate">Arabika Beans sisa 1.2kg</span>
                                    </div>
                                </div>

                                <!-- Card 4: AI Business Health -->
                                <div class="bg-gradient-to-br from-[#FCEDEA] to-[#E1F0F7] p-3.5 sm:p-5 rounded-2xl border border-[#E87559]/20 shadow-sm">
                                    <div class="flex items-center justify-between text-[#252525] text-[11px] sm:text-xs font-semibold">
                                        <span>AI Business Score</span>
                                        <span class="px-2 py-0.5 rounded-full bg-white text-[#E87559] font-bold text-[10px] sm:text-[11px]">EXCELLENT</span>
                                    </div>
                                    <div class="mt-2 text-xl sm:text-2xl font-extrabold text-[#E87559] flex items-center gap-1.5">
                                        <span>96</span><span class="text-xs sm:text-sm font-semibold text-[#777777]">/100</span>
                                    </div>
                                    <div class="mt-1 text-[11px] sm:text-xs text-[#252525] font-medium">
                                        Efisiensi Dapur: <span class="text-[#43A58A] font-bold">+24%</span>
                                    </div>
                                </div>
                            </div>

                            <!-- 2. Middle Grid: Sales Velocity & Live Voice POS -->
                            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                                <!-- Left Column: Revenue Velocity & Peak Hours (8 cols) -->
                                <div class="lg:col-span-8 bg-white p-5 sm:p-6 rounded-2xl border border-[#EEEEEE] shadow-sm space-y-4">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <div>
                                            <h2 class="text-base font-bold text-[#252525]">Revenue Overview & Jam Sibuk</h2>
                                            <p class="text-xs text-[#777777]">Grafik transaksi per jam (F&B Peak Traffic Analysis)</p>
                                        </div>
                                        <div class="flex items-center gap-2 text-xs">
                                            <span class="inline-flex items-center gap-1 text-[#777777]">
                                                <span class="w-2.5 h-2.5 rounded-full bg-[#E87559]"></span> Dine-In
                                            </span>
                                            <span class="inline-flex items-center gap-1 text-[#777777]">
                                                <span class="w-2.5 h-2.5 rounded-full bg-[#43A58A]"></span> Takeaway
                                            </span>
                                            <span class="inline-flex items-center gap-1 text-[#777777]">
                                                <span class="w-2.5 h-2.5 rounded-full bg-[#E1F0F7] border border-blue-300"></span> Online
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Visual Chart Simulation -->
                                    <div class="pt-4 pb-2">
                                        <div class="h-44 w-full flex items-end gap-2 sm:gap-4 justify-between border-b border-[#EEEEEE] pb-2">
                                            <!-- 08:00 (Pagi) -->
                                            <div class="flex-1 flex flex-col items-center gap-1 h-full justify-end group">
                                                <div class="w-full bg-[#FCEDEA] hover:bg-[#E87559] transition-all rounded-t-lg h-[35%] relative">
                                                    <span class="opacity-0 group-hover:opacity-100 transition absolute -top-7 left-1/2 -translate-x-1/2 bg-[#252525] text-white text-[10px] px-1.5 py-0.5 rounded whitespace-nowrap">Rp 1.8M</span>
                                                </div>
                                                <span class="text-[11px] text-[#777777]">08:00</span>
                                            </div>

                                            <!-- 10:00 -->
                                            <div class="flex-1 flex flex-col items-center gap-1 h-full justify-end group">
                                                <div class="w-full bg-[#FCEDEA] hover:bg-[#E87559] transition-all rounded-t-lg h-[50%] relative">
                                                    <span class="opacity-0 group-hover:opacity-100 transition absolute -top-7 left-1/2 -translate-x-1/2 bg-[#252525] text-white text-[10px] px-1.5 py-0.5 rounded whitespace-nowrap">Rp 2.4M</span>
                                                </div>
                                                <span class="text-[11px] text-[#777777]">10:00</span>
                                            </div>

                                            <!-- 12:00 (Lunch Rush) -->
                                            <div class="flex-1 flex flex-col items-center gap-1 h-full justify-end group">
                                                <div class="w-full bg-[#E87559] rounded-t-lg h-[92%] relative shadow-sm shadow-[#E87559]/30">
                                                    <span class="absolute -top-7 left-1/2 -translate-x-1/2 bg-[#E87559] text-white text-[10px] font-bold px-2 py-0.5 rounded-full whitespace-nowrap flex items-center gap-1 shadow-sm">
                                                        <svg class="w-2.5 h-2.5 fill-current" viewBox="0 0 24 24"><path d="M12 2c.4 0 .7.2.9.5 2 2.6 4.6 6.3 4.6 9.5 0 4.1-3.4 7.5-7.5 7.5S2.5 16.1 2.5 12c0-3.3 2.1-6.8 4.3-9.5.3-.4.8-.5 1.2-.2.4.3.5.8.3 1.2-.7 1.8-.8 3.4-.3 4.4.9-1.2 2.3-2.6 3.6-5.4.1-.3.4-.5.7-.5z"/></svg>
                                                        <span>PEAK</span>
                                                    </span>
                                                </div>
                                                <span class="text-[11px] font-bold text-[#E87559]">12:00</span>
                                            </div>

                                            <!-- 14:00 (Lunch Rush 2) -->
                                            <div class="flex-1 flex flex-col items-center gap-1 h-full justify-end group">
                                                <div class="w-full bg-[#E87559]/80 rounded-t-lg h-[75%] relative">
                                                    <span class="opacity-0 group-hover:opacity-100 transition absolute -top-7 left-1/2 -translate-x-1/2 bg-[#252525] text-white text-[10px] px-1.5 py-0.5 rounded whitespace-nowrap">Rp 3.6M</span>
                                                </div>
                                                <span class="text-[11px] text-[#777777]">14:00</span>
                                            </div>

                                            <!-- 16:00 (Afternoon Chill) -->
                                            <div class="flex-1 flex flex-col items-center gap-1 h-full justify-end group">
                                                <div class="w-full bg-[#FCEDEA] hover:bg-[#E87559] transition-all rounded-t-lg h-[45%] relative">
                                                    <span class="opacity-0 group-hover:opacity-100 transition absolute -top-7 left-1/2 -translate-x-1/2 bg-[#252525] text-white text-[10px] px-1.5 py-0.5 rounded whitespace-nowrap">Rp 2.1M</span>
                                                </div>
                                                <span class="text-[11px] text-[#777777]">16:00</span>
                                            </div>

                                            <!-- 18:00 (Dinner Peak) -->
                                            <div class="flex-1 flex flex-col items-center gap-1 h-full justify-end group">
                                                <div class="w-full bg-[#43A58A] rounded-t-lg h-[85%] relative shadow-sm shadow-[#43A58A]/30">
                                                    <span class="opacity-0 group-hover:opacity-100 transition absolute -top-7 left-1/2 -translate-x-1/2 bg-[#43A58A] text-white text-[10px] font-bold px-1.5 py-0.5 rounded whitespace-nowrap">DINNER</span>
                                                </div>
                                                <span class="text-[11px] font-bold text-[#43A58A]">18:00</span>
                                            </div>

                                            <!-- 20:00 (Closing Rush) -->
                                            <div class="flex-1 flex flex-col items-center gap-1 h-full justify-end group">
                                                <div class="w-full bg-[#FCEDEA] hover:bg-[#E87559] transition-all rounded-t-lg h-[60%] relative">
                                                    <span class="opacity-0 group-hover:opacity-100 transition absolute -top-7 left-1/2 -translate-x-1/2 bg-[#252525] text-white text-[10px] px-1.5 py-0.5 rounded whitespace-nowrap">Rp 2.8M</span>
                                                </div>
                                                <span class="text-[11px] text-[#777777]">20:00</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Top Seller Quick Badges -->
                                    <div class="flex flex-wrap items-center gap-2 pt-1 text-xs">
                                        <span class="font-semibold text-[#252525] flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5 text-[#E87559] shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941"/></svg>
                                            <span>Top Seller:</span>
                                        </span>
                                        <span class="px-2.5 py-1 rounded-lg bg-[#FCEDEA] text-[#E87559] font-medium">Kopi Susu Aren (84x)</span>
                                        <span class="px-2.5 py-1 rounded-lg bg-[#DDF1EC] text-[#43A58A] font-medium">Butter Croissant (42x)</span>
                                        <span class="px-2.5 py-1 rounded-lg bg-[#E1F0F7] text-[#252525] font-medium">Matcha Latte (31x)</span>
                                    </div>
                                </div>

                                <!-- Right Column: Smart AI Voice POS in Action (4 cols) -->
                                <div class="lg:col-span-4 bg-gradient-to-br from-[#FCEDEA] via-white to-[#E1F0F7] p-5 rounded-2xl border border-[#E87559]/20 shadow-sm flex flex-col justify-between">
                                    <div>
                                        <div class="flex items-center justify-between pb-3 border-b border-[#EEEEEE]">
                                            <div class="flex items-center gap-2">
                                                <div class="w-7 h-7 rounded-lg bg-[#E87559] text-white flex items-center justify-center">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/></svg>
                                                </div>
                                                <span class="text-xs font-bold text-[#252525]">AI Voice POS Assistant</span>
                                            </div>
                                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-[#E87559] text-white">LIVE</span>
                                        </div>

                                        <!-- Voice Prompt Bubble -->
                                        <div class="mt-4 p-3 rounded-xl bg-white border border-[#E87559]/30 shadow-sm">
                                            <div class="flex items-center gap-1.5 text-[11px] text-[#E87559] font-bold">
                                                <span class="w-2 h-2 rounded-full bg-[#E87559] animate-ping"></span>
                                                <span>Input Suara Pelayan:</span>
                                            </div>
                                            <p class="mt-1 text-xs text-[#252525] font-medium italic">
                                                "Dua Caramel Latte less sugar, satu Croissant hangat untuk Meja 04."
                                            </p>
                                        </div>

                                        <!-- AI Parsed Order Result -->
                                        <div class="mt-3 p-3 rounded-xl bg-white/90 border border-[#EEEEEE] space-y-2 text-xs">
                                            <div class="flex justify-between items-center text-[#777777] text-[11px]">
                                                <span>Item Terdeteksi</span>
                                                <span class="font-bold text-[#43A58A] inline-flex items-center gap-1">
                                                    <svg class="w-3.5 h-3.5 text-[#43A58A] shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                                    <span>Auto-Parsed</span>
                                                </span>
                                            </div>
                                            <div class="flex justify-between text-[#252525]">
                                                <span>2x Iced Caramel Latte (Less Sugar)</span>
                                                <span class="font-bold">Rp 64.000</span>
                                            </div>
                                            <div class="flex justify-between text-[#252525]">
                                                <span>1x Almond Croissant (Warmed)</span>
                                                <span class="font-bold">Rp 28.000</span>
                                            </div>
                                            <div class="pt-2 border-t border-[#EEEEEE] flex justify-between font-extrabold text-[#E87559]">
                                                <span>Total Meja 04</span>
                                                <span>Rp 92.000</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- AI Recommendation Box -->
                                    <div class="mt-4 p-2.5 rounded-xl bg-[#DDF1EC]/80 border border-[#43A58A]/30 text-[11px] text-[#252525]">
                                        <div class="font-bold text-[#43A58A] flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5 text-[#43A58A] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 001.5-.189m-1.5.189a6.01 6.01 0 01-1.5-.189m3.75 7.478a12.06 12.06 0 01-4.5 0m3.75 2.383a14.406 14.406 0 01-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 10-7.516 0c.85.493 1.509 1.333 1.509 2.316V18"/></svg>
                                            <span>AI Insight:</span>
                                        </div>
                                        <p class="mt-0.5 text-[#252525]/80">
                                            Susu Fresh Milk akan habis jam 17:30. Rekomendasi: auto-order 12L ke supplier sekarang.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- 3. Bottom Row: Inventory Tracker & Recent Orders -->
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                <!-- Inventory Level -->
                                <div class="bg-white p-5 rounded-2xl border border-[#EEEEEE] shadow-sm">
                                    <div class="flex items-center justify-between pb-3 border-b border-[#EEEEEE]">
                                        <h2 class="text-sm font-bold text-[#252525] flex items-center gap-1.5">
                                            <svg class="w-4 h-4 text-[#E87559]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                            <span>Inventory Status (Recipe Deduct)</span>
                                        </h2>
                                        <span class="text-xs text-[#777777]">Update: Real-time</span>
                                    </div>
                                    <div class="mt-3 space-y-3">
                                        <!-- Item 1 -->
                                        <div>
                                            <div class="flex justify-between text-xs font-medium">
                                                <span>Arabica Beans House Blend</span>
                                                <span class="text-[#E7A34A] font-bold">1.2 kg (Perlu Restock)</span>
                                            </div>
                                            <div class="w-full bg-gray-100 h-2 rounded-full mt-1.5 overflow-hidden">
                                                <div class="bg-[#E7A34A] h-full rounded-full" style="width: 25%"></div>
                                            </div>
                                        </div>
                                        <!-- Item 2 -->
                                        <div>
                                            <div class="flex justify-between text-xs font-medium">
                                                <span>Fresh Milk Pasteurisasi</span>
                                                <span class="text-[#43A58A] font-bold">24 Liter (Aman)</span>
                                            </div>
                                            <div class="w-full bg-gray-100 h-2 rounded-full mt-1.5 overflow-hidden">
                                                <div class="bg-[#43A58A] h-full rounded-full" style="width: 78%"></div>
                                            </div>
                                        </div>
                                        <!-- Item 3 -->
                                        <div>
                                            <div class="flex justify-between text-xs font-medium">
                                                <span>Caramel Syrup Monin</span>
                                                <span class="text-[#43A58A] font-bold">4 Botol (Aman)</span>
                                            </div>
                                            <div class="w-full bg-gray-100 h-2 rounded-full mt-1.5 overflow-hidden">
                                                <div class="bg-[#43A58A] h-full rounded-full" style="width: 65%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Recent Live Transactions -->
                                <div class="bg-white p-5 rounded-2xl border border-[#EEEEEE] shadow-sm">
                                    <div class="flex items-center justify-between pb-3 border-b border-[#EEEEEE]">
                                        <h2 class="text-sm font-bold text-[#252525] flex items-center gap-1.5">
                                            <svg class="w-4 h-4 text-[#43A58A]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                            <span>Recent Transactions</span>
                                        </h2>
                                        <span class="text-xs font-semibold text-[#E87559] hover:underline cursor-pointer">Lihat Semua</span>
                                    </div>
                                    <div class="mt-3 space-y-2.5">
                                        <!-- Order 1 -->
                                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-gray-50 text-xs">
                                            <div>
                                                <div class="font-bold text-[#252525]">Meja 08 • 2x Aren Latte, 1x Croissant</div>
                                                <div class="text-[11px] text-[#777777]">14:28 WIB • Metode: QRIS Statis</div>
                                            </div>
                                            <div class="text-right">
                                                <div class="font-extrabold text-[#252525]">Rp 94.000</div>
                                                <span class="text-[10px] font-semibold text-[#43A58A] bg-[#DDF1EC] px-1.5 py-0.5 rounded">LUNAS</span>
                                            </div>
                                        </div>
                                        <!-- Order 2 -->
                                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-gray-50 text-xs">
                                            <div>
                                                <div class="font-bold text-[#252525]">Takeaway #42 • 1x V60 Gayo, 1x Roll</div>
                                                <div class="text-[11px] text-[#777777]">14:25 WIB • Metode: Tunai</div>
                                            </div>
                                            <div class="text-right">
                                                <div class="font-extrabold text-[#252525]">Rp 62.000</div>
                                                <span class="text-[10px] font-semibold text-[#43A58A] bg-[#DDF1EC] px-1.5 py-0.5 rounded">LUNAS</span>
                                            </div>
                                        </div>
                                        <!-- Order 3 -->
                                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-gray-50 text-xs">
                                            <div>
                                                <div class="font-bold text-[#252525]">Meja 03 • 3x Beef Burger Combo</div>
                                                <div class="text-[11px] text-[#777777]">14:21 WIB • Metode: Debit BCA</div>
                                            </div>
                                            <div class="text-right">
                                                <div class="font-extrabold text-[#252525]">Rp 175.000</div>
                                                <span class="text-[10px] font-semibold text-[#43A58A] bg-[#DDF1EC] px-1.5 py-0.5 rounded">LUNAS</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </section>

        <!-- ======================================================== -->
        <!-- SECTION C: FEATURE SHOWCASE                              -->
        <!-- ======================================================== -->
        <section id="features" class="py-20 lg:py-28 bg-white border-t border-[#EEEEEE] relative isolate overflow-hidden">
            <!-- Subtle Coral Ambient Accent (Features) -->
            <div class="absolute -top-14 -right-14 w-[340px] h-[340px] rounded-full bg-coral-glow filter blur-[70px] pointer-events-none z-0 select-none"></div>

            <!-- Subtle Abstract Decorative Accents (Features) -->
            <div class="absolute -top-16 -right-16 sm:-right-24 w-[360px] sm:w-[460px] pointer-events-none z-0 animate-subtle-rotate select-none opacity-20 mix-blend-multiply mask-organic-soft filter blur-[6px]">
                <img src="{{ asset('images/abstract-coral-shape.jpg') }}" alt="" class="w-full h-auto object-contain rotate-45">
            </div>
            <div class="absolute -bottom-16 -left-20 sm:-left-28 w-[300px] sm:w-[400px] pointer-events-none z-0 animate-subtle-float-2 select-none opacity-15 mix-blend-multiply mask-organic-soft filter blur-[6px]">
                <img src="{{ asset('images/abstract-coral-shape.jpg') }}" alt="" class="w-full h-auto object-contain -scale-x-100 -rotate-12">
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">

                <!-- Section Header -->
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <span class="inline-block px-3.5 py-1 rounded-full bg-[#FCEDEA] text-[#E87559] text-xs font-bold tracking-wider uppercase mb-3">
                        Fitur Unggulan KasiVo
                    </span>
                    <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-[#252525] tracking-tight">
                        Everything You Need to Manage Your Business
                    </h2>
                    <p class="mt-4 text-base sm:text-lg text-[#777777]">
                        Ekosistem teknologi kasir dan manajemen bisnis terintegrasi yang dirancang khusus untuk mempercepat pertumbuhan usaha kuliner dan ritel Anda.
                    </p>
                </div>

                <!-- 4 Feature Cards Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

                    <!-- Card 1: Voice Transaction (Pastel Soft Blush) -->
                    <div class="bg-[#FCEDEA]/60 rounded-3xl p-8 lg:p-10 border border-[#E87559]/20 hover:border-[#E87559]/50 transition-all shadow-sm hover:shadow-card flex flex-col justify-between group">
                        <div>
                            <!-- Icon -->
                            <div class="w-14 h-14 rounded-2xl bg-white text-[#E87559] flex items-center justify-center shadow-sm border border-[#E87559]/20 mb-6 group-hover:scale-105 transition-transform">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/></svg>
                            </div>
                            <h3 class="text-2xl font-bold text-[#252525]">Voice Transaction</h3>
                            <p class="mt-3 text-base text-[#777777] leading-relaxed">
                                Catat transaksi menggunakan perintah suara secara praktis. Barista dan kasir tidak perlu repot mengetik saat jam sibuk; cukup bicara dan AI KasiVo otomatis memetakan pesanan ke sistem kasir.
                            </p>
                        </div>

                        <!-- Card Visual Mockup -->
                        <div class="mt-8 pt-6 border-t border-[#E87559]/15">
                            <div class="bg-white rounded-2xl p-4 shadow-sm border border-[#EEEEEE]">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-[#E87559] text-white flex items-center justify-center">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    </div>
                                    <div class="flex-1">
                                        <div class="text-xs font-bold text-[#252525]">"Es Kopi Gula Aren 2, Toast Cokelat 1"</div>
                                        <div class="text-[11px] text-[#43A58A] font-semibold">Terekam & Terkirim ke Printer Dapur (3 detik)</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 2: Receipt Scanning (Pastel Soft Mint) -->
                    <div class="bg-[#DDF1EC]/60 rounded-3xl p-8 lg:p-10 border border-[#43A58A]/20 hover:border-[#43A58A]/50 transition-all shadow-sm hover:shadow-card flex flex-col justify-between group">
                        <div>
                            <!-- Icon -->
                            <div class="w-14 h-14 rounded-2xl bg-white text-[#43A58A] flex items-center justify-center shadow-sm border border-[#43A58A]/20 mb-6 group-hover:scale-105 transition-transform">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </div>
                            <h3 class="text-2xl font-bold text-[#252525]">Receipt Scanning</h3>
                            <p class="mt-3 text-base text-[#777777] leading-relaxed">
                                Scan struk dan ekstrak informasi transaksi secara otomatis menggunakan AI. Digitalisasi faktur supplier, nota pasar, dan nota belanja dapur hanya dengan satu jepretan kamera.
                            </p>
                        </div>

                        <!-- Card Visual Mockup -->
                        <div class="mt-8 pt-6 border-t border-[#43A58A]/15">
                            <div class="bg-white rounded-2xl p-4 shadow-sm border border-[#EEEEEE]">
                                <div class="flex items-center justify-between text-xs">
                                    <div class="flex items-center gap-2">
                                        <span class="w-6 h-6 rounded bg-[#DDF1EC] text-[#43A58A] flex items-center justify-center font-bold text-[10px]">OCR</span>
                                        <span class="font-bold text-[#252525]">Nota Pasar: Susu, Gula, Sirup</span>
                                    </div>
                                    <span class="text-[#43A58A] font-bold">Rp 385.000</span>
                                </div>
                                <div class="mt-1 text-[11px] text-[#777777]">Otomatis tercatat ke Buku Pengeluaran & Stok Bahan Baku</div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 3: Inventory Management (Pastel Soft Sky Blue) -->
                    <div class="bg-[#E1F0F7]/60 rounded-3xl p-8 lg:p-10 border border-blue-200 hover:border-blue-400 transition-all shadow-sm hover:shadow-card flex flex-col justify-between group">
                        <div>
                            <!-- Icon -->
                            <div class="w-14 h-14 rounded-2xl bg-white text-[#252525] flex items-center justify-center shadow-sm border border-blue-200 mb-6 group-hover:scale-105 transition-transform">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            </div>
                            <h3 class="text-2xl font-bold text-[#252525]">Inventory Management</h3>
                            <p class="mt-3 text-base text-[#777777] leading-relaxed">
                                Pantau stok barang dan persediaan bisnis dengan lebih mudah. Dilengkapi integrasi resep (*bill of materials*): setiap kali menu terjual, bahan baku di gudang otomatis terpotong sesuai takaran.
                            </p>
                        </div>

                        <!-- Card Visual Mockup -->
                        <div class="mt-8 pt-6 border-t border-blue-200/40">
                            <div class="bg-white rounded-2xl p-4 shadow-sm border border-[#EEEEEE] space-y-2">
                                <div class="flex justify-between text-xs font-semibold text-[#252525]">
                                    <span>Sirup Karamel (1.5L)</span>
                                    <span class="text-[#E7A34A]">Stok Menipis (15%)</span>
                                </div>
                                <div class="w-full bg-gray-100 h-2 rounded-full overflow-hidden">
                                    <div class="bg-[#E7A34A] h-full" style="width: 15%"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 4: AI Business Insights (Soft Pastel Gradient) -->
                    <div class="bg-gradient-to-br from-[#FCEDEA]/70 via-white to-[#E1F0F7]/70 rounded-3xl p-8 lg:p-10 border border-[#E87559]/20 hover:border-[#E87559]/50 transition-all shadow-sm hover:shadow-card flex flex-col justify-between group">
                        <div>
                            <!-- Icon -->
                            <div class="w-14 h-14 rounded-2xl bg-white text-[#E87559] flex items-center justify-center shadow-sm border border-[#E87559]/20 mb-6 group-hover:scale-105 transition-transform">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            </div>
                            <h3 class="text-2xl font-bold text-[#252525]">AI Business Insights</h3>
                            <p class="mt-3 text-base text-[#777777] leading-relaxed">
                                Dapatkan insight dan ringkasan performa bisnis untuk membantu pengambilan keputusan. Pahami tren penjualan mingguan, jam tersibuk, menu paling profit, dan rekomendasi promo berbasis data.
                            </p>
                        </div>

                        <!-- Card Visual Mockup -->
                        <div class="mt-8 pt-6 border-t border-[#E87559]/15">
                            <div class="bg-white rounded-2xl p-4 shadow-sm border border-[#EEEEEE]">
                                <div class="flex items-center gap-1.5 text-xs font-bold text-[#E87559]">
                                    <svg class="w-4 h-4 text-[#E87559] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z"/></svg>
                                    <span>Rekomendasi AI Minggu Ini:</span>
                                </div>
                                <p class="text-xs text-[#252525] mt-1 font-medium">
                                    "Kombinasi Croissant + Kopi Susu menyumbang 42% laba kotor. Disarankan buat promo bundling khusus pagi hari."
                                </p>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </section>

        <!-- ======================================================== -->
        <!-- SECTION D: WHY CHOOSE KASIVO (Split Layout)              -->
        <!-- ======================================================== -->
        <section id="why-us" class="py-20 lg:py-28 bg-[#FCEDEA]/25 border-t border-[#EEEEEE] relative overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 items-center">

                    <!-- Left: Benefits Narrative & List (7 cols) -->
                    <div class="lg:col-span-7 space-y-6">
                        <span class="inline-block px-3.5 py-1 rounded-full bg-[#FCEDEA] text-[#E87559] text-xs font-bold tracking-wider uppercase">
                            Mengapa KasiVo?
                        </span>

                        <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-[#252525] tracking-tight leading-tight">
                            Why Businesses Choose KasiVo
                        </h2>

                        <p class="text-base sm:text-lg text-[#777777] leading-relaxed">
                            Dirancang dari masalah nyata pelaku bisnis F&B yang lelah dengan sistem kasir lama yang ribet, perhitungan stok manual yang sering bocor, dan laporan keuangan yang membingungkan.
                        </p>

                        <!-- 4 Core Benefits -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-4">

                            <!-- Benefit 1 -->
                            <div class="flex items-start gap-3.5">
                                <div class="w-10 h-10 rounded-xl bg-white text-[#E87559] border border-[#E87559]/20 flex items-center justify-center flex-shrink-0 shadow-sm mt-0.5">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <div>
                                    <h3 class="text-base font-bold text-[#252525]">Save Time with Automation</h3>
                                    <p class="text-xs sm:text-sm text-[#777777] mt-1 leading-normal">
                                        Hemat 3–4 jam setiap hari dengan otomatisasi rekap kasir, scan struk belanja, dan laporan stok harian.
                                    </p>
                                </div>
                            </div>

                            <!-- Benefit 2 -->
                            <div class="flex items-start gap-3.5">
                                <div class="w-10 h-10 rounded-xl bg-white text-[#43A58A] border border-[#43A58A]/20 flex items-center justify-center flex-shrink-0 shadow-sm mt-0.5">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <div>
                                    <h3 class="text-base font-bold text-[#252525]">Simplify Daily Transactions</h3>
                                    <p class="text-xs sm:text-sm text-[#777777] mt-1 leading-normal">
                                        Antarmuka kasir super responsif. Terima pesanan via suara, scan nota, atau klik cepat tanpa antrean panjang.
                                    </p>
                                </div>
                            </div>

                            <!-- Benefit 3 -->
                            <div class="flex items-start gap-3.5">
                                <div class="w-10 h-10 rounded-xl bg-white text-[#252525] border border-blue-200 flex items-center justify-center flex-shrink-0 shadow-sm mt-0.5">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                </div>
                                <div>
                                    <h3 class="text-base font-bold text-[#252525]">Monitor Business Performance</h3>
                                    <p class="text-xs sm:text-sm text-[#777777] mt-1 leading-normal">
                                        Pantau penjualan dan omset cabang secara real-time dari smartphone Anda kapan pun dan di mana pun.
                                    </p>
                                </div>
                            </div>

                            <!-- Benefit 4 -->
                            <div class="flex items-start gap-3.5">
                                <div class="w-10 h-10 rounded-xl bg-white text-[#E7A34A] border border-[#E7A34A]/20 flex items-center justify-center flex-shrink-0 shadow-sm mt-0.5">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                </div>
                                <div>
                                    <h3 class="text-base font-bold text-[#252525]">Make Smarter Decisions</h3>
                                    <p class="text-xs sm:text-sm text-[#777777] mt-1 leading-normal">
                                        Prediksi lonjakan pelanggan, hindari stok basi, dan maksimalkan margin keuntungan dengan rekomendasi AI.
                                    </p>
                                </div>
                            </div>

                        </div>

                        <!-- Mini CTA in section -->
                        <div class="pt-4">
                            <a href="{{ Route::has('register') ? route('register') : url('/register') }}"
                               class="inline-flex items-center gap-2 text-sm font-bold text-[#E87559] hover:text-[#D66448] group">
                                <span>Coba Gratis KasiVo Selama 14 Hari</span>
                                <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                            </a>
                        </div>
                    </div>

                    <!-- Right: Visual Benefit Showcase Card (5 cols) -->
                    <div class="lg:col-span-5">
                        <div class="bg-white rounded-3xl border border-[#EEEEEE] p-6 sm:p-8 shadow-card relative">
                            <!-- Header badge -->
                            <div class="flex items-center justify-between pb-4 border-b border-[#EEEEEE]">
                                <div>
                                    <span class="text-xs font-bold text-[#252525]">Dampak Terukur Penggunaan KasiVo</span>
                                    <p class="text-[11px] text-[#777777]">Rata-rata hasil pada 30 hari pertama</p>
                                </div>
                                <span class="px-2.5 py-1 rounded-full bg-[#DDF1EC] text-[#43A58A] text-xs font-bold">Terbukti</span>
                            </div>

                            <!-- Metrics Comparison -->
                            <div class="mt-6 space-y-4">
                                <!-- Metric 1 -->
                                <div class="p-4 rounded-2xl bg-[#FCEDEA]/50 border border-[#E87559]/20 flex items-center justify-between">
                                    <div>
                                        <div class="text-xs font-semibold text-[#777777]">Kecepatan Input Pesanan</div>
                                        <div class="text-sm font-extrabold text-[#252525] mt-0.5">Dari 90 detik → 30 detik</div>
                                    </div>
                                    <div class="text-2xl font-extrabold text-[#E87559]">3x</div>
                                </div>

                                <!-- Metric 2 -->
                                <div class="p-4 rounded-2xl bg-[#DDF1EC]/50 border border-[#43A58A]/20 flex items-center justify-between">
                                    <div>
                                        <div class="text-xs font-semibold text-[#777777]">Akurasi Bahan Baku & Resep</div>
                                        <div class="text-sm font-extrabold text-[#252525] mt-0.5">Cegah kebocoran stok dapur</div>
                                    </div>
                                    <div class="text-2xl font-extrabold text-[#43A58A]">99.4%</div>
                                </div>

                                <!-- Metric 3 -->
                                <div class="p-4 rounded-2xl bg-[#E1F0F7]/50 border border-blue-200 flex items-center justify-between">
                                    <div>
                                        <div class="text-xs font-semibold text-[#777777]">Efisiensi Margin Keuntungan</div>
                                        <div class="text-sm font-extrabold text-[#252525] mt-0.5">Optimasi menu bundling & HPP</div>
                                    </div>
                                    <div class="text-2xl font-extrabold text-[#252525]">+22%</div>
                                </div>
                            </div>

                            <!-- Testimonial Quote Bubble -->
                            <div class="mt-6 p-4 rounded-2xl bg-gray-50 border border-[#EEEEEE] text-xs text-[#777777] italic">
                                "Kami tidak lagi pusing saat akhir bulan menghitung stok fisik. Selisih bahan baku hampir 0% sejak pakai KasiVo."
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </section>

        <!-- ======================================================== -->
        <!-- SECTION E: HOW IT WORKS                                  -->
        <!-- ======================================================== -->
        <section id="how-it-works" class="py-20 lg:py-28 bg-white border-t border-[#EEEEEE] relative">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                <!-- Section Header -->
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <span class="inline-block px-3.5 py-1 rounded-full bg-[#E1F0F7] text-[#252525] text-xs font-bold tracking-wider uppercase mb-3">
                        Alur Kerja Praktis
                    </span>
                    <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-[#252525] tracking-tight">
                        Your Business Workflow, Simplified
                    </h2>
                    <p class="mt-4 text-base sm:text-lg text-[#777777]">
                        Tiga langkah sederhana untuk mentransformasi operasional kasir dan manajemen usaha F&B Anda.
                    </p>
                </div>

                <!-- 3 Step Workflow Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 relative">

                    <!-- Step 1 -->
                    <div class="bg-white rounded-3xl p-8 border border-[#EEEEEE] hover:border-[#E87559]/40 shadow-sm hover:shadow-card transition-all flex flex-col justify-between group">
                        <div>
                            <div class="flex items-center justify-between mb-6">
                                <span class="text-3xl font-extrabold text-[#E87559]">01</span>
                                <span class="px-3 py-1 rounded-full bg-[#FCEDEA] text-[#E87559] text-xs font-bold">Input</span>
                            </div>
                            <h3 class="text-xl font-bold text-[#252525]">Record Transactions</h3>
                            <p class="mt-3 text-sm text-[#777777] leading-relaxed">
                                Catat transaksi melalui suara, teks, atau scan struk. Kasir dapat melayani pelanggan dengan cepat tanpa antrean panjang bahkan saat rush hour.
                            </p>
                        </div>
                        <div class="mt-8 pt-4 border-t border-[#EEEEEE] flex items-center gap-2 text-xs font-semibold text-[#777777]">
                            <svg class="w-4 h-4 text-[#E87559] shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                            <span>Voice POS • Scan Struk • Layar Sentuh</span>
                        </div>
                    </div>

                    <!-- Step 2 -->
                    <div class="bg-white rounded-3xl p-8 border border-[#EEEEEE] hover:border-[#43A58A]/40 shadow-sm hover:shadow-card transition-all flex flex-col justify-between group">
                        <div>
                            <div class="flex items-center justify-between mb-6">
                                <span class="text-3xl font-extrabold text-[#43A58A]">02</span>
                                <span class="px-3 py-1 rounded-full bg-[#DDF1EC] text-[#43A58A] text-xs font-bold">Operasi</span>
                            </div>
                            <h3 class="text-xl font-bold text-[#252525]">Manage Your Business</h3>
                            <p class="mt-3 text-sm text-[#777777] leading-relaxed">
                                Kelola persediaan dan pantau transaksi bisnis. Setiap pesanan kasir otomatis memotong stok bahan baku dan mencatat arus kas secara instan.
                            </p>
                        </div>
                        <div class="mt-8 pt-4 border-t border-[#EEEEEE] flex items-center gap-2 text-xs font-semibold text-[#777777]">
                            <svg class="w-4 h-4 text-[#43A58A] shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                            <span>Auto-Deduct Stok • Tiket Dapur</span>
                        </div>
                    </div>

                    <!-- Step 3 -->
                    <div class="bg-white rounded-3xl p-8 border border-[#EEEEEE] hover:border-blue-400 shadow-sm hover:shadow-card transition-all flex flex-col justify-between group">
                        <div>
                            <div class="flex items-center justify-between mb-6">
                                <span class="text-3xl font-extrabold text-[#252525]">03</span>
                                <span class="px-3 py-1 rounded-full bg-[#E1F0F7] text-[#252525] text-xs font-bold">Pertumbuhan</span>
                            </div>
                            <h3 class="text-xl font-bold text-[#252525]">Analyze & Improve</h3>
                            <p class="mt-3 text-sm text-[#777777] leading-relaxed">
                                Pahami laporan dan insight bisnis menggunakan AI. Dapatkan rekomendasi menu paling profit, jam paling ramai, dan strategi promo berbasis data.
                            </p>
                        </div>
                        <div class="mt-8 pt-4 border-t border-[#EEEEEE] flex items-center gap-2 text-xs font-semibold text-[#777777]">
                            <svg class="w-4 h-4 text-[#252525] shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                            <span>Rekomendasi AI • Prediksi Penjualan</span>
                        </div>
                    </div>

                </div>

            </div>
        </section>

        <!-- ======================================================== -->
        <!-- SECTION F: TESTIMONIAL SECTION                           -->
        <!-- ======================================================== -->
        <section id="testimonials" class="py-20 lg:py-28 bg-[#FCEDEA]/20 border-t border-[#EEEEEE] relative">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                <!-- Section Header -->
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <span class="inline-block px-3.5 py-1 rounded-full bg-[#FCEDEA] text-[#E87559] text-xs font-bold tracking-wider uppercase mb-3">
                        Kisah Sukses UMKM
                    </span>
                    <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-[#252525] tracking-tight">
                        Trusted by Growing Businesses
                    </h2>
                    <p class="mt-4 text-base sm:text-lg text-[#777777]">
                        Kisah nyata dari pemilik coffee shop, resto, dan usaha kuliner yang bertumbuh lebih cepat bersama KasiVo.
                    </p>
                    <p class="mt-2 text-xs text-[#777777]/70 italic">
                        *Simulasi pengalaman pengguna uji coba sistem KasiVo
                    </p>
                </div>

                <!-- 3 Testimonial Cards Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

                    <!-- Card 1 -->
                    <div class="bg-white rounded-3xl p-8 border border-[#EEEEEE] shadow-sm hover:shadow-card transition flex flex-col justify-between">
                        <div>
                            <!-- Star Rating -->
                            <div class="flex items-center gap-1 text-[#E7A34A] mb-4">
                                @for($i = 0; $i < 5; $i++)
                                <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                @endfor
                            </div>
                            <p class="text-sm sm:text-base text-[#252525] leading-relaxed italic">
                                "Fitur Voice Transaction-nya sangat menghemat waktu! Barista kami gak perlu bolak-balik klik layar kasir saat jam makan siang yang ramai. Cukup sebut pesanan, tiket dapur langsung tercetak."
                            </p>
                        </div>

                        <div class="mt-8 pt-6 border-t border-[#EEEEEE] flex items-center gap-3">
                            <div class="w-11 h-11 rounded-full bg-[#FCEDEA] text-[#E87559] font-bold text-sm flex items-center justify-center flex-shrink-0">
                                RP
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-[#252525]">Reza Pratama</h3>
                                <p class="text-xs text-[#777777]">Owner, Titik Temu Coffee</p>
                            </div>
                        </div>
                    </div>

                    <!-- Card 2 -->
                    <div class="bg-white rounded-3xl p-8 border border-[#EEEEEE] shadow-sm hover:shadow-card transition flex flex-col justify-between">
                        <div>
                            <!-- Star Rating -->
                            <div class="flex items-center gap-1 text-[#E7A34A] mb-4">
                                @for($i = 0; $i < 5; $i++)
                                <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                @endfor
                            </div>
                            <p class="text-sm sm:text-base text-[#252525] leading-relaxed italic">
                                "Scan struk belanjaan pasar langsung masuk ke sistem inventory otomatis. Selisih bahan baku berkurang drastis dan kami bisa kontrol HPP setiap menu dengan jauh lebih akurat."
                            </p>
                        </div>

                        <div class="mt-8 pt-6 border-t border-[#EEEEEE] flex items-center gap-3">
                            <div class="w-11 h-11 rounded-full bg-[#DDF1EC] text-[#43A58A] font-bold text-sm flex items-center justify-center flex-shrink-0">
                                NS
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-[#252525]">Nadia Salsabila</h3>
                                <p class="text-xs text-[#777777]">Founder, Dapur Sedap Catering</p>
                            </div>
                        </div>
                    </div>

                    <!-- Card 3 -->
                    <div class="bg-white rounded-3xl p-8 border border-[#EEEEEE] shadow-sm hover:shadow-card transition flex flex-col justify-between">
                        <div>
                            <!-- Star Rating -->
                            <div class="flex items-center gap-1 text-[#E7A34A] mb-4">
                                @for($i = 0; $i < 5; $i++)
                                <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                @endfor
                            </div>
                            <p class="text-sm sm:text-base text-[#252525] leading-relaxed italic">
                                "AI Business Insights-nya sangat membantu melihat jam paling sibuk dan menu mana yang paling menghasilkan profit. Keputusan menambah stok pastry pagi hari menaikkan omset 24%."
                            </p>
                        </div>

                        <div class="mt-8 pt-6 border-t border-[#EEEEEE] flex items-center gap-3">
                            <div class="w-11 h-11 rounded-full bg-[#E1F0F7] text-[#252525] font-bold text-sm flex items-center justify-center flex-shrink-0">
                                HW
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-[#252525]">Hendra Wijaya</h3>
                                <p class="text-xs text-[#777777]">Co-Founder, Artisan Bakery & Cafe</p>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </section>

        <!-- ======================================================== -->
        <!-- SECTION G: FINAL CTA                                     -->
        <!-- ======================================================== -->
        <section class="py-20 lg:py-28 bg-white relative">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                <!-- Card Container with Soft Pastel Gradient -->
                <div class="bg-gradient-to-br from-[#FCEDEA] via-white to-[#E1F0F7] rounded-3xl border border-[#EEEEEE] p-8 sm:p-12 lg:p-16 text-center shadow-soft relative isolate overflow-hidden">

                    <!-- Subtle Coral Accent Glow -->
                    <div class="absolute -top-16 -right-16 w-[360px] h-[360px] rounded-full bg-coral-glow filter blur-[60px] pointer-events-none z-0 select-none"></div>

                    <!-- Subtle Abstract Organic Accents (CTA) -->
                    <div class="absolute -top-20 -right-16 sm:-right-24 w-[380px] sm:w-[480px] pointer-events-none z-0 animate-subtle-float-1 opacity-25 mix-blend-multiply mask-organic-soft filter blur-[6px] select-none">
                        <img src="{{ asset('images/abstract-coral-shape.jpg') }}" alt="" class="w-full h-auto object-contain rotate-[35deg]">
                    </div>
                    <div class="absolute -bottom-24 -left-16 sm:-left-24 w-[320px] sm:w-[420px] pointer-events-none z-0 animate-subtle-float-2 opacity-20 mix-blend-multiply mask-organic-soft filter blur-[6px] select-none">
                        <img src="{{ asset('images/abstract-coral-shape.jpg') }}" alt="" class="w-full h-auto object-contain -scale-x-100 rotate-[125deg]">
                    </div>

                    <!-- Decorative blur orbs -->
                    <div class="absolute -top-24 -left-24 w-64 h-64 bg-[#E87559]/10 rounded-full blur-3xl pointer-events-none -z-10"></div>
                    <div class="absolute -bottom-24 -right-24 w-64 h-64 bg-[#43A58A]/10 rounded-full blur-3xl pointer-events-none -z-10"></div>

                    <div class="max-w-3xl mx-auto relative z-10">
                        <span class="inline-block px-4 py-1.5 rounded-full bg-white text-[#E87559] text-xs font-bold tracking-wider uppercase mb-4 shadow-sm border border-[#E87559]/20">
                            Mulai Hari Ini
                        </span>

                        <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-[#252525] tracking-tight leading-tight">
                            Ready to Grow Your Business with KasiVo?
                        </h2>

                        <p class="mt-4 text-base sm:text-lg text-[#777777] max-w-xl mx-auto leading-relaxed">
                            Mulai kelola transaksi, stok, dan bisnis dengan cara yang lebih praktis. Uji coba gratis tanpa kartu kredit.
                        </p>

                        <!-- CTA Actions -->
                        <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-4">
                            <a href="{{ Route::has('register') ? route('register') : url('/register') }}"
                               class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-4 rounded-2xl bg-[#E87559] hover:bg-[#D66448] text-white font-semibold text-base shadow-lg shadow-[#E87559]/30 hover:shadow-xl hover:shadow-[#E87559]/40 transition-all transform active:scale-95">
                                <span>Get Started Free</span>
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </a>
                            <a href="#hero"
                               class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-4 rounded-2xl bg-white hover:bg-gray-50 text-[#252525] font-semibold text-base border border-[#EEEEEE] shadow-sm transition-all">
                                <span>Lihat Demo Kasir</span>
                            </a>
                        </div>

                        <!-- Highlights Pills -->
                        <div class="mt-10 flex flex-wrap items-center justify-center gap-4 sm:gap-6 text-xs text-[#777777] font-medium">
                            <span class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-[#43A58A] shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span>Uji Coba 14 Hari</span>
                            </span>
                            <span class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-[#43A58A] shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span>Tanpa Kartu Kredit</span>
                            </span>
                            <span class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-[#43A58A] shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span>Setup Cepat 5 Menit</span>
                            </span>
                            <span class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-[#43A58A] shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span>Bantuan Tim Teknis 24/7</span>
                            </span>
                        </div>
                    </div>

                </div>

            </div>
        </section>
    </main>

    <!-- ======================================================== -->
    <!-- SECTION H: FOOTER                                        -->
    <!-- ======================================================== -->
    <footer class="bg-white border-t border-[#EEEEEE] pt-16 pb-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-10 lg:gap-8 pb-12 border-b border-[#EEEEEE]">

                <!-- Col 1: Brand Info (2 cols on lg) -->
                <div class="lg:col-span-2 space-y-4">
                    <x-kasivo-logo size="md" :showTagline="true" />
                    <p class="text-sm text-[#777777] leading-relaxed max-w-sm mt-3">
                        KasiVo adalah platform kasir cerdas dan manajemen bisnis berbasis AI untuk membantu pengusaha kuliner dan UMKM mengelola pesanan, persediaan, dan strategi bisnis.
                    </p>

                </div>

                <!-- Col 2: Product Navigation -->
                <div class="space-y-3">
                    <h3 class="text-xs font-bold text-[#252525] uppercase tracking-wider">Product</h3>
                    <ul class="space-y-2 text-sm text-[#777777]">
                        <li><a href="#features" class="hover:text-[#E87559] transition-colors">Voice Transaction</a></li>
                        <li><a href="#features" class="hover:text-[#E87559] transition-colors">Receipt Scanning</a></li>
                        <li><a href="#features" class="hover:text-[#E87559] transition-colors">Smart Inventory</a></li>
                        <li><a href="#features" class="hover:text-[#E87559] transition-colors">AI Business Insights</a></li>
                        <li><a href="#hero" class="hover:text-[#E87559] transition-colors">F&B Cashier POS</a></li>
                    </ul>
                </div>

                <!-- Col 3: Company Navigation -->
                <div class="space-y-3">
                    <h3 class="text-xs font-bold text-[#252525] uppercase tracking-wider">Company</h3>
                    <ul class="space-y-2 text-sm text-[#777777]">
                        <li><a href="#why-us" class="hover:text-[#E87559] transition-colors">About KasiVo</a></li>
                        <li><a href="#how-it-works" class="hover:text-[#E87559] transition-colors">How It Works</a></li>
                        <li><a href="#testimonials" class="hover:text-[#E87559] transition-colors">Kisah Sukses</a></li>
                        <li><a href="#hero" class="hover:text-[#E87559] transition-colors">Karir</a></li>
                        <li><a href="#hero" class="hover:text-[#E87559] transition-colors">Kontak Tim</a></li>
                    </ul>
                </div>

                <!-- Col 4: Contact & Support -->
                <div class="space-y-3">
                    <h3 class="text-xs font-bold text-[#252525] uppercase tracking-wider">Contact & Support</h3>
                    <ul class="space-y-2 text-sm text-[#777777]">
                        <li>Email: <span class="font-medium text-[#252525]">niskalaateam@gmail.com</span></li>
                        <li>WhatsApp: <span class="font-medium text-[#252525]">+62 877-2926-2978</span></li>
                        <li>Lokasi: Surabaya timur, Indonesia</li>
                        <li class="pt-2">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-[#DDF1EC] text-[#43A58A] text-xs font-semibold">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#43A58A] animate-pulse"></span>
                                Semua Sistem Normal
                            </span>
                        </li>
                    </ul>
                </div>

            </div>

            <!-- Bottom Copyright & Legal -->
            <div class="pt-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-[#777777]">
                <p>© {{ date('Y') }} KasiVo Technologies. All rights reserved.</p>
                <div class="flex items-center gap-6">
                    <a href="#" class="hover:text-[#252525] transition-colors">Privacy Policy</a>
                    <a href="#" class="hover:text-[#252525] transition-colors">Terms of Service</a>
                    <a href="#" class="hover:text-[#252525] transition-colors">Security</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Interactive Scripts for Mobile Menu Toggle -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const btn = document.getElementById('mobile-menu-btn');
            const menu = document.getElementById('mobile-menu');
            const menuIcon = document.getElementById('menu-icon');
            const closeIcon = document.getElementById('close-icon');

            if (btn && menu) {
                btn.addEventListener('click', function () {
                    const isHidden = menu.classList.contains('hidden');
                    if (isHidden) {
                        menu.classList.remove('hidden');
                        menuIcon.classList.add('hidden');
                        closeIcon.classList.remove('hidden');
                    } else {
                        menu.classList.add('hidden');
                        menuIcon.classList.remove('hidden');
                        closeIcon.classList.add('hidden');
                    }
                });

                // Auto close mobile menu when clicking nav links
                const navLinks = document.querySelectorAll('.mobile-nav-link');
                navLinks.forEach(link => {
                    link.addEventListener('click', () => {
                        menu.classList.add('hidden');
                        menuIcon.classList.remove('hidden');
                        closeIcon.classList.add('hidden');
                    });
                });
            }
        });
    </script>
</body>
</html>
