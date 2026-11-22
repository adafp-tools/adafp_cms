<?php

namespace Tests\Feature\Phase7;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BackupPanelTest extends TestCase
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

    public function test_owner_can_access_backups_panel(): void
    {
        $owner = User::factory()->create([
            'role'                    => UserRole::Owner,
            'two_factor_confirmed_at' => now(),
        ]);
        $this->actingAs($owner)->get('/panel/backups')->assertStatus(200);
    }

    public function test_editor_cannot_access_backups_panel(): void
    {
        $editor = User::factory()->create(['role' => UserRole::Editor]);
        $this->actingAs($editor)->get('/panel/backups')->assertStatus(403);
    }

    public function test_download_with_path_traversal_returns_404(): void
    {
        $owner = User::factory()->create([
            'role'                    => UserRole::Owner,
            'two_factor_confirmed_at' => now(),
        ]);
        $this->actingAs($owner)
             ->get('/panel/backups/download/../../etc/passwd')
             ->assertStatus(404);
    }

    public function test_download_with_disallowed_extension_returns_404(): void
    {
        $owner = User::factory()->create([
            'role'                    => UserRole::Owner,
            'two_factor_confirmed_at' => now(),
        ]);
        $this->actingAs($owner)
             ->get('/panel/backups/download/evil.php')
             ->assertStatus(404);
    }

    public function test_backup_command_succeeds_on_sqlite(): void
    {
        // In testing, DB_CONNECTION=sqlite — mysqldump is skipped, should still return SUCCESS
        $this->artisan('cms:backup')
             ->assertSuccessful()
             ->expectsOutputToContain('Backup complete');
    }

    public function test_release_command_skip_build_creates_zip(): void
    {
        $releasesDir = storage_path('releases');

        $this->artisan('cms:release', ['--skip-build' => true, '--skip-tests' => true])
             ->assertSuccessful();

        // A ZIP file should have been created
        $zips = glob("$releasesDir/cms-*.zip");
        $this->assertNotEmpty($zips, 'Release ZIP was not created.');

        // Clean up
        foreach ($zips as $zip) {
            @unlink($zip);
        }
    }
}
