<?php

namespace Tests\Feature;

use App\Http\Middleware\DemoGuard;
use App\Http\Middleware\DemoNoindex;
use App\Listeners\DemoMailFilter;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Mail\Events\MessageSending;
use Symfony\Component\Mime\Email as MimeEmail;
use Tests\TestCase;

class DemoModeTest extends TestCase
{
    // -------------------------------------------------------------------------
    // DemoNoindex middleware
    // -------------------------------------------------------------------------

    public function test_demo_noindex_sets_header_in_demo_mode(): void
    {
        config(['cms.instance' => 'demo']);

        $request  = Request::create('/');
        $response = new Response('ok');
        $middleware = new DemoNoindex();

        $result = $middleware->handle($request, fn () => $response);

        $this->assertEquals('noindex, nofollow', $result->headers->get('X-Robots-Tag'));
    }

    public function test_demo_noindex_does_not_set_header_in_production(): void
    {
        config(['cms.instance' => 'production']);

        $request  = Request::create('/');
        $response = new Response('ok');
        $middleware = new DemoNoindex();

        $result = $middleware->handle($request, fn () => $response);

        $this->assertNull($result->headers->get('X-Robots-Tag'));
    }

    // -------------------------------------------------------------------------
    // DemoGuard middleware
    // -------------------------------------------------------------------------

    public function test_demo_guard_blocks_blocked_route_in_demo_mode(): void
    {
        config(['cms.instance' => 'demo']);

        $request = Request::create('/user/password', 'PUT');
        // Simulate a named route on the request.
        $route = new \Illuminate\Routing\Route('PUT', '/user/password', []);
        $route->name('user-password');
        $request->setRouteResolver(fn () => $route);

        $middleware = new DemoGuard();
        $response   = $middleware->handle($request, fn () => new Response('ok'));

        // Should be a redirect back with errors (or 302)
        $this->assertNotEquals(200, $response->getStatusCode());
    }

    public function test_demo_guard_allows_safe_route_in_demo_mode(): void
    {
        config(['cms.instance' => 'demo']);

        $request = Request::create('/panel', 'GET');
        $route   = new \Illuminate\Routing\Route('GET', '/panel', []);
        $route->name('panel.dashboard');
        $request->setRouteResolver(fn () => $route);

        $middleware = new DemoGuard();
        $response   = $middleware->handle($request, fn () => new Response('allowed'));

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_demo_guard_does_nothing_in_production(): void
    {
        config(['cms.instance' => 'production']);

        $request = Request::create('/user/password', 'PUT');
        $route   = new \Illuminate\Routing\Route('PUT', '/user/password', []);
        $route->name('user-password');
        $request->setRouteResolver(fn () => $route);

        $middleware = new DemoGuard();
        $response   = $middleware->handle($request, fn () => new Response('allowed'));

        $this->assertEquals(200, $response->getStatusCode());
    }

    // -------------------------------------------------------------------------
    // DemoMailFilter listener
    // -------------------------------------------------------------------------

    public function test_mail_filter_suppresses_all_mail_when_allowlist_empty(): void
    {
        config(['cms.instance' => 'demo', 'cms.demo_mail_allowlist' => []]);

        $mime = new MimeEmail();
        $mime->to('anyone@example.com')->subject('Test')->text('body');
        $event = new MessageSending($mime, []);

        $listener = new DemoMailFilter();
        $result   = $listener->handle($event);

        $this->assertFalse($result);
    }

    public function test_mail_filter_allows_mail_to_listed_address(): void
    {
        config(['cms.instance' => 'demo', 'cms.demo_mail_allowlist' => ['allowed@example.com']]);

        $mime = new MimeEmail();
        $mime->to('allowed@example.com')->subject('Test')->text('body');
        $event = new MessageSending($mime, []);

        $listener = new DemoMailFilter();
        $result   = $listener->handle($event);

        $this->assertNull($result);  // null = proceed normally
    }

    public function test_mail_filter_suppresses_mail_to_unlisted_address(): void
    {
        config(['cms.instance' => 'demo', 'cms.demo_mail_allowlist' => ['allowed@example.com']]);

        $mime = new MimeEmail();
        $mime->to('unlisted@evil.com')->subject('Test')->text('body');
        $event = new MessageSending($mime, []);

        $listener = new DemoMailFilter();
        $result   = $listener->handle($event);

        $this->assertFalse($result);
    }

    public function test_mail_filter_does_nothing_in_production(): void
    {
        config(['cms.instance' => 'production']);

        $mime = new MimeEmail();
        $mime->to('anyone@example.com')->subject('Test')->text('body');
        $event = new MessageSending($mime, []);

        $listener = new DemoMailFilter();
        $result   = $listener->handle($event);

        $this->assertNull($result);
    }

    // -------------------------------------------------------------------------
    // No ADAFP string in demo mode files
    // -------------------------------------------------------------------------

    public function test_no_adafp_string_in_demo_middleware(): void
    {
        $files = [
            app_path('Http/Middleware/DemoGuard.php'),
            app_path('Http/Middleware/DemoNoindex.php'),
            app_path('Console/Commands/DemoResetCommand.php'),
            app_path('Listeners/DemoMailFilter.php'),
        ];

        foreach ($files as $path) {
            $content = file_get_contents($path);
            $this->assertStringNotContainsStringIgnoringCase(
                'adafp',
                $content,
                "Found 'adafp' in {$path}"
            );
        }
    }
}
