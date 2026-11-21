<?php

namespace App\Http\Controllers\Public;

use App\Enums\ContentStatus;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Page;
use App\Models\Post;
use App\Models\Setting;
use Illuminate\Http\Response;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class SitemapController extends Controller
{
    use ResolvesLocale;

    public function index(): Response
    {
        $sitemap = Sitemap::create();
        $active  = $this->activeLocales();

        foreach ($active as $locale) {
            // Home
            $sitemap->add(Url::create(route('public.home', $locale))
                ->setPriority(1.0)
                ->setChangeFrequency('daily'));

            // Pages
            $pages = Page::where('status', ContentStatus::Published)
                ->with('translations')
                ->get();
            foreach ($pages as $page) {
                $sitemap->add(Url::create(route('public.page.show', ['locale' => $locale, 'slug' => $page->slug]))
                    ->setLastModificationDate($page->updated_at)
                    ->setPriority(0.8));
            }

            // Posts
            $posts = Post::where('status', ContentStatus::Published)
                ->get();
            foreach ($posts as $post) {
                $sitemap->add(Url::create(route('public.news.show', ['locale' => $locale, 'slug' => $post->slug]))
                    ->setLastModificationDate($post->updated_at)
                    ->setPriority(0.7));
            }

            // Events
            $events = Event::where('status', ContentStatus::Published)
                ->where('starts_at', '>=', now())
                ->get();
            foreach ($events as $event) {
                $sitemap->add(Url::create(route('public.events.show', ['locale' => $locale, 'slug' => $event->slug]))
                    ->setLastModificationDate($event->updated_at)
                    ->setPriority(0.7));
            }
        }

        return response($sitemap->render(), 200)
            ->header('Content-Type', 'application/xml; charset=utf-8');
    }
}
