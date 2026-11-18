@props([
    'label' => null,
    'name' => null,
    'id' => null,
    'type' => 'text',
    'placeholder' => null,
    'helper' => null,
    'required' => false,
    'icon' => null,
])

@php
    $inputId = $id ?? $name ?? 'input-' . uniqid();
    $hasError = $name && isset($errors) && $errors->has($name);
@endphp

<div class="w-full">
    @if ($label)
        <label for="{{ $inputId }}" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
            {{ $label }}
            @if ($required)
                <span class="text-rose-500 font-bold">*</span>
            @endif
        </label>
    @endif

    <div class="relative rounded-xl shadow-xs">
        @if ($icon)
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                {{ $icon }}
            </div>
        @endif

        <input 
            type="{{ $type }}"
            id="{{ $inputId }}"
            name="{{ $name }}"
            placeholder="{{ $placeholder }}"
            @if($required) required @endif
            {{ $attributes->merge([
                'class' => 'block w-full rounded-xl border-slate-300 bg-white text-slate-900 text-sm shadow-xs transition-colors duration-150 min-h-[42px] ' .
                           ($icon ? 'pl-10 ' : 'pl-3.5 ') .
                           ($hasError 
                               ? 'border-rose-400 focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20' 
                               : 'border-slate-300 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20')
            ]) }}
        />
    </div>

    @if ($hasError)
        <p class="mt-1.5 text-xs text-rose-600 font-medium flex items-center">
            <svg class="w-3.5 h-3.5 mr-1 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            {{ $errors->first($name) }}
        </p>
    @elseif ($helper)
        <p class="mt-1.5 text-xs text-slate-500">{{ $helper }}</p>
    @endif
</div>
