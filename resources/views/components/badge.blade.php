@props([
    'variant' => 'neutral', // success, danger, warning, info, nursery, neutral, brand
    'size' => 'md',        // sm, md
    'dot' => false,
])

@php
    $sizeClasses = match($size) {
        'sm' => 'px-2 py-0.5 text-[10px]',
        default => 'px-2.5 py-1 text-xs',
    };

    $variantClasses = match($variant) {
        'success' => 'bg-emerald-50 text-emerald-700 border-emerald-200/60',
        'danger' => 'bg-rose-50 text-rose-700 border-rose-200/60',
        'warning' => 'bg-amber-50 text-amber-700 border-amber-200/60',
        'info' => 'bg-cyan-50 text-cyan-700 border-cyan-200/60',
        'nursery' => 'bg-amber-100 text-amber-800 border-amber-300/80 font-bold',
        'brand' => 'bg-brand-50 text-brand-700 border-brand-200/60',
        default => 'bg-slate-100 text-slate-700 border-slate-200/60',
    };

    $dotColor = match($variant) {
        'success' => 'bg-emerald-500',
        'danger' => 'bg-rose-500',
        'warning' => 'bg-amber-500',
        'info' => 'bg-cyan-500',
        'nursery' => 'bg-amber-600',
        'brand' => 'bg-brand-500',
        default => 'bg-slate-400',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center font-semibold rounded-full border shadow-2xs select-none $sizeClasses $variantClasses"]) }}>
    @if ($dot)
        <span class="w-1.5 h-1.5 rounded-full mr-1.5 shrink-0 {{ $dotColor }}"></span>
    @endif
    {{ $slot }}
</span>
