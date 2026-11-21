<?php

namespace App\Http\Controllers\Public;

use App\Helpers\SeoHelper;
use App\Http\Controllers\Controller;
use App\Models\Person;
use Illuminate\View\View;

class PeopleController extends Controller
{
    use ResolvesLocale;

    public function index(string $locale): View
    {
        $people = Person::with(['translations', 'photo'])
            ->orderByRaw("CASE
                WHEN `group` = 'leadership' THEN 0
                WHEN `group` = 'executive'  THEN 1
                WHEN `group` = 'parliament' THEN 2
                ELSE 3 END")
            ->orderBy('sort')
            ->get();

        $headerMenu    = $this->menuItems('header');
        $footerMenu    = $this->menuItems('footer');
        $canonicalUrl  = SeoHelper::canonical('public.leadership', ['locale' => $locale]);
        $hreflangLinks = SeoHelper::hreflangLinks('public.leadership', ['locale' => $locale], $this->activeLocales());

        $this->recordView('/' . $locale . '/leadership');

        $theme = $this->currentTheme();

        return view("themes.{$theme}.leadership", array_merge(
            $this->themeData($locale),
            compact('people', 'headerMenu', 'footerMenu', 'canonicalUrl', 'hreflangLinks'),
        ));
    }
}
