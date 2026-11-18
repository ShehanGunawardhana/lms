<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? __('Station Mode') }} - {{ config('app.name', 'EduPortal') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full text-slate-100 flex flex-col font-sans select-none overflow-x-hidden antialiased" 
      x-data="{ 
          currentTime: '', 
          stationName: '{{ $stationName ?? __('Station Mode') }}',
          initClock() {
              this.updateTime();
              setInterval(() => this.updateTime(), 1000);
          },
          updateTime() {
              const now = new Date();
              this.currentTime = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
          }
      }" 
      x-init="initClock()">

    <!-- Kiosk Top Control Header -->
    <header class="h-20 bg-slate-900 border-b border-slate-800 px-4 sm:px-8 flex items-center justify-between shrink-0 shadow-lg">
        <!-- Station Identity -->
        <div class="flex items-center space-x-4">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-brand-600 to-accent-500 flex items-center justify-center text-white shadow-lg shadow-brand-500/25">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight flex items-center gap-2">
                    <span>{{ $stationTitle ?? __('Terminal Station') }}</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mr-1.5 animate-pulse"></span>
                        {{ __('ONLINE') }}
                    </span>
                </h1>
                <p class="text-xs text-slate-400">{{ config('app.name', 'EduPortal') }} &bull; {{ $stationSubtitle ?? __('Automated Kiosk Terminal') }}</p>
            </div>
        </div>

        <!-- Center: Real-time Clock -->
        <div class="hidden md:flex flex-col items-center">
            <span class="text-3xl font-mono font-bold tracking-widest text-slate-100" x-text="currentTime">--:--:--</span>
            <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400">{{ date('l, M j, Y') }}</span>
        </div>

        <!-- Right: Actions & Exit -->
        <div class="flex items-center space-x-3">
            <a href="{{ url('/station/attendance') }}" class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all {{ request()->is('*attendance*') ? 'bg-brand-600 text-white shadow-md shadow-brand-500/25 ring-2 ring-brand-400' : 'bg-slate-800 text-slate-300 hover:bg-slate-700' }}">
                {{ __('Attendance') }}
            </a>
            <a href="{{ url('/station/printing') }}" class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all {{ request()->is('*printing*') ? 'bg-brand-600 text-white shadow-md shadow-brand-500/25 ring-2 ring-brand-400' : 'bg-slate-800 text-slate-300 hover:bg-slate-700' }}">
                {{ __('Print Counter') }}
            </a>
            <a href="{{ url('/dashboard') }}" class="p-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white transition-colors" title="{{ __('Back to Dashboard') }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </a>
        </div>
    </header>

    <!-- Station Dynamic Viewport -->
    <main class="flex-1 flex flex-col p-4 sm:p-6 lg:p-8 max-w-7xl mx-auto w-full">
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    @livewireScripts
</body>
</html>
