@props([
    'title' => null,
    'subtitle' => null,
    'hover' => true,
    'padding' => true,
    'badge' => null,
])

<div {{ $attributes->merge([
    'class' => 'bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden ' . 
               ($hover ? 'card-hover ' : '')
]) }}>
    @if ($title || isset($header) || $badge)
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                @if (isset($header))
                    {{ $header }}
                @else
                    <h3 class="text-base font-bold text-slate-900 tracking-tight">{{ $title }}</h3>
                    @if ($subtitle)
                        <p class="text-xs text-slate-500 mt-0.5">{{ $subtitle }}</p>
                    @endif
                @endif
            </div>
            @if ($badge)
                <div>{{ $badge }}</div>
            @endif
        </div>
    @endif

    <div class="{{ $padding ? 'p-5 sm:p-6' : '' }}">
        {{ $slot }}
    </div>

    @if (isset($footer))
        <div class="px-5 py-3 bg-slate-50/70 border-t border-slate-100 text-xs text-slate-500 flex items-center justify-between">
            {{ $footer }}
        </div>
    @endif
</div>
