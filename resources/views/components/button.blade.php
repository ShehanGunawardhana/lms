@props([
    'variant' => 'primary', // primary, secondary, accent, nursery, danger, outline, ghost
    'size' => 'md',        // sm, md, lg
    'type' => 'button',
    'loading' => false,
    'icon' => null,
])

@php
    $baseClasses = 'inline-flex items-center justify-center font-semibold rounded-xl transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 active:scale-[0.98] disabled:opacity-50 disabled:pointer-events-none select-none';

    $sizeClasses = match($size) {
        'sm' => 'px-3 py-1.5 text-xs min-h-[36px]',
        'lg' => 'px-6 py-3.5 text-base min-h-[48px]',
        default => 'px-4 py-2.5 text-sm min-h-[42px]',
    };

    $variantClasses = match($variant) {
        'secondary' => 'bg-slate-100 text-slate-700 hover:bg-slate-200 focus:ring-slate-400',
        'accent' => 'bg-accent-500 text-white hover:bg-accent-600 shadow-md shadow-accent-500/20 hover:shadow-lg hover:-translate-y-0.5 focus:ring-accent-400',
        'nursery' => 'bg-amber-500 text-white hover:bg-amber-600 shadow-md shadow-amber-500/25 hover:shadow-lg hover:-translate-y-0.5 focus:ring-amber-400',
        'danger' => 'bg-rose-600 text-white hover:bg-rose-700 shadow-md shadow-rose-600/20 hover:shadow-lg hover:-translate-y-0.5 focus:ring-rose-500',
        'outline' => 'border-2 border-slate-300 text-slate-700 hover:border-brand-600 hover:text-brand-600 hover:bg-brand-50/50 focus:ring-brand-500',
        'ghost' => 'text-slate-600 hover:text-brand-600 hover:bg-slate-100 focus:ring-slate-300',
        default => 'bg-brand-600 text-white hover:bg-brand-700 shadow-md shadow-brand-600/25 hover:shadow-lg hover:-translate-y-0.5 focus:ring-brand-500',
    };
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['class' => "$baseClasses $sizeClasses $variantClasses"]) }}>
    @if ($icon)
        <span class="mr-2 -ml-0.5 shrink-0">{{ $icon }}</span>
    @endif
    
    {{ $slot }}

    @if ($loading)
        <svg wire:loading class="animate-spin -mr-0.5 ml-2 h-4 w-4 text-current" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
        </svg>
    @endif
</button>
