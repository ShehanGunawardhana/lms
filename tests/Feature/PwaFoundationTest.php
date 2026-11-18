<?php

namespace Tests\Feature;

use Tests\TestCase;

class PwaFoundationTest extends TestCase
{
    public function test_pwa_manifest_exists_and_has_valid_json_schema(): void
    {
        $manifestPath = public_path('manifest.json');
        expect(file_exists($manifestPath))->toBeTrue();

        $content = (string) file_get_contents($manifestPath);
        $json = json_decode($content, true);

        expect($json)->toBeArray();
        expect($json['name'])->toBe('Educational Institution & Nursery Learning Portal');
        expect($json['short_name'])->toBe('EduPortal');
        expect($json['start_url'])->toBe('/');
        expect($json['display'])->toBe('standalone');
        expect($json['theme_color'])->toBe('#4338ca');
        expect($json['icons'])->toHaveCount(3);
    }

    public function test_pwa_icons_exist(): void
    {
        expect(file_exists(public_path('icons/icon-192.png')))->toBeTrue();
        expect(file_exists(public_path('icons/icon-512.png')))->toBeTrue();
        expect(file_exists(public_path('icons/icon-maskable-512.png')))->toBeTrue();
    }

    public function test_service_worker_file_exists_and_contains_caching_logic(): void
    {
        $swPath = public_path('sw.js');
        expect(file_exists($swPath))->toBeTrue();

        $content = (string) file_get_contents($swPath);
        expect($content)->toContain('/offline');
        expect($content)->toContain('portal-pwa-v1');
        expect($content)->toContain('addEventListener(\'fetch\'');
    }

    public function test_offline_page_route_renders_successfully(): void
    {
        $response = $this->get(route('offline'));

        $response->assertOk()
            ->assertSee(__('No Internet Connection'))
            ->assertSee(__('Try Reconnecting'));
    }

    public function test_layouts_link_to_pwa_manifest(): void
    {
        $renderedPublic = view('layouts.public', ['title' => 'Test'])->render();
        expect($renderedPublic)->toContain('<link rel="manifest" href="/manifest.json">');

        $renderedApp = view('layouts.app', ['title' => 'Test'])->render();
        expect($renderedApp)->toContain('<link rel="manifest" href="/manifest.json">');
    }
}
