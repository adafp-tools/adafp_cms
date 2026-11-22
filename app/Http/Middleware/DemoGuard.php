<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DemoGuard
{
    /**
     * Panel route names that the demo account cannot access.
     * These cover settings, users, backups, health, and 2FA management.
     */
    private const BLOCKED_ROUTES = [
        'panel.settings.index',
        'panel.settings.update',
        'panel.settings.logo',
        'panel.users.index',
        'panel.users.invite',
        'panel.users.destroy',
        'panel.backups.index',
        'panel.backups.run',
        'panel.backups.download',
        'panel.backups.destroy',
        'panel.health',
        'panel.health.mail-test',
        'panel.two-factor.disable',
        'panel.two-factor.regenerate',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (config('cms.instance') !== 'demo') {
            return $next($request);
        }

        $user      = $request->user();
        $demoEmail = config('cms.demo_login_email', '');

        if (! $user || ! $demoEmail || $user->email !== $demoEmail) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();

        if ($routeName && in_array($routeName, self::BLOCKED_ROUTES, true)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => __('panel.demo_blocked')], 403);
            }

            return redirect()
                ->route('panel.dashboard')
                ->with('warning', __('panel.demo_blocked'));
        }

        return $next($request);
    }
}
