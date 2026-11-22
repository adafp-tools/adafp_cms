<?php

namespace App\Http\Controllers\Public;

use App\Models\PageView;
use App\Models\Setting;
use Illuminate\Support\Facades\Request;

trait ResolvesLocale
{
    protected function activeLocales(): array
    {
        $str = Setting::get('active_locales', 'en');
        return array_map('trim', explode(',', (string) $str));
    }

    protected function defaultLocale(): string
    {
        return (string) Setting::get('default_locale', 'en');
    }

    protected function currentTheme(): string
    {
        return (string) Setting::get('theme', 'classic');
    }

    protected function themeLayout(): string
    {
        return 'themes.' . $this->currentTheme() . '.layout';
    }

    protected function themeData(string $locale): array
    {
        $siteName     = (string) Setting::get('site_name', config('cms.product_name'));
        $primaryColor = (string) Setting::get('primary_color', '#1e3a5f');
        $secondaryColor = (string) Setting::get('secondary_color', '#c0392b');
        $textOnPrimary  = (string) Setting::get('text_on_primary', '#ffffff');
        $logoPath       = (string) Setting::get('logo_path', '');
        $noindex        = config('cms.instance') === 'demo'
                          || Setting::get('maintenance_mode', '0') === '1'
                          || Setting::get('noindex', '0') === '1';
        $logoUrl = $logoPath ? config('app.url') . '/storage/' . $logoPath : null;
        $theme   = $this->currentTheme();

        return [
            'locale'         => $locale,
            'siteName'       => $siteName,
            'primaryColor'   => $primaryColor,
            'secondaryColor' => $secondaryColor,
            'textOnPrimary'  => $textOnPrimary,
            'logoUrl'        => $logoUrl,
            'activeLocales'  => $this->activeLocales(),
            'noindex'        => $noindex,
            'currentYear'    => date('Y'),
            'theme'          => $theme,
            'layout'         => "themes.{$theme}.layout",
        ];
    }

    protected function recordView(string $path): void
    {
        if (Setting::get('page_counter_enabled', '0') !== '1') {
            return;
        }
        $ip = Request::ip() ?? '0.0.0.0';
        PageView::create([
            'path'      => $path,
            'ip_hash'   => hash('sha256', $ip . config('app.key')),
            'viewed_at' => now(),
        ]);
    }

    protected function menuItems(string $location): array
    {
        $menu = \App\Models\Menu::with('translations')
            ->whereHas('translations')
            ->get()
            ->first(fn ($m) => $m->location?->value === $location);

        if (! $menu) {
            return [];
        }
        $translation = $menu->translation(app()->getLocale())
                    ?? $menu->translation('en')
                    ?? $menu->translations->first();
        return $translation?->items ?? [];
    }
}
