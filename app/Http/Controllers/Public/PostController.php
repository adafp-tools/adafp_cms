<?php

namespace App\Http\Controllers\Public;

use App\Enums\ContentStatus;
use App\Helpers\EmbedHelper;
use App\Helpers\SeoHelper;
use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\View\View;

class PostController extends Controller
{
    use ResolvesLocale;

    public function index(string $locale): View
    {
        $posts = Post::where('status', ContentStatus::Published)
            ->with(['translations', 'featuredImage'])
            ->latest('published_at')
            ->paginate(15);

        $headerMenu    = $this->menuItems('header');
        $footerMenu    = $this->menuItems('footer');
        $canonicalUrl  = SeoHelper::canonical('public.news.index', ['locale' => $locale]);
        $hreflangLinks = SeoHelper::hreflangLinks('public.news.index', ['locale' => $locale], $this->activeLocales());

        $this->recordView('/' . $locale . '/news');

        $theme = $this->currentTheme();

        return view("themes.{$theme}.news-index", array_merge(
            $this->themeData($locale),
            compact('posts', 'headerMenu', 'footerMenu', 'canonicalUrl', 'hreflangLinks'),
        ));
    }

    public function show(string $locale, string $slug): View
    {
        $post = Post::where('slug', $slug)
            ->where('status', ContentStatus::Published)
            ->with(['translations', 'featuredImage'])
            ->firstOrFail();

        $translation = $post->translation($locale)
                    ?? $post->translation($this->defaultLocale())
                    ?? $post->translations->first();

        if (! $translation) {
            abort(404);
        }

        $body          = $translation->body ? EmbedHelper::transform($translation->body) : '';
        $headerMenu    = $this->menuItems('header');
        $footerMenu    = $this->menuItems('footer');
        $canonicalUrl  = SeoHelper::canonical('public.news.show', ['locale' => $locale, 'slug' => $slug]);
        $hreflangLinks = SeoHelper::hreflangLinks('public.news.show', ['locale' => $locale, 'slug' => $slug], $this->activeLocales());

        $this->recordView('/' . $locale . '/news/' . $slug);

        $theme = $this->currentTheme();

        return view("themes.{$theme}.news-show", array_merge(
            $this->themeData($locale),
            [
                'post'          => $post,
                'translation'   => $translation,
                'body'          => $body,
                'pageTitle'     => $translation->title ?? $slug,
                'metaDescription' => $translation->meta_description ?? '',
                'ogType'        => 'article',
                'ogImage'       => SeoHelper::ogImage($post->featuredImage),
                'headerMenu'    => $headerMenu,
                'footerMenu'    => $footerMenu,
                'canonicalUrl'  => $canonicalUrl,
                'hreflangLinks' => $hreflangLinks,
            ]
        ));
    }
}
