<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireTwoFactor
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('panel.login');
        }

        if ($user->requires2FA() && ! $user->twoFactorEnabled()) {
            return redirect()->route('panel.two-factor.setup')
                ->with('warning', 'Your role requires two-factor authentication. Please set it up to continue.');
        }

        $user->touchActivity();

        return $next($request);
    }
}
