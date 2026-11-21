<?php

namespace Tests\Feature\Phase4;

use App\Models\Event;
use App\Models\Page;
use App\Models\Post;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicRoutesTest extends TestCase
{
    use RefreshDatabase;

    private function markInstalled(): void
    {
        file_put_contents(storage_path('installed'), 'test');
    }

    protected function tearDown(): void
    {
        @unlink(storage_path('installed'));
        parent::tearDown();
    }

    private function seedActiveLocales(string $locales = 'en,ar'): void
    {
        Setting::set('active_locales', $locales);
        Setting::set('default_locale', 'en');
        Setting::set('theme', 'classic');
        Setting::set('maintenance_mode', '0');
    }

    public function test_root_redirects_to_default_locale(): void
    {
        $this->markInstalled();
        $this->seedActiveLocales('en');
        $this->get('/')->assertRedirect('/en');
    }

    public function test_home_en_returns_200(): void
    {
        $this->markInstalled();
        $this->seedActiveLocales();
        $this->get('/en')->assertOk();
    }

    public function test_home_ar_returns_200_when_active(): void
    {
        $this->markInstalled();
        $this->seedActiveLocales('en,ar');
        $this->get('/ar')->assertOk();
    }

    public function test_invalid_locale_returns_404(): void
    {
        $this->markInstalled();
        $this->seedActiveLocales('en');
        $this->get('/xx')->assertNotFound();
    }

    public function test_inactive_locale_returns_404(): void
    {
        $this->markInstalled();
        $this->seedActiveLocales('en'); // ar not active
        $this->get('/ar')->assertNotFound();
    }

    public function test_news_index_returns_200(): void
    {
        $this->markInstalled();
        $this->seedActiveLocales();
        $this->get('/en/news')->assertOk();
    }

    public function test_news_show_returns_200_for_published_post(): void
    {
        $this->markInstalled();
        $this->seedActiveLocales();
        $post = Post::factory()->create();
        $this->get('/en/news/' . $post->slug)->assertOk();
    }

    public function test_news_show_returns_404_for_draft_post(): void
    {
        $this->markInstalled();
        $this->seedActiveLocales();
        $post = Post::factory()->draft()->create();
        $this->get('/en/news/' . $post->slug)->assertNotFound();
    }

    public function test_sitemap_returns_200_xml(): void
    {
        $this->markInstalled();
        $this->seedActiveLocales();
        $response = $this->get('/sitemap.xml');
        $response->assertOk();
        $this->assertStringContainsString('xml', $response->headers->get('Content-Type') ?? '');
    }

    public function test_robots_txt_returns_200_text(): void
    {
        $this->markInstalled();
        $this->seedActiveLocales();
        $response = $this->get('/robots.txt');
        $response->assertOk();
        $this->assertStringContainsString('text/plain', $response->headers->get('Content-Type') ?? '');
    }

    public function test_feed_xml_returns_200(): void
    {
        $this->markInstalled();
        $this->seedActiveLocales();
        $this->get('/feed.xml')->assertOk();
    }

    public function test_maintenance_mode_returns_503(): void
    {
        $this->markInstalled();
        $this->seedActiveLocales();
        Setting::set('maintenance_mode', '1');
        $this->get('/en')->assertStatus(503);
    }
}
