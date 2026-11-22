<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class InstallerLocaleMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $supported = config('cms.supported_locales', ['en', 'fr', 'pt', 'ar']);
        $locale    = session('installer_lang', 'en');

        if (in_array($locale, $supported, true)) {
            App::setLocale($locale);
        }

        return $next($request);
    }
}
