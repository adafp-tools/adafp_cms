<?php

namespace App\Http\Controllers\Public;

use App\Enums\ContentStatus;
use App\Helpers\SeoHelper;
use App\Http\Controllers\Controller;
use App\Models\Gallery;
use Illuminate\View\View;

class GalleryController extends Controller
{
    use ResolvesLocale;

    public function show(string $locale, string $slug): View
    {
        $gallery = Gallery::where('slug', $slug)
            ->where('status', ContentStatus::Published)
            ->with(['translations', 'images.translations'])
            ->firstOrFail();

        $translation = $gallery->translation($locale)
                    ?? $gallery->translation($this->defaultLocale())
                    ?? $gallery->translations->first();

        if (! $translation) {
            abort(404);
        }

        $headerMenu    = $this->menuItems('header');
        $footerMenu    = $this->menuItems('footer');
        $canonicalUrl  = SeoHelper::canonical('public.gallery.show', ['locale' => $locale, 'slug' => $slug]);
        $hreflangLinks = SeoHelper::hreflangLinks('public.gallery.show', ['locale' => $locale, 'slug' => $slug], $this->activeLocales());

        $this->recordView('/' . $locale . '/gallery/' . $slug);

        $theme = $this->currentTheme();

        return view("themes.{$theme}.gallery", array_merge(
            $this->themeData($locale),
            [
                'gallery'       => $gallery,
                'translation'   => $translation,
                'pageTitle'     => $translation->title ?? $slug,
                'headerMenu'    => $headerMenu,
                'footerMenu'    => $footerMenu,
                'canonicalUrl'  => $canonicalUrl,
                'hreflangLinks' => $hreflangLinks,
            ]
        ));
    }
}
