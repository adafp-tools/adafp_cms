<?php

namespace App\Http\Controllers\Public;

use App\Enums\ContentStatus;
use App\Helpers\SeoHelper;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Person;
use App\Models\Post;
use Illuminate\View\View;

class HomeController extends Controller
{
    use ResolvesLocale;

    public function redirect(): \Illuminate\Http\RedirectResponse
    {
        return redirect()->route('public.home', ['locale' => $this->defaultLocale()]);
    }

    public function index(string $locale): View
    {
        $this->recordView('/' . $locale);

        $posts  = Post::where('status', ContentStatus::Published)
            ->with(['translations', 'featuredImage'])
            ->latest('published_at')
            ->limit(3)
            ->get();

        $events = Event::where('status', ContentStatus::Published)
            ->where('starts_at', '>=', now())
            ->with('translations')
            ->orderBy('starts_at')
            ->limit(3)
            ->get();

        $people = Person::with(['translations', 'photo'])
            ->orderByRaw("CASE
                WHEN `group` = 'leadership' THEN 0
                WHEN `group` = 'executive'  THEN 1
                WHEN `group` = 'parliament' THEN 2
                ELSE 3 END")
            ->orderBy('sort')
            ->limit(6)
            ->get();

        $headerMenu = $this->menuItems('header');
        $footerMenu = $this->menuItems('footer');

        $hreflangLinks = SeoHelper::hreflangLinks('public.home', ['locale' => $locale], $this->activeLocales());
        $canonicalUrl  = SeoHelper::canonical('public.home', ['locale' => $locale]);

        $theme = $this->currentTheme();

        return view("themes.{$theme}.home", array_merge(
            $this->themeData($locale),
            compact('posts', 'events', 'people', 'headerMenu', 'footerMenu', 'hreflangLinks', 'canonicalUrl'),
        ));
    }
}
