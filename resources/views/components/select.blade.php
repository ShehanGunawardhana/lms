@props([
    'label' => null,
    'name' => null,
    'id' => null,
    'placeholder' => null,
    'helper' => null,
    'required' => false,
])

@php
    $selectId = $id ?? $name ?? 'select-' . uniqid();
    $hasError = $name && isset($errors) && $errors->has($name);
@endphp

<div class="w-full">
    @if ($label)
        <label for="{{ $selectId }}" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
            {{ $label }}
            @if ($required)
                <span class="text-rose-500 font-bold">*</span>
            @endif
        </label>
    @endif

    <div class="relative rounded-xl shadow-xs">
        <select 
            id="{{ $selectId }}"
            name="{{ $name }}"
            @if($required) required @endif
            {{ $attributes->merge([
                'class' => 'block w-full rounded-xl bg-white text-slate-900 text-sm shadow-xs transition-colors duration-150 min-h-[42px] px-3.5 ' .
                           ($hasError 
                               ? 'border-rose-400 focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20' 
                               : 'border-slate-300 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20')
            ]) }}
        >
            @if ($placeholder)
                <option value="" disabled selected>{{ $placeholder }}</option>
            @endif
            {{ $slot }}
        </select>
    </div>

    @if ($hasError)
        <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $errors->first($name) }}</p>
    @elseif ($helper)
        <p class="mt-1.5 text-xs text-slate-500">{{ $helper }}</p>
    @endif
</div>
