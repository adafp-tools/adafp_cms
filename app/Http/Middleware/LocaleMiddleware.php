<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class LocaleMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->route('locale');

        $supported  = config('cms.supported_locales', ['en']);
        $activeStr  = Setting::get('active_locales', 'en');
        $active     = array_map('trim', explode(',', (string) $activeStr));

        if (! in_array($locale, $supported, true) || ! in_array($locale, $active, true)) {
            abort(404);
        }

        App::setLocale($locale);

        // Maintenance mode — redirect to maintenance view unless authenticated panel user
        if (Setting::get('maintenance_mode', '0') === '1') {
            $user = Auth::user();
            $panelPath = config('cms.panel_path', 'panel');
            $isPanelUser = $user && $request->is($panelPath . '/*', $panelPath);

            if (! $isPanelUser) {
                $theme  = Setting::get('theme', 'classic');
                $siteName = Setting::get('site_name', config('cms.product_name'));
                return response(
                    view("themes.{$theme}.maintenance", compact('locale', 'siteName')),
                    503,
                );
            }
        }

        return $next($request);
    }
}
