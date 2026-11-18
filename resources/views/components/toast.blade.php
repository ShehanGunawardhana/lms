@props([
    'type' => 'info', // success, error, warning, info
    'title' => null,
    'message' => null,
    'timeout' => 5000,
])

@php
    $borderColor = match($type) {
        'success' => 'border-l-emerald-500 text-emerald-600',
        'error' => 'border-l-rose-500 text-rose-600',
        'warning' => 'border-l-amber-500 text-amber-600',
        default => 'border-l-brand-500 text-brand-600',
    };
@endphp

<div 
    x-data="{ show: true }" 
    x-show="show" 
    @if ($timeout) x-init="setTimeout(() => show = false, {{ $timeout }})" @endif
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 translate-y-2 scale-95"
    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-95"
    class="p-4 rounded-xl bg-white shadow-xl border border-slate-200/80 border-l-4 {{ $borderColor }} flex items-start space-x-3"
>
    <div class="mt-0.5 shrink-0">
        @if ($type === 'success')
            <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        @elseif ($type === 'error')
            <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        @elseif ($type === 'warning')
            <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        @else
            <svg class="w-5 h-5 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        @endif
    </div>

    <div class="flex-1">
        @if ($title)
            <h4 class="text-xs font-bold text-slate-900">{{ $title }}</h4>
        @endif
        <div class="text-xs text-slate-600 mt-0.5">
            {{ $message ?? $slot }}
        </div>
    </div>

    <button type="button" @click="show = false" class="text-slate-400 hover:text-slate-600 p-0.5">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>
</div>
