<?php

namespace App\Http\Controllers\Public;

use App\Enums\ContentStatus;
use App\Helpers\SeoHelper;
use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\View\View;

class EventController extends Controller
{
    use ResolvesLocale;

    public function index(string $locale): View
    {
        $events = Event::where('status', ContentStatus::Published)
            ->where('starts_at', '>=', now())
            ->with('translations')
            ->orderBy('starts_at')
            ->paginate(12);

        $headerMenu    = $this->menuItems('header');
        $footerMenu    = $this->menuItems('footer');
        $canonicalUrl  = SeoHelper::canonical('public.events.index', ['locale' => $locale]);
        $hreflangLinks = SeoHelper::hreflangLinks('public.events.index', ['locale' => $locale], $this->activeLocales());

        $this->recordView('/' . $locale . '/events');

        $theme = $this->currentTheme();

        return view("themes.{$theme}.events-index", array_merge(
            $this->themeData($locale),
            compact('events', 'headerMenu', 'footerMenu', 'canonicalUrl', 'hreflangLinks'),
        ));
    }

    public function show(string $locale, string $slug): View
    {
        $event = Event::where('slug', $slug)
            ->where('status', ContentStatus::Published)
            ->with('translations')
            ->firstOrFail();

        $translation = $event->translation($locale)
                    ?? $event->translation($this->defaultLocale())
                    ?? $event->translations->first();

        if (! $translation) {
            abort(404);
        }

        $headerMenu    = $this->menuItems('header');
        $footerMenu    = $this->menuItems('footer');
        $canonicalUrl  = SeoHelper::canonical('public.events.show', ['locale' => $locale, 'slug' => $slug]);
        $hreflangLinks = SeoHelper::hreflangLinks('public.events.show', ['locale' => $locale, 'slug' => $slug], $this->activeLocales());

        $this->recordView('/' . $locale . '/events/' . $slug);

        $theme = $this->currentTheme();

        return view("themes.{$theme}.events-show", array_merge(
            $this->themeData($locale),
            [
                'event'         => $event,
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
