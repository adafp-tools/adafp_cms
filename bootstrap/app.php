<?php

use App\Http\Middleware\DemoNoindex;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: [
            __DIR__ . '/../routes/web.php',
            __DIR__ . '/../routes/installer.php',
            __DIR__ . '/../routes/panel.php',
        ],
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Apply security headers to every response.
        $middleware->append(SecurityHeaders::class);

        // Redirect unauthenticated panel users to the panel login.
        $middleware->redirectGuestsTo(fn ($request) => route('panel.login'));

        // In demo mode, force noindex on every response.
        $middleware->append(DemoNoindex::class);

        // In demo mode, block account-mutation routes (Fortify + panel).
        $middleware->appendToGroup('web', \App\Http\Middleware\DemoGuard::class);

        // Ensure EnsureInstalled runs before Authenticate in the priority sort.
        // Laravel's priority sort moves Authenticate before SubstituteBindings (from the
        // web group), which would inadvertently place it before our custom middleware.
        // Adding EnsureInstalled to the priority list before Authenticate fixes this.
        $middleware->prependToPriorityList(
            \Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests::class,
            \App\Http\Middleware\EnsureInstalled::class,
        );

        // Named middleware aliases
        $middleware->alias([
            'installed'      => \App\Http\Middleware\EnsureInstalled::class,
            'not.installed'  => \App\Http\Middleware\EnsureNotInstalled::class,
            'require.2fa'    => \App\Http\Middleware\RequireTwoFactor::class,
            'panel.noindex'  => \App\Http\Middleware\PanelNoindex::class,
            'locale'         => \App\Http\Middleware\LocaleMiddleware::class,
            'page.cache'     => \App\Http\Middleware\PublicPageCache::class,
            'demo.guard'     => \App\Http\Middleware\DemoGuard::class,
            'demo.noindex'   => \App\Http\Middleware\DemoNoIndex::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
