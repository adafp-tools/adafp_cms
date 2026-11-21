<?php

namespace Tests\Feature\Phase2;

use App\Services\InstallerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstallerTest extends TestCase
{
    use RefreshDatabase;

    public function test_installer_is_accessible_when_not_installed(): void
    {
        $this->assertFalse(app(InstallerService::class)->isInstalled());
        $response = $this->get('/install/requirements');
        $response->assertStatus(200);
    }

    public function test_installer_is_blocked_when_installed(): void
    {
        file_put_contents(storage_path('installed'), 'test');
        $this->get('/install/requirements')->assertNotFound();
        @unlink(storage_path('installed'));
    }

    public function test_panel_redirects_to_installer_when_not_installed(): void
    {
        $panelPath = config('cms.panel_path', 'panel');
        $this->get("/{$panelPath}")->assertRedirect(route('installer.index'));
    }

    public function test_requirements_page_shows_php_version(): void
    {
        $response = $this->get('/install/requirements');
        $response->assertSee('PHP version');
    }

    public function test_database_test_rejects_bad_credentials(): void
    {
        $response = $this->postJson('/install/database/test', [
            'db_host'     => '127.0.0.1',
            'db_port'     => 3306,
            'db_database' => 'nonexistent_db_xyz',
            'db_username' => 'nobody',
            'db_password' => 'wrongpassword',
        ]);
        $response->assertJson(['success' => false]);
    }

    public function test_site_step_requires_prior_db_session(): void
    {
        $this->get('/install/site')->assertRedirect(route('installer.database'));
    }

    public function test_owner_step_requires_prior_site_session(): void
    {
        $this->get('/install/owner')->assertRedirect(route('installer.site'));
    }

    public function test_installer_service_marks_installed(): void
    {
        $svc = app(InstallerService::class);
        $this->assertFalse($svc->isInstalled());
        $svc->markInstalled();
        $this->assertTrue($svc->isInstalled());
        @unlink(storage_path('installed'));
    }

    public function test_installer_checks_requirements_structure(): void
    {
        $checks = app(InstallerService::class)->checkRequirements();
        $this->assertArrayHasKey('php_version', $checks);
        $this->assertArrayHasKey('storage_writable', $checks);
        $this->assertArrayHasKey('bootstrap_cache_writable', $checks);
        foreach ($checks as $check) {
            $this->assertArrayHasKey('label', $check);
            $this->assertArrayHasKey('pass', $check);
        }
    }
}
