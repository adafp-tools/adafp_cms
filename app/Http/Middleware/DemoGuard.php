<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * In demo mode, block destructive panel actions on the demo admin account:
 * - password changes
 * - email changes
 * - account deletion
 * - new user creation
 *
 * Returns a 403 JSON/redirect with a clear demo message so the UI shows it.
 * Only active when CMS_INSTANCE=demo.
 */
class DemoGuard
{
    // Routes that mutate the demo account or user list (actual route names from routes/panel.php + Fortify)
    private const BLOCKED_ROUTES = [
        'user-profile-information',  // PUT (Fortify) — update name/email
        'user-password',             // PUT (Fortify) — change password
        'panel.users.invite',        // POST — invite new user
        'panel.users.destroy',       // DELETE — delete user
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (config('cms.instance') !== 'demo') {
            return $next($request);
        }

        $routeName = $request->route()?->getName() ?? '';

        if (in_array($routeName, self::BLOCKED_ROUTES, true)) {
            $message = 'This action is disabled on the demo site.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 403);
            }

            return back()->withErrors(['demo' => $message]);
        }

        return $next($request);
    }
}
