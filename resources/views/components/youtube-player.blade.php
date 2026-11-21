@props([
    'videoId' => null,
    'url' => null,
    'title' => 'YouTube video player',
    'aspectRatio' => 'aspect-video',
    'autoplay' => false,
])

@php
    $id = $videoId;
    if (!$id && $url) {
        $id = app(\App\Services\YoutubeService::class)->extractVideoId($url);
    }
    $params = ['rel' => 0, 'modestbranding' => 1];
    if ($autoplay) {
        $params['autoplay'] = 1;
    }
    $embedUrl = $id ? app(\App\Services\YoutubeService::class)->getEmbedUrl($id, $params) : null;
@endphp

@if ($id && $embedUrl)
    <div {{ $attributes->merge(['class' => "relative w-full {$aspectRatio} overflow-hidden rounded-2xl bg-slate-900 shadow-md border border-slate-200/60 dark:border-slate-800"]) }}>
        <iframe
            src="{{ $embedUrl }}"
            title="{{ $title }}"
            class="absolute inset-0 w-full h-full border-0 rounded-2xl"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
            referrerpolicy="strict-origin-when-cross-origin"
            loading="lazy"
            allowfullscreen
        ></iframe>
    </div>
@else
    <div class="p-4 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-500 text-xs text-center border border-dashed border-slate-300 dark:border-slate-700">
        {{ __('Invalid or missing YouTube video.') }}
    </div>
@endif
