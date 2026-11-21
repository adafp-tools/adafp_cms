<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        // Initialise per-request embed hosts list
        $request->attributes->set('csp_embed_hosts', []);

        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'same-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');

        // Collect embed hosts added during this request
        $embedHosts = $request->attributes->get('csp_embed_hosts', []);

        $frameSrc = "'self'";
        if (! empty($embedHosts)) {
            $frameSrc .= ' ' . implode(' ', array_unique($embedHosts));
        }

        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline'",
            "style-src 'self' 'unsafe-inline'",
            "font-src 'self'",
            "img-src 'self' data:" . (! empty($embedHosts) ? ' ' . implode(' ', array_unique($embedHosts)) : ''),
            "connect-src 'self'",
            "form-action 'self'",
            "frame-ancestors 'self'",
            "frame-src {$frameSrc}",
            "base-uri 'self'",
            "object-src 'none'",
        ]);
        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }

    /**
     * Add an embed host to the per-request CSP allowlist.
     * Called by EmbedHelper when transforming body HTML.
     */
    public static function addEmbedHost(Request $request, string $host): void
    {
        $hosts   = $request->attributes->get('csp_embed_hosts', []);
        $hosts[] = $host;
        $request->attributes->set('csp_embed_hosts', $hosts);
    }
}
