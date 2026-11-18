@props([
    'name' => 'file',
    'id' => null,
    'accept' => '.mp4,.mov,.webm,.pdf,.jpg,.jpeg,.png,.zip',
    'maxSizeMb' => 50,
    'helper' => __('Drag and drop your file here, or browse'),
])

@php
    $dropId = $id ?? 'file-drop-' . uniqid();
@endphp

<div 
    x-data="{ isDragging: false, fileName: '' }"
    class="w-full"
>
    <label 
        for="{{ $dropId }}"
        @dragover.prevent="isDragging = true"
        @dragleave.prevent="isDragging = false"
        @drop.prevent="isDragging = false; if ($event.dataTransfer.files.length > 0) { $refs.fileInput.files = $event.dataTransfer.files; fileName = $event.dataTransfer.files[0].name; }"
        :class="isDragging ? 'border-brand-500 bg-brand-50/50 scale-[1.01]' : 'border-slate-300 hover:border-brand-400 bg-slate-50/60 hover:bg-white'"
        class="relative flex flex-col items-center justify-center p-6 sm:p-8 rounded-2xl border-2 border-dashed transition-all duration-200 cursor-pointer text-center group"
    >
        <div class="w-12 h-12 rounded-2xl bg-white shadow-md text-brand-600 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
            <svg class="w-6 h-6 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
            </svg>
        </div>

        <p class="text-sm font-semibold text-slate-800" x-text="fileName ? fileName : '{{ $helper }}'"></p>
        <p class="text-xs text-slate-500 mt-1">
            {{ __('MP4, PDF, JPG, PNG or ZIP') }} &bull; {{ __('Max :max MB', ['max' => $maxSizeMb]) }}
        </p>

        <input 
            type="file" 
            id="{{ $dropId }}" 
            name="{{ $name }}"
            accept="{{ $accept }}"
            x-ref="fileInput"
            @change="if ($event.target.files.length > 0) fileName = $event.target.files[0].name"
            class="sr-only"
            {{ $attributes }}
        />

        {{ $slot }}
    </label>
</div>
