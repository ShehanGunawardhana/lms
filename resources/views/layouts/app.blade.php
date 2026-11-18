<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#4338ca">

    <title>{{ $title ?? config('app.name', 'EduPortal') }}</title>

    <!-- PWA -->
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />

    <!-- Scripts and Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-full antialiased font-sans text-slate-800 flex flex-col md:flex-row" x-data="{ sidebarOpen: false, userDropdown: false, notifDropdown: false }">
    
    <!-- Toast Notification Container -->
    <div class="fixed bottom-5 right-5 z-50 flex flex-col space-y-2 pointer-events-none max-w-sm w-full" id="toast-container">
        @if (session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" 
                 x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-3" x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 class="pointer-events-auto p-4 rounded-xl bg-white shadow-xl border border-emerald-100 border-l-4 border-l-emerald-500 flex items-start space-x-3">
                <div class="text-emerald-500 mt-0.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <div class="flex-1 text-sm text-slate-800">{{ session('success') }}</div>
                <button @click="show = false" class="text-slate-400 hover:text-slate-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
        @endif
        @if (session('error'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                 x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-3" x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 class="pointer-events-auto p-4 rounded-xl bg-white shadow-xl border border-rose-100 border-l-4 border-l-rose-500 flex items-start space-x-3">
                <div class="text-rose-500 mt-0.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </div>
                <div class="flex-1 text-sm text-slate-800">{{ session('error') }}</div>
                <button @click="show = false" class="text-slate-400 hover:text-slate-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
        @endif
    </div>

    <!-- Mobile Top Header Bar -->
    <div class="md:hidden sticky top-0 z-30 bg-slate-900 text-white flex items-center justify-between px-4 h-16 shadow-md">
        <button type="button" @click="sidebarOpen = true" class="p-2 -ml-2 rounded-lg text-slate-300 hover:text-white hover:bg-slate-800">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
        <div class="flex items-center space-x-2">
            <div class="w-8 h-8 rounded-lg bg-brand-600 flex items-center justify-center font-bold text-white text-sm">
                EP
            </div>
            <span class="font-bold text-white tracking-tight">{{ config('app.name', 'EduPortal') }}</span>
        </div>
        <div class="flex items-center space-x-2">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
        </div>
    </div>

    <!-- Off-canvas Mobile Sidebar Overlay -->
    <div x-show="sidebarOpen" x-cloak class="md:hidden fixed inset-0 z-40 flex">
        <div x-show="sidebarOpen" x-transition:enter="transition-opacity ease-linear duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-linear duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs" @click="sidebarOpen = false"></div>
        <div x-show="sidebarOpen" x-transition:enter="transition ease-in-out duration-250 transform" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in-out duration-250 transform" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full" class="relative flex-1 flex flex-col max-w-xs w-full bg-slate-900 text-white pt-5 pb-4">
            <div class="flex items-center justify-between px-4 pb-4 border-b border-slate-800">
                <span class="font-bold text-lg tracking-tight">{{ config('app.name', 'EduPortal') }}</span>
                <button type="button" @click="sidebarOpen = false" class="p-2 rounded-lg text-slate-400 hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <nav class="mt-4 px-3 space-y-1 flex-1 overflow-y-auto">
                @include('layouts.partials.nav-links')
            </nav>
        </div>
    </div>

    <!-- Desktop Sidebar -->
    <aside class="hidden md:flex flex-col w-64 bg-slate-900 text-slate-300 min-h-screen shrink-0 border-r border-slate-800 select-none">
        <!-- Brand Header -->
        <div class="h-16 flex items-center px-6 border-b border-slate-800/80">
            <a href="{{ url('/dashboard') }}" class="flex items-center space-x-3 group">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-brand-600 to-accent-500 flex items-center justify-center text-white shadow-md shadow-brand-500/20 group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                    </svg>
                </div>
                <div class="flex flex-col">
                    <span class="text-white font-bold tracking-tight text-sm group-hover:text-brand-400 transition-colors">
                        {{ config('app.name', 'EduPortal') }}
                    </span>
                    <span class="text-[10px] uppercase font-semibold text-accent-400 tracking-wider">
                        Management Hub
                    </span>
                </div>
            </a>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
            @include('layouts.partials.nav-links')
        </nav>

        <!-- Current User Info -->
        <div class="p-3 border-t border-slate-800 bg-slate-950/40">
            <div class="flex items-center justify-between px-2 py-2">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 rounded-full bg-brand-600 flex items-center justify-center font-bold text-white text-xs ring-2 ring-brand-500/30">
                        {{ substr(auth()->user()?->name ?? 'U', 0, 1) }}
                    </div>
                    <div class="flex flex-col truncate max-w-[120px]">
                        <span class="text-xs font-medium text-white truncate">{{ auth()->user()?->name ?? 'Guest User' }}</span>
                        <span class="text-[10px] text-slate-400 capitalize truncate">{{ auth()->user()?->roles?->first()?->name ?? 'Portal User' }}</span>
                    </div>
                </div>
                <form method="POST" action="{{ Route::has('logout') ? route('logout') : url('/logout') }}">
                    @csrf
                    <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-400 hover:bg-slate-800 transition-colors" title="{{ __('Sign out') }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0">
        <!-- Top Application Bar -->
        <header class="h-16 bg-white border-b border-slate-200/80 px-4 sm:px-6 flex items-center justify-between shadow-xs sticky top-0 z-20">
            <!-- Left: Breadcrumbs / Page Context -->
            <div class="flex items-center space-x-3">
                <h1 class="text-lg font-bold text-slate-900 tracking-tight">
                    {{ $header ?? __('Dashboard') }}
                </h1>
            </div>

            <!-- Right: Actions, Notifications, Profile -->
            <div class="flex items-center space-x-3 sm:space-x-4">
                <!-- Station Shortcuts -->
                <div class="hidden sm:flex items-center space-x-2">
                    <a href="{{ url('/station/attendance') }}" class="inline-flex items-center px-2.5 py-1.5 rounded-lg text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 transition-colors">
                        <svg class="w-3.5 h-3.5 mr-1 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                        {{ __('Attendance') }}
                    </a>
                    <a href="{{ url('/station/printing') }}" class="inline-flex items-center px-2.5 py-1.5 rounded-lg text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 transition-colors">
                        <svg class="w-3.5 h-3.5 mr-1 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        {{ __('Printing') }}
                    </a>
                </div>

                <!-- Notifications Dropdown -->
                <div class="relative" @click.outside="notifDropdown = false">
                    <button type="button" @click="notifDropdown = !notifDropdown" class="p-2 rounded-lg text-slate-500 hover:text-slate-800 hover:bg-slate-100 relative">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        <span class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-brand-500 ring-2 ring-white"></span>
                    </button>
                    <div x-show="notifDropdown" x-transition x-cloak class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-xl border border-slate-200 py-2 z-50">
                        <div class="px-4 py-2 border-b border-slate-100 flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-800 uppercase tracking-wider">{{ __('Notifications') }}</span>
                            <span class="text-[10px] text-brand-600 font-semibold">{{ __('Mark all read') }}</span>
                        </div>
                        <div class="py-4 text-center text-xs text-slate-500">
                            {{ __('No unread notifications') }}
                        </div>
                    </div>
                </div>

                <!-- Profile Dropdown -->
                <div class="relative" @click.outside="userDropdown = false">
                    <button type="button" @click="userDropdown = !userDropdown" class="flex items-center space-x-2 focus:outline-none">
                        <div class="w-9 h-9 rounded-full bg-brand-600 flex items-center justify-center font-bold text-white text-sm shadow-xs">
                            {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                        </div>
                        <svg class="w-4 h-4 text-slate-400 hidden sm:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="userDropdown" x-transition x-cloak class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-xl border border-slate-200 py-1.5 z-50">
                        <div class="px-4 py-2 border-b border-slate-100">
                            <p class="text-xs font-semibold text-slate-900 truncate">{{ auth()->user()->name ?? 'User' }}</p>
                            <p class="text-[11px] text-slate-500 truncate">{{ auth()->user()->email ?? 'user@portal.local' }}</p>
                        </div>
                        <a href="{{ Route::has('profile.show') ? route('profile.show') : url('/profile') }}" class="block px-4 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-brand-600">
                            {{ __('Profile & Settings') }}
                        </a>
                        <form method="POST" action="{{ route('logout', [], false) ? route('logout') : url('/logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2 text-xs font-medium text-rose-600 hover:bg-rose-50">
                                {{ __('Sign Out') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <!-- Dynamic Content Body -->
        <main class="flex-1 p-4 sm:p-6 lg:p-8 animate-fade-in">
            {{ $slot ?? '' }}
            @yield('content')
        </main>
    </div>

    @livewireScripts
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').catch(err => console.log('SW registration failed:', err));
            });
        }
    </script>
</body>
</html>
