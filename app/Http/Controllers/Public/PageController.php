<?php

namespace App\Http\Controllers\Public;

use App\Enums\ContentStatus;
use App\Helpers\EmbedHelper;
use App\Helpers\SeoHelper;
use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\View\View;

class PageController extends Controller
{
    use ResolvesLocale;

    public function show(string $locale, string $slug): View
    {
        $page = Page::where('slug', $slug)
            ->where('status', ContentStatus::Published)
            ->with(['translations', 'featuredImage'])
            ->firstOrFail();

        $translation = $page->translation($locale)
                    ?? $page->translation($this->defaultLocale())
                    ?? $page->translations->first();

        if (! $translation) {
            abort(404);
        }

        $body = $translation->body ? EmbedHelper::transform($translation->body) : '';

        $headerMenu    = $this->menuItems('header');
        $footerMenu    = $this->menuItems('footer');
        $hreflangLinks = SeoHelper::hreflangLinks('public.page.show', ['locale' => $locale, 'slug' => $slug], $this->activeLocales());
        $canonicalUrl  = SeoHelper::canonical('public.page.show', ['locale' => $locale, 'slug' => $slug]);

        $theme = $this->currentTheme();

        $this->recordView('/' . $locale . '/' . $slug);

        return view("themes.{$theme}.page", array_merge(
            $this->themeData($locale),
            [
                'page'          => $page,
                'translation'   => $translation,
                'body'          => $body,
                'pageTitle'     => $translation->title ?? $page->slug,
                'metaDescription' => $translation->meta_description ?? '',
                'ogType'        => 'article',
                'ogImage'       => SeoHelper::ogImage($page->featuredImage),
                'headerMenu'    => $headerMenu,
                'footerMenu'    => $footerMenu,
                'hreflangLinks' => $hreflangLinks,
                'canonicalUrl'  => $canonicalUrl,
            ]
        ));
    }
}
