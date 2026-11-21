<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class PublicPageCache
{
    private const PREFIX = 'page-cache-';
    private const TTL    = 86400; // 24 hours in seconds

    public function handle(Request $request, Closure $next): Response
    {
        // Skip caching in tests, for authenticated users, non-GET, or preview mode
        if (
            app()->environment('testing') ||
            $request->user() ||
            $request->method() !== 'GET' ||
            $request->has('preview')
        ) {
            return $next($request);
        }

        $key = self::cacheKey($request->fullUrl());

        if ($cached = Cache::get($key)) {
            return response($cached, 200)
                ->header('Content-Type', 'text/html; charset=utf-8')
                ->header('Cache-Control', 'public, max-age=3600')
                ->header('X-Cache', 'HIT');
        }

        $response = $next($request);

        if ($response->getStatusCode() === 200) {
            Cache::put($key, $response->getContent(), self::TTL);
            $response->headers->set('Cache-Control', 'public, max-age=3600');
            $response->headers->set('X-Cache', 'MISS');
        }

        return $response;
    }

    public static function forget(string $url): void
    {
        Cache::forget(self::cacheKey($url));
    }

    public static function flush(): void
    {
        // Laravel file/array cache doesn't natively support tag-based flushing,
        // so we flush the entire cache store. Acceptable for a CMS with low
        // publish frequency.
        Cache::flush();
    }

    private static function cacheKey(string $url): string
    {
        return self::PREFIX . md5($url);
    }
}
