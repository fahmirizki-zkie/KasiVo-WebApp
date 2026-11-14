<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

@php
    $currentRoute = request()->route()?->getName();
    
    // Page Title Resolver
    $pageTitle = match($currentRoute) {
        'dashboard' => 'Dashboard',
        'transactions.index' => 'Riwayat Transaksi',
        'transactions.create' => 'Pilih Jenis Pencatatan',
        'transactions.expense' => 'Catat Pengeluaran',
        'transactions.photo' => 'Catat Pengeluaran',
        'transactions.text' => 'Catat Pengeluaran',
        'transactions.income' => 'Catat Penjualan',
        'restock' => 'Saran Restock Bahan',
        'business-profile' => 'Profil Usaha & Ekspor',
        'profile' => 'Pengaturan Profil Akun',
        'onboarding' => 'Profil Usaha',
        default => 'Dashboard'
    };

    // Hierarchical Functional Breadcrumbs Definition
    // Parent crumbs have a valid route url (native navigation for reliable 1-click response), current active crumb has null url
    $breadcrumbs = match($currentRoute) {
        'dashboard' => [
            ['label' => 'Dashboard', 'url' => null],
        ],
        'transactions.index' => [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Riwayat Transaksi', 'url' => null],
        ],
        'transactions.create' => [
            ['label' => 'Transaksi', 'url' => route('transactions.index')],
            ['label' => 'Pilih Jenis Pencatatan', 'url' => null],
        ],
        'transactions.income' => [
            ['label' => 'Transaksi', 'url' => route('transactions.index')],
            ['label' => 'Catat Penjualan', 'url' => null],
        ],
        'transactions.expense' => [
            ['label' => 'Transaksi', 'url' => route('transactions.index')],
            ['label' => 'Catat Pengeluaran', 'url' => null],
        ],
        'transactions.photo' => [
            ['label' => 'Transaksi', 'url' => route('transactions.index')],
            ['label' => 'Pilih Jenis', 'url' => route('transactions.create')],
            ['label' => 'Foto Nota', 'url' => null],
        ],
        'transactions.text' => [
            ['label' => 'Transaksi', 'url' => route('transactions.index')],
            ['label' => 'Pilih Jenis', 'url' => route('transactions.create')],
            ['label' => 'Teks Bebas', 'url' => null],
        ],
        'restock' => [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Saran Restock Bahan', 'url' => null],
        ],
        'business-profile' => [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Profil Usaha & Ekspor', 'url' => null],
        ],
        'profile' => [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Pengaturan Akun', 'url' => null],
        ],
        'onboarding' => [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Profil Usaha', 'url' => null],
        ],
        default => [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => $pageTitle, 'url' => null],
        ]
    };

    $userName = auth()->user()->name ?? 'Pengguna KasiVo';
    $userEmail = auth()->user()->email ?? 'user@kasivo.id';
    $nameParts = array_filter(explode(' ', trim($userName)));
    $userInitials = '';
    foreach (array_slice($nameParts, 0, 2) as $part) {
        $userInitials .= strtoupper(substr($part, 0, 1));
    }
    if (empty($userInitials)) $userInitials = 'KV';
@endphp

