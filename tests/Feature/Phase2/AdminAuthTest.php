<?php

namespace Tests\Feature\Phase2;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    private function markInstalled(): void
    {
        file_put_contents(storage_path('installed'), 'test');
    }

    protected function tearDown(): void
    {
        @unlink(storage_path('installed'));
        parent::tearDown();
    }

    public function test_login_page_loads_when_installed(): void
    {
        $this->markInstalled();
        $this->get('/panel/login')->assertStatus(200)->assertSee('Sign in');
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $this->markInstalled();
        User::factory()->create(['email' => 'test@example.com', 'role' => UserRole::Editor]);
        $this->post('/panel/login', ['email' => 'test@example.com', 'password' => 'wrong'])
             ->assertSessionHasErrors('email');
    }

    public function test_editor_authenticated_can_access_dashboard(): void
    {
        $this->markInstalled();
        $user = User::factory()->create(['role' => UserRole::Editor]);
        $this->actingAs($user)->get('/panel')->assertOk();
    }

    public function test_rate_limit_blocks_after_five_attempts(): void
    {
        $this->markInstalled();
        User::factory()->create(['email' => 'rl@example.com', 'role' => UserRole::Editor]);
        for ($i = 0; $i < 5; $i++) {
            $this->post('/panel/login', ['email' => 'rl@example.com', 'password' => 'wrong']);
        }
        $response = $this->post('/panel/login', ['email' => 'rl@example.com', 'password' => 'wrong']);
        $response->assertSessionHasErrors('email');
    }

    public function test_owner_without_2fa_redirected_to_setup(): void
    {
        $this->markInstalled();
        $owner = User::factory()->create([
            'role'                   => UserRole::Owner,
            'two_factor_confirmed_at' => null,
        ]);
        $this->actingAs($owner)
             ->get('/panel')
             ->assertRedirect(route('panel.two-factor.setup'));
    }

    public function test_editor_without_2fa_can_access_dashboard(): void
    {
        $this->markInstalled();
        $editor = User::factory()->create(['role' => UserRole::Editor]);
        $this->actingAs($editor)
             ->get('/panel')
             ->assertOk();
    }

    public function test_logout_destroys_session(): void
    {
        $this->markInstalled();
        $user = User::factory()->create(['role' => UserRole::Editor]);
        $this->actingAs($user)->post('/panel/logout')
             ->assertRedirect(route('panel.login'));
        $this->assertGuest();
    }

    public function test_security_headers_present(): void
    {
        $this->markInstalled();
        $response = $this->get('/panel/login');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('Referrer-Policy', 'same-origin');
        $response->assertHeader('Content-Security-Policy');
    }
}
