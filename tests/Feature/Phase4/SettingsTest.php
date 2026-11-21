<?php

namespace Tests\Feature\Phase4;

use App\Enums\UserRole;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SettingsTest extends TestCase
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

    private function ownerWith2fa(): User
    {
        return User::factory()->create([
            'role'                    => UserRole::Owner,
            'two_factor_confirmed_at' => now(),
        ]);
    }

    public function test_owner_can_update_settings(): void
    {
        $this->markInstalled();
        $owner = $this->ownerWith2fa();

        $response = $this->actingAs($owner)->post(route('panel.settings.update'), [
            'theme'                => 'campaign',
            'primary_color'        => '#1e3a5f',
            'secondary_color'      => '#c0392b',
            'text_on_primary'      => '#ffffff',
            'site_name'            => 'Test Party',
            'default_locale'       => 'en',
            'active_locales'       => 'en,ar',
            'maintenance_mode'     => '0',
            'noindex'              => '0',
            'page_counter_enabled' => '0',
        ]);

        $response->assertRedirect(route('panel.settings.index'));
        $this->assertEquals('campaign', Setting::get('theme'));
        $this->assertEquals('Test Party', Setting::get('site_name'));
        $this->assertEquals('en,ar', Setting::get('active_locales'));
    }

    public function test_wcag_contrast_warning_for_low_contrast_colors(): void
    {
        $this->markInstalled();
        $owner = $this->ownerWith2fa();

        // White on white — contrast ratio 1:1 (fails WCAG AA)
        $response = $this->actingAs($owner)->post(route('panel.settings.update'), [
            'theme'                => 'classic',
            'primary_color'        => '#ffffff',
            'secondary_color'      => '#eeeeee',
            'text_on_primary'      => '#ffffff',
            'site_name'            => 'Test Party',
            'default_locale'       => 'en',
            'active_locales'       => 'en',
            'maintenance_mode'     => '0',
            'noindex'              => '0',
            'page_counter_enabled' => '0',
        ]);

        // Settings are saved despite warning
        $response->assertRedirect(route('panel.settings.index'));
        $flash = $response->getSession()->get('success', '');
        $this->assertStringContainsString('Warning', $flash);
    }

    public function test_logo_upload_accepted(): void
    {
        $this->markInstalled();
        Storage::fake('public');
        $owner = $this->ownerWith2fa();

        $response = $this->actingAs($owner)->post(route('panel.settings.logo'), [
            'logo' => UploadedFile::fake()->image('logo.jpg', 200, 200),
        ]);

        $response->assertRedirect(route('panel.settings.index'));
        $this->assertNotEmpty(Setting::get('logo_path'));
    }
}
