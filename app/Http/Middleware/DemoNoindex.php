<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Forces X-Robots-Tag: noindex, nofollow on all responses when the site
 * is running in demo mode (CMS_INSTANCE=demo), regardless of the noindex
 * setting in the database.
 */
class DemoNoindex
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (config('cms.instance') === 'demo') {
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
        }

        return $response;
    }
}
