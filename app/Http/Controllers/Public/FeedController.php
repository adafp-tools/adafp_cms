<?php

namespace App\Http\Controllers\Public;

use App\Enums\ContentStatus;
use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Setting;
use Illuminate\Http\Response;

class FeedController extends Controller
{
    use ResolvesLocale;

    public function index(): Response
    {
        $locale = $this->defaultLocale();
        $siteName = (string) Setting::get('site_name', config('cms.product_name'));

        $posts = Post::where('status', ContentStatus::Published)
            ->with('translations')
            ->latest('published_at')
            ->limit(20)
            ->get();

        $items = $posts->map(function ($post) use ($locale) {
            $translation = $post->translation($locale)
                        ?? $post->translations->first();
            return [
                'title'       => $translation?->title ?? $post->slug,
                'link'        => route('public.news.show', ['locale' => $locale, 'slug' => $post->slug]),
                'description' => $translation?->summary ?? '',
                'pubDate'     => $post->published_at?->format('D, d M Y H:i:s O') ?? '',
            ];
        });

        $siteUrl  = config('app.url');
        $homeUrl  = route('public.home', $locale);
        $feedUrl  = route('public.feed');

        return response(
            view('feed', compact('siteName', 'siteUrl', 'homeUrl', 'locale', 'items'))->render(),
            200,
            ['Content-Type' => 'application/rss+xml; charset=utf-8']
        );
    }
}
