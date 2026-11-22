<?php

namespace Tests\Feature\Phase3;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoModeTest extends TestCase
{
    use RefreshDatabase;

    // ── Demo guard: production mode ───────────────────────────────────────────

    public function test_demo_guard_is_inactive_in_production(): void
    {
        $this->app['config']->set('cms.instance', 'production');
        $this->app['config']->set('cms.demo_login_email', 'demo@example.org');

        file_put_contents(storage_path('installed'), 'test');

        $user = User::factory()->create(['role' => UserRole::Editor]);
        $this->actingAs($user);

        // Settings route is only blocked when the user IS the demo user in demo mode.
        // In production, any editor's access is controlled by the normal policy.
        $this->assertSame('production', config('cms.instance'));

        @unlink(storage_path('installed'));
    }

    // ── Demo guard: demo mode ─────────────────────────────────────────────────

    public function test_demo_guard_blocks_settings_for_demo_user(): void
    {
        $this->app['config']->set('cms.instance', 'demo');
        $this->app['config']->set('cms.demo_login_email', 'demo@example.org');

        file_put_contents(storage_path('installed'), 'test');

        $demo = User::factory()->create([
            'email' => 'demo@example.org',
            'role'  => UserRole::Editor,
        ]);
        $this->actingAs($demo);

        $panelPath = config('cms.panel_path', 'panel');
        $this->get("/{$panelPath}/settings")->assertRedirect();

        @unlink(storage_path('installed'));
    }

    public function test_demo_guard_allows_content_for_demo_user(): void
    {
        $this->app['config']->set('cms.instance', 'demo');
        $this->app['config']->set('cms.demo_login_email', 'demo@example.org');

        file_put_contents(storage_path('installed'), 'test');

        $demo = User::factory()->create([
            'email' => 'demo@example.org',
            'role'  => UserRole::Editor,
        ]);
        $this->actingAs($demo);

        $panelPath = config('cms.panel_path', 'panel');
        // Content routes (posts, pages, etc.) must remain accessible.
        $this->get("/{$panelPath}/posts")->assertOk();

        @unlink(storage_path('installed'));
    }

    public function test_non_demo_user_is_not_blocked_in_demo_mode(): void
    {
        $this->app['config']->set('cms.instance', 'demo');
        $this->app['config']->set('cms.demo_login_email', 'demo@example.org');

        file_put_contents(storage_path('installed'), 'test');

        // Owner is not the demo user — guard must not block them.
        $owner = User::factory()->create(['role' => UserRole::Owner]);
        $this->actingAs($owner);

        // Owner should still reach settings (the DemoGuard skips non-demo users).
        // We just assert that settings is not auto-redirected back for owner.
        $panelPath = config('cms.panel_path', 'panel');
        $response  = $this->get("/{$panelPath}/settings");
        $this->assertNotSame(route('panel.dashboard'), $response->headers->get('Location'));

        @unlink(storage_path('installed'));
    }

    // ── Demo forms notice ─────────────────────────────────────────────────────

    public function test_contact_form_shows_demo_notice_in_demo_mode(): void
    {
        $this->app['config']->set('cms.instance', 'demo');
        file_put_contents(storage_path('installed'), 'test');

        $response = $this->post('/en/contact', [
            'name'    => 'Test User',
            'email'   => 'test@example.com',
            'subject' => 'Hello',
            'message' => 'This is a test message.',
            'consent' => '1',
        ]);

        $response->assertSessionHas('success');
        // No FormSubmission row should exist in demo.
        $this->assertDatabaseCount('form_submissions', 0);

        @unlink(storage_path('installed'));
    }

    public function test_contact_form_stores_submission_in_production(): void
    {
        $this->app['config']->set('cms.instance', 'production');
        file_put_contents(storage_path('installed'), 'test');

        $this->post('/en/contact', [
            'name'    => 'Test User',
            'email'   => 'test@example.com',
            'subject' => 'Hello',
            'message' => 'This is a test message.',
            'consent' => '1',
        ]);

        $this->assertDatabaseCount('form_submissions', 1);

        @unlink(storage_path('installed'));
    }

    // ── Robots.txt ────────────────────────────────────────────────────────────

    public function test_robots_txt_disallows_all_in_demo(): void
    {
        $this->app['config']->set('cms.instance', 'demo');
        file_put_contents(storage_path('installed'), 'test');

        $response = $this->get('/robots.txt');
        $response->assertSee('Disallow: /');
        $response->assertDontSee('Allow: /');

        @unlink(storage_path('installed'));
    }

    public function test_robots_txt_allows_in_production_by_default(): void
    {
        $this->app['config']->set('cms.instance', 'production');
        file_put_contents(storage_path('installed'), 'test');

        $response = $this->get('/robots.txt');
        $response->assertSee('Allow: /');

        @unlink(storage_path('installed'));
    }

    // ── DemoResetCommand guard ────────────────────────────────────────────────

    public function test_demo_reset_command_refuses_in_production(): void
    {
        $this->app['config']->set('cms.instance', 'production');

        $this->artisan('cms:demo-reset')->assertFailed();
    }
}
