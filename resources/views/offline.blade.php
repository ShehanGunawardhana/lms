<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5">
    <meta name="theme-color" content="#4338ca">
    <title>{{ __('Offline — Educational Portal') }}</title>
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" href="/icons/icon-192.png">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased text-slate-800 flex items-center justify-center p-4">
    <div class="max-w-md w-full text-center space-y-6 bg-white p-8 rounded-2xl shadow-xl border border-slate-100 card-hover">
        <!-- Brand Icon / Wifi Off -->
        <div class="w-20 h-20 mx-auto rounded-3xl bg-brand-50 border border-brand-100 flex items-center justify-center text-brand-600 shadow-inner">
            <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
        </div>

        <div class="space-y-2">
            <h1 class="text-2xl font-black text-brand-950 tracking-tight">
                {{ __('No Internet Connection') }}
            </h1>
            <p class="text-sm text-slate-600 leading-relaxed">
                {{ __('You appear to be offline. The portal has preserved your cached session data. Once your network connection is restored, please reconnect.') }}
            </p>
        </div>

        <div class="p-4 bg-slate-50 rounded-xl border border-slate-100 text-left text-xs text-slate-500 space-y-1.5">
            <div class="font-semibold text-slate-700 flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                {{ __('Troubleshooting Tips:') }}
            </div>
            <ul class="list-disc list-inside space-y-1 pl-1">
                <li>{{ __('Check your Wi-Fi or cellular data signal.') }}</li>
                <li>{{ __('Ensure airplane mode is switched off.') }}</li>
                <li>{{ __('Any saved offline forms will submit when reconnected.') }}</li>
            </ul>
        </div>

        <div class="pt-2">
            <button 
                type="button" 
                onclick="window.location.reload()" 
                class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl font-bold text-white bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-500 hover:to-brand-600 active:scale-[0.98] shadow-md shadow-brand-500/20 transition-all cursor-pointer btn-interactive">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                </svg>
                {{ __('Try Reconnecting') }}
            </button>
        </div>

        <p class="text-xs text-slate-400">
            {{ portal_setting('institution.name', 'Educational Institution & Nursery Portal') }}
        </p>
    </div>
</body>
</html>
