<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Throwable;

class YoutubeService
{
    /**
     * Privacy-enhanced embed base domain.
     */
    public const EMBED_BASE = 'https://www.youtube-nocookie.com/embed/';

    /**
     * Check if a given URL or string is a valid YouTube URL/ID.
     */
    public function isValid(string $url): bool
    {
        return $this->extractVideoId($url) !== null;
    }

    /**
     * Extract the 11-character YouTube video ID from various URL formats:
     * - watch?v=VIDEO_ID
     * - youtu.be/VIDEO_ID
     * - /embed/VIDEO_ID
     * - /shorts/VIDEO_ID
     * - /live/VIDEO_ID
     * - raw 11-character video ID
     */
    public function extractVideoId(string $url): ?string
    {
        $trimmed = trim($url);

        if (empty($trimmed)) {
            return null;
        }

        // If it's already a plain 11-character alphanumeric ID
        if (preg_match('/^[a-zA-Z0-9_-]{11}$/', $trimmed)) {
            return $trimmed;
        }

        // Parse host and query via parse_url
        $parsed = parse_url($trimmed);
        if (! $parsed) {
            return null;
        }

        $host = strtolower($parsed['host'] ?? '');
        $path = $parsed['path'] ?? '';
        $query = $parsed['query'] ?? '';

        // Check if domain is youtube.com, youtu.be, youtube-nocookie.com
        $isYouTube = str_contains($host, 'youtube.com')
            || str_contains($host, 'youtu.be')
            || str_contains($host, 'youtube-nocookie.com');

        if (! $isYouTube && ! empty($host)) {
            return null;
        }

        // 1. youtu.be/ID
        if (str_contains($host, 'youtu.be')) {
            $segments = explode('/', trim($path, '/'));
            $candidate = $segments[0];
            if (preg_match('/^[a-zA-Z0-9_-]{11}$/', $candidate)) {
                return $candidate;
            }
        }

        // 2. Query param ?v=ID (e.g. watch?v=...)
        if (! empty($query)) {
            parse_str($query, $queryParams);
            if (isset($queryParams['v']) && is_string($queryParams['v']) && preg_match('/^[a-zA-Z0-9_-]{11}$/', $queryParams['v'])) {
                return $queryParams['v'];
            }
        }

        // 3. Path /embed/ID, /shorts/ID, /v/ID, /live/ID
        if (preg_match('#/(?:embed|shorts|v|live)/([a-zA-Z0-9_-]{11})#', $path, $matches)) {
            return $matches[1];
        }

        // 4. Regex fallback across full URL
        if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/\n\s]+/\S+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([a-zA-Z0-9_-]{11})%x', $trimmed, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Generate privacy-enhanced embed URL with youtube-nocookie.com
     *
     * @param  array<string, mixed>  $params
     */
    public function getEmbedUrl(string $urlOrVideoId, array $params = []): string
    {
        $videoId = $this->extractVideoId($urlOrVideoId) ?? $urlOrVideoId;

        $defaultParams = [
            'rel' => 0,
            'modestbranding' => 1,
        ];

        $allParams = array_merge($defaultParams, $params);
        $queryString = http_build_query($allParams);

        return self::EMBED_BASE.$videoId.($queryString ? '?'.$queryString : '');
    }

    /**
     * Generate high-quality thumbnail image URL.
     */
    public function getThumbnailUrl(string $urlOrVideoId, string $quality = 'hqdefault'): string
    {
        $videoId = $this->extractVideoId($urlOrVideoId) ?? $urlOrVideoId;

        return "https://img.youtube.com/vi/{$videoId}/{$quality}.jpg";
    }

    /**
     * Fetch video title and thumbnail via YouTube's public oEmbed endpoint.
     * No API key required.
     *
     * @return array{video_id: string, title: string, thumbnail_url: string, author_name: ?string, author_url: ?string, embed_url: string, is_valid: bool}
     */
    public function fetchOEmbed(string $url): array
    {
        $videoId = $this->extractVideoId($url);

        if (! $videoId) {
            return [
                'video_id' => '',
                'title' => '',
                'thumbnail_url' => '',
                'author_name' => null,
                'author_url' => null,
                'embed_url' => '',
                'is_valid' => false,
            ];
        }

        $canonicalUrl = "https://www.youtube.com/watch?v={$videoId}";
        $defaultThumbnail = $this->getThumbnailUrl($videoId, 'hqdefault');
        $embedUrl = $this->getEmbedUrl($videoId);

        try {
            $response = Http::timeout(4)
                ->acceptJson()
                ->get('https://www.youtube.com/oembed', [
                    'url' => $canonicalUrl,
                    'format' => 'json',
                ]);

            if ($response->successful()) {
                /** @var array<string, mixed> $data */
                $data = $response->json();

                return [
                    'video_id' => $videoId,
                    'title' => (string) ($data['title'] ?? "YouTube Video ({$videoId})"),
                    'thumbnail_url' => (string) ($data['thumbnail_url'] ?? $defaultThumbnail),
                    'author_name' => isset($data['author_name']) ? (string) $data['author_name'] : null,
                    'author_url' => isset($data['author_url']) ? (string) $data['author_url'] : null,
                    'embed_url' => $embedUrl,
                    'is_valid' => true,
                ];
            }
        } catch (Throwable) {
            // Graceful fallback for offline, mock, or sandboxed environments
        }

        return [
            'video_id' => $videoId,
            'title' => "YouTube Video ({$videoId})",
            'thumbnail_url' => $defaultThumbnail,
            'author_name' => null,
            'author_url' => null,
            'embed_url' => $embedUrl,
            'is_valid' => true,
        ];
    }

    /**
     * Verify whether a YouTube video is reachable and public.
     */
    public function checkHealth(string $urlOrVideoId): bool
    {
        $videoId = $this->extractVideoId($urlOrVideoId);
        if (! $videoId) {
            return false;
        }

        try {
            $response = Http::timeout(4)->get('https://www.youtube.com/oembed', [
                'url' => "https://www.youtube.com/watch?v={$videoId}",
                'format' => 'json',
            ]);

            return $response->status() === 200;
        } catch (Throwable) {
            return false;
        }
    }
}
