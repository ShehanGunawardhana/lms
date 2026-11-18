<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
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
<body class="min-h-full bg-slate-50 text-slate-800 antialiased flex flex-col font-sans selection:bg-brand-500 selection:text-white" x-data="{ mobileMenuOpen: false }">
    <!-- Navigation Bar -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200/80 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Brand / Logo -->
                <div class="flex items-center space-x-3">
                    <a href="{{ url('/') }}" class="flex items-center space-x-2.5 group">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-700 via-brand-600 to-accent-500 flex items-center justify-center text-white shadow-md shadow-brand-500/20 group-hover:scale-105 transition-transform duration-200">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                            </svg>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-lg font-bold tracking-tight text-slate-900 group-hover:text-brand-600 transition-colors">
                                {{ config('app.name', 'EduPortal') }}
                            </span>
                            <span class="text-[10px] uppercase tracking-widest font-semibold text-brand-600 -mt-1">
                                Institute & Nursery
                            </span>
                        </div>
                    </a>
                </div>

                <!-- Desktop Navigation Links -->
                <nav class="hidden md:flex items-center space-x-1 lg:space-x-2">
                    <a href="{{ url('/') }}" class="px-3 py-2 rounded-lg text-sm font-medium text-slate-700 hover:text-brand-600 hover:bg-slate-100 transition-colors">
                        {{ __('Home') }}
                    </a>
                    <a href="{{ Route::has('public.lecturers') ? route('public.lecturers') : url('/#lecturers') }}" class="px-3 py-2 rounded-lg text-sm font-medium text-slate-700 hover:text-brand-600 hover:bg-slate-100 transition-colors">
                        {{ __('Lecturers & Teachers') }}
                    </a>
                    <a href="{{ Route::has('public.classes') ? route('public.classes') : url('/#classes') }}" class="px-3 py-2 rounded-lg text-sm font-medium text-slate-700 hover:text-brand-600 hover:bg-slate-100 transition-colors">
                        {{ __('Classes & Programs') }}
                    </a>
                    <a href="{{ url('/#about') }}" class="px-3 py-2 rounded-lg text-sm font-medium text-slate-700 hover:text-brand-600 hover:bg-slate-100 transition-colors">
                        {{ __('About') }}
                    </a>
                </nav>

                <!-- Auth Buttons & Action -->
                <div class="hidden md:flex items-center space-x-3">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="btn-interactive inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold text-white bg-brand-600 hover:bg-brand-700 shadow-sm shadow-brand-500/30">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                            </svg>
                            {{ __('Dashboard') }}
                        </a>
                    @else
                        <a href="{{ Route::has('login') ? route('login') : url('/login') }}" class="px-3.5 py-2 text-sm font-medium text-slate-700 hover:text-brand-600 transition-colors">
                            {{ __('Sign In') }}
                        </a>
                        <a href="{{ Route::has('register.parent') ? route('register.parent') : (Route::has('register') ? route('register') : url('/register')) }}" class="btn-interactive inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold text-white bg-brand-600 hover:bg-brand-700 shadow-sm shadow-brand-500/25">
                            {{ __('Parent Register') }}
                        </a>
                    @endauth
                </div>

                <!-- Mobile Menu Button -->
                <div class="flex items-center md:hidden">
                    <button type="button" @click="mobileMenuOpen = !mobileMenuOpen" class="p-2 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500" aria-label="Toggle menu">
                        <svg x-show="!mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                        <svg x-show="mobileMenuOpen" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Dropdown Menu -->
        <div x-show="mobileMenuOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2" x-cloak class="md:hidden border-b border-slate-200 bg-white px-4 pt-2 pb-6 space-y-3">
            <a href="{{ url('/') }}" class="block px-3 py-2.5 rounded-lg text-base font-medium text-slate-800 hover:bg-slate-50 hover:text-brand-600">
                {{ __('Home') }}
            </a>
            <a href="{{ Route::has('public.lecturers') ? route('public.lecturers') : url('/#lecturers') }}" class="block px-3 py-2.5 rounded-lg text-base font-medium text-slate-800 hover:bg-slate-50 hover:text-brand-600">
                {{ __('Lecturers & Teachers') }}
            </a>
            <a href="{{ Route::has('public.classes') ? route('public.classes') : url('/#classes') }}" class="block px-3 py-2.5 rounded-lg text-base font-medium text-slate-800 hover:bg-slate-50 hover:text-brand-600">
                {{ __('Classes & Programs') }}
            </a>
            <div class="pt-3 border-t border-slate-100 flex flex-col space-y-2">
                @auth
                    <a href="{{ url('/dashboard') }}" class="w-full text-center py-2.5 rounded-lg text-sm font-semibold text-white bg-brand-600 hover:bg-brand-700">
                        {{ __('Go to Dashboard') }}
                    </a>
                @else
                    <a href="{{ Route::has('login') ? route('login') : url('/login') }}" class="w-full text-center py-2.5 rounded-lg text-sm font-medium text-slate-700 bg-slate-100 hover:bg-slate-200">
                        {{ __('Sign In') }}
                    </a>
                    <a href="{{ Route::has('register.parent') ? route('register.parent') : (Route::has('register') ? route('register') : url('/register')) }}" class="w-full text-center py-2.5 rounded-lg text-sm font-semibold text-white bg-brand-600 hover:bg-brand-700">
                        {{ __('Parent Register') }}
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow">
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    <!-- Public Footer -->
    <footer class="bg-slate-900 text-slate-400 py-12 border-t border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="space-y-4 md:col-span-2">
                    <div class="flex items-center space-x-2.5">
                        <div class="w-8 h-8 rounded-lg bg-brand-600 flex items-center justify-center text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                            </svg>
                        </div>
                        <span class="text-white font-bold text-lg">{{ config('app.name', 'EduPortal') }}</span>
                    </div>
                    <p class="text-sm text-slate-400 max-w-sm">
                        {{ __('A unified learning and management portal for nursery, kindergarten, tuition institutes, colleges, and educational centers.') }}
                    </p>
                </div>
                <div>
                    <h4 class="text-white font-semibold text-sm mb-3">{{ __('Portals') }}</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ url('/station/attendance') }}" class="hover:text-white transition-colors">{{ __('Attendance Station') }}</a></li>
                        <li><a href="{{ url('/station/printing') }}" class="hover:text-white transition-colors">{{ __('Printing Counter') }}</a></li>
                        <li><a href="{{ url('/login') }}" class="hover:text-white transition-colors">{{ __('Staff & Teacher Portal') }}</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-semibold text-sm mb-3">{{ __('Safety & Privacy') }}</h4>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        {{ __('Strict object-level access control, zero public storage leaks, encrypted health records, and dedicated guardian-controlled workflows.') }}
                    </p>
                </div>
            </div>
            <div class="mt-8 pt-8 border-t border-slate-800 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500">
                <p>&copy; {{ date('Y') }} {{ config('app.name', 'EduPortal') }}. {{ __('All rights reserved.') }}</p>
                <p class="mt-2 sm:mt-0">{{ __('Multi-Institution Educational Platform') }}</p>
            </div>
        </div>
    </footer>

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
