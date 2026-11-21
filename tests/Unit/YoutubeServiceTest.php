<?php

namespace Tests\Unit;

use App\Services\YoutubeService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class YoutubeServiceTest extends TestCase
{
    protected YoutubeService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new YoutubeService;
    }

    public function test_extracts_video_id_from_standard_watch_urls(): void
    {
        $urls = [
            'https://www.youtube.com/watch?v=dQw4w9WgXcQ' => 'dQw4w9WgXcQ',
            'http://youtube.com/watch?v=dQw4w9WgXcQ' => 'dQw4w9WgXcQ',
            'https://www.youtube.com/watch?v=dQw4w9WgXcQ&t=42s&feature=share' => 'dQw4w9WgXcQ',
            'https://m.youtube.com/watch?v=dQw4w9WgXcQ' => 'dQw4w9WgXcQ',
        ];

        foreach ($urls as $url => $expectedId) {
            $this->assertEquals($expectedId, $this->service->extractVideoId($url), "Failed extracting ID from {$url}");
            $this->assertTrue($this->service->isValid($url));
        }
    }

    public function test_extracts_video_id_from_short_and_embed_and_shorts_urls(): void
    {
        $urls = [
            'https://youtu.be/dQw4w9WgXcQ' => 'dQw4w9WgXcQ',
            'https://youtu.be/dQw4w9WgXcQ?t=15' => 'dQw4w9WgXcQ',
            'https://www.youtube.com/embed/dQw4w9WgXcQ' => 'dQw4w9WgXcQ',
            'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ' => 'dQw4w9WgXcQ',
            'https://www.youtube.com/shorts/dQw4w9WgXcQ' => 'dQw4w9WgXcQ',
            'https://www.youtube.com/live/dQw4w9WgXcQ' => 'dQw4w9WgXcQ',
            'dQw4w9WgXcQ' => 'dQw4w9WgXcQ',
        ];

        foreach ($urls as $url => $expectedId) {
            $this->assertEquals($expectedId, $this->service->extractVideoId($url), "Failed extracting ID from {$url}");
            $this->assertTrue($this->service->isValid($url));
        }
    }

    public function test_rejects_invalid_or_non_youtube_urls(): void
    {
        $invalid = [
            '',
            'https://vimeo.com/12345678',
            'https://example.com/watch?v=123',
            'https://dailymotion.com/video/x7tgad0',
            'not a url',
            'youtube.com/random/page/without/id',
        ];

        foreach ($invalid as $url) {
            $this->assertNull($this->service->extractVideoId($url));
            $this->assertFalse($this->service->isValid($url));
        }
    }

    public function test_generates_privacy_enhanced_embed_url(): void
    {
        $url = 'https://www.youtube.com/watch?v=dQw4w9WgXcQ';
        $embed = $this->service->getEmbedUrl($url);

        $this->assertStringStartsWith('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ', $embed);
        $this->assertStringContainsString('rel=0', $embed);
        $this->assertStringContainsString('modestbranding=1', $embed);
    }

    public function test_generates_thumbnail_urls(): void
    {
        $thumbnail = $this->service->getThumbnailUrl('dQw4w9WgXcQ', 'hqdefault');
        $this->assertEquals('https://img.youtube.com/vi/dQw4w9WgXcQ/hqdefault.jpg', $thumbnail);
    }

    public function test_fetches_oembed_metadata_with_http_mock(): void
    {
        Http::fake([
            'https://www.youtube.com/oembed*' => Http::response([
                'title' => 'Wheels on the Bus | Nursery Rhyme',
                'author_name' => 'CoComelon',
                'thumbnail_url' => 'https://i.ytimg.com/vi/dQw4w9WgXcQ/hqdefault.jpg',
            ], 200),
        ]);

        $data = $this->service->fetchOEmbed('https://youtu.be/dQw4w9WgXcQ');

        $this->assertTrue($data['is_valid']);
        $this->assertEquals('dQw4w9WgXcQ', $data['video_id']);
        $this->assertEquals('Wheels on the Bus | Nursery Rhyme', $data['title']);
        $this->assertEquals('https://i.ytimg.com/vi/dQw4w9WgXcQ/hqdefault.jpg', $data['thumbnail_url']);
        $this->assertEquals('CoComelon', $data['author_name']);
        $this->assertStringContainsString('youtube-nocookie.com', $data['embed_url']);
    }

    public function test_falls_back_gracefully_when_oembed_fails_or_offline(): void
    {
        Http::fake([
            'https://www.youtube.com/oembed*' => Http::response([], 500),
        ]);

        $data = $this->service->fetchOEmbed('https://www.youtube.com/watch?v=dQw4w9WgXcQ');

        $this->assertTrue($data['is_valid']);
        $this->assertEquals('dQw4w9WgXcQ', $data['video_id']);
        $this->assertEquals('YouTube Video (dQw4w9WgXcQ)', $data['title']);
        $this->assertStringContainsString('img.youtube.com/vi/dQw4w9WgXcQ', $data['thumbnail_url']);
    }

    public function test_check_health(): void
    {
        Http::fakeSequence()
            ->push([], 200)
            ->push([], 404);

        $this->assertTrue($this->service->checkHealth('dQw4w9WgXcQ'));
        $this->assertFalse($this->service->checkHealth('dQw4w9WgXcQ'));
    }
}
