<?php

namespace Tests\Feature\Phase6;

use App\Enums\UserRole;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

class SsoTest extends TestCase
{
    use RefreshDatabase;

    private function markInstalled(): void
    {
        file_put_contents(storage_path('installed'), 'test');
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->markInstalled();
    }

    protected function tearDown(): void
    {
        @unlink(storage_path('installed'));
        parent::tearDown();
    }

    private function makeToken(array $overrides = []): array
    {
        $secret  = 'sso-test-secret';
        $payload = array_merge([
            'email' => 'sso-user@example.com',
            'name'  => 'SSO User',
            'role'  => 'editor',
            'exp'   => time() + 60,
        ], $overrides);

        $token = base64_encode(json_encode($payload));
        $sig   = hash_hmac('sha256', $token, $secret);

        return compact('token', 'sig', 'secret');
    }

    public function test_sso_returns_403_when_disabled(): void
    {
        Setting::set('crm_sso_enabled', '0');
        ['token' => $token, 'sig' => $sig] = $this->makeToken();

        $this->get("/panel/sso?token={$token}&sig={$sig}")->assertStatus(403);
    }

    public function test_valid_sso_token_logs_in_and_redirects(): void
    {
        ['token' => $token, 'sig' => $sig, 'secret' => $secret] = $this->makeToken();
        Setting::set('crm_sso_enabled', '1');
        Setting::set('crm_sso_secret', Crypt::encryptString($secret));

        $this->get("/panel/sso?token={$token}&sig={$sig}")
             ->assertRedirect(route('panel.dashboard'));

        $this->assertAuthenticated();
    }

    public function test_expired_token_returns_403(): void
    {
        ['token' => $token, 'sig' => $sig, 'secret' => $secret] = $this->makeToken(['exp' => time() - 1]);
        Setting::set('crm_sso_enabled', '1');
        Setting::set('crm_sso_secret', Crypt::encryptString($secret));

        $this->get("/panel/sso?token={$token}&sig={$sig}")->assertStatus(403);
    }

    public function test_invalid_signature_returns_403(): void
    {
        ['token' => $token, 'secret' => $secret] = $this->makeToken();
        Setting::set('crm_sso_enabled', '1');
        Setting::set('crm_sso_secret', Crypt::encryptString($secret));

        $this->get("/panel/sso?token={$token}&sig=invalidsig")->assertStatus(403);
    }

    public function test_missing_params_returns_400(): void
    {
        Setting::set('crm_sso_enabled', '1');
        Setting::set('crm_sso_secret', Crypt::encryptString('some-secret'));

        $this->get('/panel/sso')->assertStatus(400);
    }

    public function test_sso_creates_new_user_if_not_found(): void
    {
        ['token' => $token, 'sig' => $sig, 'secret' => $secret] = $this->makeToken([
            'email' => 'brand-new@example.com',
        ]);
        Setting::set('crm_sso_enabled', '1');
        Setting::set('crm_sso_secret', Crypt::encryptString($secret));

        $this->assertDatabaseMissing('users', ['email' => 'brand-new@example.com']);

        $this->get("/panel/sso?token={$token}&sig={$sig}")
             ->assertRedirect(route('panel.dashboard'));

        $this->assertDatabaseHas('users', ['email' => 'brand-new@example.com']);
    }
}
