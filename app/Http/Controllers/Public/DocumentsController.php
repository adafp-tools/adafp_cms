<?php

namespace App\Http\Controllers\Public;

use App\Helpers\SeoHelper;
use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\View\View;

class DocumentsController extends Controller
{
    use ResolvesLocale;

    public function index(string $locale): View
    {
        $documents = Document::whereNotNull('published_at')
            ->with('translations')
            ->latest('published_at')
            ->paginate(20);

        $headerMenu    = $this->menuItems('header');
        $footerMenu    = $this->menuItems('footer');
        $canonicalUrl  = SeoHelper::canonical('public.documents', ['locale' => $locale]);
        $hreflangLinks = SeoHelper::hreflangLinks('public.documents', ['locale' => $locale], $this->activeLocales());

        $this->recordView('/' . $locale . '/documents');

        $theme = $this->currentTheme();

        return view("themes.{$theme}.documents", array_merge(
            $this->themeData($locale),
            compact('documents', 'headerMenu', 'footerMenu', 'canonicalUrl', 'hreflangLinks'),
        ));
    }
}