<div x-data="{ 
        mobileDrawerOpen: false, 
        transactionsExpanded: {{ request()->routeIs('transactions.*') ? 'true' : 'false' }} 
     }" 
     @keydown.escape.window="mobileDrawerOpen = false">

    <!-- ======================================================== -->
    <!-- 1. PERMANENT DESKTOP SIDEBAR (Visible on lg+, Hidden on Mobile) -->
    <!-- ======================================================== -->
    <aside class="hidden lg:fixed lg:inset-y-0 lg:left-0 lg:z-40 lg:w-64 lg:flex lg:flex-col bg-white border-r border-[#EEEEEE]">
        
        <!-- Brand Header Section -->
        <div class="h-20 flex items-center px-6 border-b border-[#EEEEEE]/80">
            <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center gap-2 group">
                <x-kasivo-logo size="md" :showTagline="false" />
            </a>
        </div>

        <!-- Store / Branch Quick Info Pill -->
        <div class="px-4 pt-4 pb-2">
            <div class="px-3.5 py-2.5 rounded-2xl bg-gray-50 border border-[#EEEEEE] flex items-center justify-between">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-7 h-7 rounded-lg bg-[#DDF1EC] text-[#43A58A] flex items-center justify-center font-bold text-xs shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <div class="truncate">
                        <div class="text-xs font-bold text-[#252525] truncate">KasiVo POS F&B</div>
                        <div class="text-[10px] text-[#43A58A] font-semibold flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#43A58A] animate-pulse"></span>
                            <span>Shift Aktif</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Vertical Navigation Links -->
        <nav class="flex-1 overflow-y-auto px-4 py-3 space-y-1">
            <div class="px-3 pb-2 text-[10px] font-bold uppercase tracking-wider text-[#999999]">
                Menu Utama
            </div>

            <!-- 1. Dashboard -->
            <a href="{{ route('dashboard') }}" 
               wire:navigate
               class="group flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm transition-all {{ request()->routeIs('dashboard') ? 'bg-[#FCEDEA] text-[#E87559] font-bold shadow-sm' : 'text-[#64748B] hover:text-[#252525] hover:bg-gray-50 font-medium' }}">
                <div class="shrink-0 transition-transform group-hover:scale-110 {{ request()->routeIs('dashboard') ? 'text-[#E87559]' : 'text-[#777777]' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                    </svg>
                </div>
                <span>Dashboard</span>
            </a>

            <!-- 2. Transaksi (With Expandable Sub-items) -->
            <div class="space-y-1">
                <div class="flex items-center justify-between rounded-xl transition-all {{ request()->routeIs('transactions.*') ? 'bg-[#FCEDEA] text-[#E87559] font-bold shadow-sm' : 'text-[#64748B] hover:text-[#252525] hover:bg-gray-50 font-medium' }}">
                    <a href="{{ route('transactions.index') }}" 
                       wire:navigate
                       class="flex-1 flex items-center gap-3 px-3.5 py-2.5 text-sm">
                        <div class="shrink-0 {{ request()->routeIs('transactions.*') ? 'text-[#E87559]' : 'text-[#777777]' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z"/>
                            </svg>
                        </div>
                        <span>Transaksi</span>
                    </a>
                    <button type="button" 
                            @click="transactionsExpanded = !transactionsExpanded"
                            aria-label="Toggle sub-transaksi" 
                            class="px-3 py-2.5 text-inherit focus:outline-none">
                        <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="{ 'rotate-180': transactionsExpanded }" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                </div>

                <!-- Sub-menu Transaksi -->
                <div x-show="transactionsExpanded" 
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 -translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="pl-9 pr-2 space-y-1 pt-1 pb-1">
                    <a href="{{ route('transactions.index') }}" 
                       wire:navigate
                       class="block px-3 py-1.5 rounded-lg text-xs transition-colors {{ request()->routeIs('transactions.index') ? 'text-[#E87559] font-bold bg-[#FCEDEA]/50' : 'text-[#777777] hover:text-[#252525] hover:bg-gray-50' }}">
                        Riwayat Transaksi
                    </a>
                    <!-- Catat Pengeluaran (Multi-Moda AI) -->
                    <a href="{{ route('transactions.expense') }}"
                       wire:navigate
                       class="flex items-center justify-between px-3 py-1.5 rounded-lg text-xs transition-colors {{ request()->routeIs('transactions.expense', 'transactions.photo', 'transactions.text') ? 'text-[#E87559] font-bold bg-[#FCEDEA]/50' : 'text-[#777777] hover:text-[#252525] hover:bg-gray-50' }}">
                        <span>Catat Pengeluaran</span>
                        <span class="text-[9px] font-semibold px-1.5 py-0.2 rounded bg-amber-100 text-amber-700">AI</span>
                    </a>
                    <a href="{{ route('transactions.income') }}" 
                       wire:navigate
                       class="flex items-center justify-between px-3 py-1.5 rounded-lg text-xs transition-colors {{ request()->routeIs('transactions.income') ? 'text-[#E87559] font-bold bg-[#FCEDEA]/50' : 'text-[#777777] hover:text-[#252525] hover:bg-gray-50' }}">
                        <span>Catat Penjualan</span>
                        <span class="text-[9px] font-semibold px-1.5 py-0.2 rounded bg-emerald-100 text-emerald-700">POS</span>
                    </a>
                </div>
            </div>

            <!-- 3. Restock Bahan -->
            <a href="{{ route('restock') }}" 
               wire:navigate
               class="group flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm transition-all {{ request()->routeIs('restock') ? 'bg-[#FCEDEA] text-[#E87559] font-bold shadow-sm' : 'text-[#64748B] hover:text-[#252525] hover:bg-gray-50 font-medium' }}">
                <div class="flex items-center gap-3">
                    <div class="shrink-0 transition-transform group-hover:scale-110 {{ request()->routeIs('restock') ? 'text-[#E87559]' : 'text-[#777777]' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <span>Restock</span>
                </div>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ request()->routeIs('restock') ? 'bg-[#E87559] text-white' : 'bg-[#FCEDEA] text-[#E87559]' }}">
                    2 Perlu
                </span>
            </a>

            <!-- 4. Profil Usaha -->
            <a href="{{ route('business-profile') }}" 
               wire:navigate
               class="group flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm transition-all {{ request()->routeIs('business-profile') ? 'bg-[#FCEDEA] text-[#E87559] font-bold shadow-sm' : 'text-[#64748B] hover:text-[#252525] hover:bg-gray-50 font-medium' }}">
                <div class="flex items-center gap-3">
                    <div class="shrink-0 transition-transform group-hover:scale-110 {{ request()->routeIs('business-profile') ? 'text-[#E87559]' : 'text-[#777777]' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <span>Profil Usaha</span>
                </div>
                <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-[#DDF1EC] text-[#43A58A]">
                    PDF
                </span>
            </a>

            <!-- Settings / Profil Akun Section -->
            <div class="pt-5 px-3 pb-2 text-[10px] font-bold uppercase tracking-wider text-[#999999]">
                Pengaturan
            </div>

            <a href="{{ route('profile') }}" 
               wire:navigate
               class="group flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm transition-all {{ request()->routeIs('profile') ? 'bg-[#FCEDEA] text-[#E87559] font-bold shadow-sm' : 'text-[#64748B] hover:text-[#252525] hover:bg-gray-50 font-medium' }}">
                <div class="shrink-0 transition-transform group-hover:scale-110 {{ request()->routeIs('profile') ? 'text-[#E87559]' : 'text-[#777777]' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <span>Akun Saya</span>
            </a>
        </nav>

        <!-- Bottom User Card & Quick Logout -->
        <div class="p-4 border-t border-[#EEEEEE]">
            <div class="flex items-center justify-between p-2 rounded-xl bg-gray-50 border border-[#EEEEEE]">
                <a href="{{ route('profile') }}" wire:navigate class="flex items-center gap-2.5 min-w-0 flex-1 hover:opacity-80 transition">
                    <div class="w-9 h-9 rounded-full bg-[#E87559] text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-sm">
                        {{ $userInitials }}
                    </div>
                    <div class="truncate text-left">
                        <div class="text-xs font-bold text-[#252525] truncate">{{ $userName }}</div>
                        <div class="text-[11px] text-[#777777] truncate">{{ $userEmail }}</div>
                    </div>
                </a>

                <button type="button" 
                        wire:click="logout" 
                        title="Log Out dari KasiVo"
                        class="p-2 text-[#777777] hover:text-[#E87559] hover:bg-[#FCEDEA] rounded-lg transition-colors focus:outline-none">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </button>
            </div>
        </div>
    </aside>

    <!-- ======================================================== -->
    <!-- 2. TOPBAR HEADER (Sticky Top, Spans lg:pl-64 on Desktop) -->
    <!-- ======================================================== -->
    <header class="sticky top-0 z-20 bg-white/95 backdrop-blur-md border-b border-[#EEEEEE] lg:pl-64 transition-all">
        <div class="h-16 px-4 sm:px-6 lg:px-8 flex items-center justify-between">
            
            <!-- Left Side: Mobile Hamburger & Logo OR Desktop Breadcrumb -->
            <div class="flex items-center gap-3">
                <!-- Hamburger Trigger (Visible on Mobile < lg, Hidden on Desktop) -->
                <button type="button" 
                        @click="mobileDrawerOpen = true"
                        aria-label="Buka Menu Navigasi"
                        class="p-2 -ml-2 rounded-xl text-[#252525] hover:bg-[#FCEDEA] hover:text-[#E87559] transition-colors lg:hidden focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                <!-- Mobile Brand Logo (Visible on Mobile < lg) -->
                <div class="flex items-center lg:hidden">
                    <a href="{{ route('dashboard') }}" wire:navigate>
                        <x-kasivo-logo size="sm" :showTagline="false" />
                    </a>
                </div>

                <!-- Mobile & Tablet Compact Breadcrumb (Visible on < lg) -->
                <div class="flex items-center gap-2 lg:hidden min-w-0">
                    <div class="h-4 w-px bg-[#EEEEEE] shrink-0" aria-hidden="true"></div>
                    <nav aria-label="Breadcrumb Mobile" class="flex items-center text-xs font-medium min-w-0">
                        <ol class="flex items-center gap-1 min-w-0">
                            @foreach ($breadcrumbs as $crumb)
                                @if (!$loop->first)
                                    <li class="shrink-0 text-[#999999]" aria-hidden="true">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </li>
                                @endif
                                <li class="min-w-0 {{ $loop->last ? 'text-[#E87559] font-semibold truncate max-w-[120px] sm:max-w-[200px]' : 'shrink-0 text-[#777777]' }}">
                                    @if ($crumb['url'] && !$loop->last)
                                        <a href="{{ $crumb['url'] }}" 
                                           class="inline-block py-0.5 text-[#777777] hover:text-[#E87559] hover:underline transition-colors focus:outline-none">
                                            {{ $crumb['label'] }}
                                        </a>
                                    @else
                                        <span class="truncate" aria-current="{{ $loop->last ? 'page' : 'false' }}">
                                            {{ $crumb['label'] }}
                                        </span>
                                    @endif
                                </li>
                            @endforeach
                        </ol>
                    </nav>
                </div>

                <!-- Desktop Breadcrumb & Dynamic Title (Visible on lg+) -->
                <div class="hidden lg:flex flex-col">
                    <nav aria-label="Breadcrumb" class="flex items-center text-xs font-medium text-[#777777]">
                        <ol class="flex items-center gap-1.5">
                            @foreach ($breadcrumbs as $crumb)
                                @if (!$loop->first)
                                    <li class="shrink-0 text-[#999999]" aria-hidden="true">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </li>
                                @endif
                                <li class="min-w-0">
                                    @if ($crumb['url'] && !$loop->last)
                                        <a href="{{ $crumb['url'] }}" 
                                           class="inline-block py-0.5 text-[#777777] hover:text-[#E87559] hover:underline transition-colors focus:outline-none focus:text-[#E87559]">
                                            {{ $crumb['label'] }}
                                        </a>
                                    @else
                                        <span class="text-[#E87559] font-semibold truncate" aria-current="page">
                                            {{ $crumb['label'] }}
                                        </span>
                                    @endif
                                </li>
                            @endforeach
                        </ol>
                    </nav>
                    <h1 class="text-base font-extrabold text-[#252525] tracking-tight">
                        {{ $pageTitle }}
                    </h1>
                </div>
            </div>

            <!-- Right Side: Status Badge, Quick Action, and User Account Dropdown -->
            <div class="flex items-center gap-3">
                
                <!-- Quick "+ Transaksi" Action Button (Desktop & Tablet) -->
                <a href="{{ route('transactions.create') }}" 
                   wire:navigate
                   class="hidden sm:inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-[#E87559] hover:bg-[#D66448] text-white text-xs font-semibold shadow-sm shadow-[#E87559]/20 hover:shadow-md transition-all transform active:scale-95">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Catat Transaksi</span>
                </a>

                <!-- User Account Dropdown -->
                <x-dropdown align="right" width="56">
                    <x-slot name="trigger">
                        <button type="button" 
                                class="inline-flex items-center gap-2 p-1.5 rounded-xl border border-transparent hover:border-[#EEEEEE] hover:bg-gray-50 focus:outline-none transition-colors">
                            <div class="w-8 h-8 rounded-full bg-[#FCEDEA] text-[#E87559] border border-[#E87559]/30 flex items-center justify-center font-bold text-xs shadow-sm">
                                {{ $userInitials }}
                            </div>
                            <span class="hidden md:inline text-xs font-bold text-[#252525] max-w-[120px] truncate">
                                {{ $userName }}
                            </span>
                            <svg class="w-4 h-4 text-[#777777]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <!-- Dropdown Header Info -->
                        <div class="px-4 py-3 border-b border-gray-100 bg-gray-50/50">
                            <div class="text-xs text-[#777777]">Masuk sebagai</div>
                            <div class="text-sm font-bold text-[#252525] truncate">{{ $userName }}</div>
                            <div class="text-[11px] text-[#777777] truncate">{{ $userEmail }}</div>
                        </div>

                        <!-- Dropdown Links -->
                        <div class="py-1">
                            <x-dropdown-link :href="route('dashboard')" wire:navigate class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-[#777777]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                                <span>Dashboard</span>
                            </x-dropdown-link>

                            <x-dropdown-link :href="route('profile')" wire:navigate class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-[#777777]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                <span>Pengaturan Akun</span>
                            </x-dropdown-link>

                            <x-dropdown-link :href="route('business-profile')" wire:navigate class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-[#777777]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                <span>Profil Usaha & Laporan</span>
                            </x-dropdown-link>

                            <x-dropdown-link :href="url('/')" class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-[#777777]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                <span>Lihat Landing Page</span>
                            </x-dropdown-link>
                        </div>

                        <!-- Dropdown Logout -->
                        <div class="border-t border-gray-100 py-1">
                            <button type="button" 
                                    wire:click="logout" 
                                    class="w-full text-start flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                <span>Log Out</span>
                            </button>
                        </div>
                    </x-slot>
                </x-dropdown>

            </div>
        </div>
    </header>

    <!-- ======================================================== -->
    <!-- 3. MOBILE SLIDE-OVER DRAWER (Visible on Mobile < lg)     -->
    <!-- ======================================================== -->
    <div x-show="mobileDrawerOpen" 
         class="fixed inset-0 z-50 lg:hidden" 
         role="dialog" 
         aria-modal="true" 
         style="display: none;">
        
        <!-- Backdrop with Smooth Fade -->
        <div x-show="mobileDrawerOpen"
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="mobileDrawerOpen = false"
             class="fixed inset-0 bg-black/50 backdrop-blur-sm"
             aria-hidden="true"></div>

        <!-- Slide-Over Drawer Content Panel -->
        <div class="fixed inset-y-0 left-0 max-w-xs w-full flex">
            <div x-show="mobileDrawerOpen"
                 x-transition:enter="transition ease-in-out duration-300 transform"
                 x-transition:enter-start="-translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transition ease-in-out duration-200 transform"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="-translate-x-full"
                 class="relative max-w-xs w-full bg-white shadow-2xl flex-1 flex flex-col pt-5 pb-4 overflow-y-auto">
                
                <!-- Drawer Header: Brand + Close Button -->
                <div class="px-5 flex items-center justify-between pb-4 border-b border-[#EEEEEE]">
                    <a href="{{ route('dashboard') }}" wire:navigate @click="mobileDrawerOpen = false">
                        <x-kasivo-logo size="md" :showTagline="true" />
                    </a>
                    <button type="button" 
                            @click="mobileDrawerOpen = false"
                            aria-label="Tutup Menu"
                            class="p-2 -mr-2 rounded-xl text-[#777777] hover:text-[#252525] hover:bg-gray-100 focus:outline-none">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Shift / Store Status Pill -->
                <div class="px-5 pt-4 pb-2">
                    <div class="px-3.5 py-2 rounded-xl bg-gray-50 border border-[#EEEEEE] flex items-center justify-between">
                        <div class="text-xs font-bold text-[#252525]">KasiVo POS F&B</div>
                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-[#DDF1EC] text-[#43A58A] text-[10px] font-bold">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#43A58A] animate-pulse"></span>
                            <span>Shift Aktif</span>
                        </span>
                    </div>
                </div>

                <!-- Drawer Navigation Links -->
                <nav class="mt-2 flex-1 px-4 space-y-1">
                    <div class="px-3 pb-2 text-[10px] font-bold uppercase tracking-wider text-[#999999]">
                        Navigasi Lengkap
                    </div>

                    <!-- Dashboard -->
                    <a href="{{ route('dashboard') }}" 
                       wire:navigate
                       @click="mobileDrawerOpen = false"
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-colors {{ request()->routeIs('dashboard') ? 'bg-[#FCEDEA] text-[#E87559]' : 'text-[#64748B] hover:text-[#252525] hover:bg-gray-50' }}">
                        <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('dashboard') ? 'text-[#E87559]' : 'text-[#777777]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        <span>Dashboard</span>
                    </a>

                    <!-- Riwayat Transaksi -->
                    <a href="{{ route('transactions.index') }}" 
                       wire:navigate
                       @click="mobileDrawerOpen = false"
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-colors {{ request()->routeIs('transactions.index') ? 'bg-[#FCEDEA] text-[#E87559]' : 'text-[#64748B] hover:text-[#252525] hover:bg-gray-50' }}">
                        <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('transactions.index') ? 'text-[#E87559]' : 'text-[#777777]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z"/></svg>
                        <span>Riwayat Transaksi</span>
                    </a>

                    <!-- Catat Pengeluaran -->
                    <a href="{{ route('transactions.expense') }}"
                       wire:navigate
                       @click="mobileDrawerOpen = false"
                       class="flex items-center justify-between pl-11 pr-3.5 py-2 rounded-xl text-xs font-medium transition-colors {{ request()->routeIs('transactions.expense', 'transactions.photo', 'transactions.text') ? 'text-[#E87559] font-bold bg-[#FCEDEA]/50' : 'text-[#777777] hover:text-[#252525] hover:bg-gray-50' }}">
                        <span>Catat Pengeluaran</span>
                        <span class="text-[9px] font-semibold px-1.5 py-0.2 rounded bg-amber-100 text-amber-700">AI</span>
                    </a>

                    <!-- Catat Penjualan -->
                    <a href="{{ route('transactions.income') }}" 
                       wire:navigate
                       @click="mobileDrawerOpen = false"
                       class="flex items-center justify-between pl-11 pr-3.5 py-2 rounded-xl text-xs font-medium transition-colors {{ request()->routeIs('transactions.income') ? 'text-[#E87559] font-bold bg-[#FCEDEA]/50' : 'text-[#777777] hover:text-[#252525] hover:bg-gray-50' }}">
                        <span>Catat Penjualan</span>
                        <span class="text-[9px] font-semibold px-1.5 py-0.2 rounded bg-emerald-100 text-emerald-700">POS</span>
                    </a>

                    <!-- Restock -->
                    <a href="{{ route('restock') }}" 
                       wire:navigate
                       @click="mobileDrawerOpen = false"
                       class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-colors {{ request()->routeIs('restock') ? 'bg-[#FCEDEA] text-[#E87559]' : 'text-[#64748B] hover:text-[#252525] hover:bg-gray-50' }}">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('restock') ? 'text-[#E87559]' : 'text-[#777777]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            <span>Restock Bahan</span>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#FCEDEA] text-[#E87559]">2 Perlu</span>
                    </a>

                    <!-- Profil Usaha -->
                    <a href="{{ route('business-profile') }}" 
                       wire:navigate
                       @click="mobileDrawerOpen = false"
                       class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-colors {{ request()->routeIs('business-profile') ? 'bg-[#FCEDEA] text-[#E87559]' : 'text-[#64748B] hover:text-[#252525] hover:bg-gray-50' }}">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('business-profile') ? 'text-[#E87559]' : 'text-[#777777]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            <span>Profil Usaha & PDF</span>
                        </div>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-[#DDF1EC] text-[#43A58A]">PDF</span>
                    </a>

                    <!-- Pengaturan Akun -->
                    <div class="pt-4 px-3 pb-2 text-[10px] font-bold uppercase tracking-wider text-[#999999]">
                        Pengaturan
                    </div>

                    <a href="{{ route('profile') }}" 
                       wire:navigate
                       @click="mobileDrawerOpen = false"
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-colors {{ request()->routeIs('profile') ? 'bg-[#FCEDEA] text-[#E87559]' : 'text-[#64748B] hover:text-[#252525] hover:bg-gray-50' }}">
                        <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('profile') ? 'text-[#E87559]' : 'text-[#777777]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <span>Pengaturan Akun</span>
                    </a>

                    <a href="{{ url('/') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium text-[#777777] hover:text-[#252525] hover:bg-gray-50 transition-colors">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        <span>Lihat Landing Page</span>
                    </a>
                </nav>

                <!-- Drawer Footer: User & Logout -->
                <div class="p-4 border-t border-[#EEEEEE] space-y-3">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-[#E87559] text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-sm">
                            {{ $userInitials }}
                        </div>
                        <div class="truncate text-left">
                            <div class="text-xs font-bold text-[#252525] truncate">{{ $userName }}</div>
                            <div class="text-[11px] text-[#777777] truncate">{{ $userEmail }}</div>
                        </div>
                    </div>

                    <button type="button" 
                            wire:click="logout" 
                            class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-red-50 text-red-600 hover:bg-red-100 font-semibold text-xs transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        <span>Keluar dari Akun</span>
                    </button>
                </div>

            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- 4. MOBILE BOTTOM NAVIGATION BAR (Thumb-Friendly, < lg)   -->
    <!-- ======================================================== -->
    <nav class="fixed bottom-0 inset-x-0 z-30 bg-white/95 backdrop-blur-md border-t border-[#EEEEEE] px-2 py-1.5 flex items-center justify-around shadow-lg shadow-black/5 lg:hidden">
        
        <!-- 1. Dashboard -->
        <a href="{{ route('dashboard') }}" 
           wire:navigate
           class="flex-1 flex flex-col items-center justify-center py-1 px-1 rounded-xl transition-colors {{ request()->routeIs('dashboard') ? 'text-[#E87559]' : 'text-[#777777] hover:text-[#252525]' }}">
            <div class="relative p-1 rounded-lg {{ request()->routeIs('dashboard') ? 'bg-[#FCEDEA]' : '' }}">
                <svg class="w-5 h-5 {{ request()->routeIs('dashboard') ? 'text-[#E87559]' : 'text-[#777777]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                </svg>
            </div>
            <span class="text-[10px] font-semibold mt-0.5 tracking-tight {{ request()->routeIs('dashboard') ? 'text-[#E87559] font-bold' : 'text-[#777777]' }}">
                Dashboard
            </span>
        </a>

        <!-- 2. Transaksi -->
        <a href="{{ route('transactions.index') }}" 
           wire:navigate
           class="flex-1 flex flex-col items-center justify-center py-1 px-1 rounded-xl transition-colors {{ request()->routeIs('transactions.*') ? 'text-[#E87559]' : 'text-[#777777] hover:text-[#252525]' }}">
            <div class="relative p-1 rounded-lg {{ request()->routeIs('transactions.*') ? 'bg-[#FCEDEA]' : '' }}">
                <svg class="w-5 h-5 {{ request()->routeIs('transactions.*') ? 'text-[#E87559]' : 'text-[#777777]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z"/>
                </svg>
            </div>
            <span class="text-[10px] font-semibold mt-0.5 tracking-tight {{ request()->routeIs('transactions.*') ? 'text-[#E87559] font-bold' : 'text-[#777777]' }}">
                Transaksi
            </span>
        </a>

        <!-- 3. Restock -->
        <a href="{{ route('restock') }}" 
           wire:navigate
           class="flex-1 flex flex-col items-center justify-center py-1 px-1 rounded-xl transition-colors {{ request()->routeIs('restock') ? 'text-[#E87559]' : 'text-[#777777] hover:text-[#252525]' }}">
            <div class="relative p-1 rounded-lg {{ request()->routeIs('restock') ? 'bg-[#FCEDEA]' : '' }}">
                <svg class="w-5 h-5 {{ request()->routeIs('restock') ? 'text-[#E87559]' : 'text-[#777777]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                <!-- Indicator Dot -->
                <span class="absolute top-1 right-1 w-2 h-2 rounded-full bg-[#E87559]"></span>
            </div>
            <span class="text-[10px] font-semibold mt-0.5 tracking-tight {{ request()->routeIs('restock') ? 'text-[#E87559] font-bold' : 'text-[#777777]' }}">
                Restock
            </span>
        </a>

        <!-- 4. Profil (Usaha / Akun) -->
        <a href="{{ route('business-profile') }}" 
           wire:navigate
           class="flex-1 flex flex-col items-center justify-center py-1 px-1 rounded-xl transition-colors {{ (request()->routeIs('business-profile') || request()->routeIs('profile')) ? 'text-[#E87559]' : 'text-[#777777] hover:text-[#252525]' }}">
            <div class="relative p-1 rounded-lg {{ (request()->routeIs('business-profile') || request()->routeIs('profile')) ? 'bg-[#FCEDEA]' : '' }}">
                <svg class="w-5 h-5 {{ (request()->routeIs('business-profile') || request()->routeIs('profile')) ? 'text-[#E87559]' : 'text-[#777777]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <span class="text-[10px] font-semibold mt-0.5 tracking-tight {{ (request()->routeIs('business-profile') || request()->routeIs('profile')) ? 'text-[#E87559] font-bold' : 'text-[#777777]' }}">
                Profil
            </span>
        </a>

    </nav>

</div>
