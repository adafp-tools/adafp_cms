<?php

namespace Tests\Feature\Phase5;

use App\Enums\UserRole;
use App\Models\FormSubmission;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class FormsTest extends TestCase
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
        Setting::set('active_locales', 'en,ar');
        Setting::set('contact_form_enabled', '1');
        Setting::set('join_form_enabled', '1');
    }

    protected function tearDown(): void
    {
        @unlink(storage_path('installed'));
        parent::tearDown();
    }

    public function test_contact_page_loads(): void
    {
        $this->get('/en/contact')->assertStatus(200);
    }

    public function test_contact_submission_stores_and_redirects(): void
    {
        $this->post('/en/contact', [
            'name'    => 'Ali Hassan',
            'email'   => 'ali@example.com',
            'subject' => 'Test subject',
            'message' => 'Hello from the form.',
            'consent' => '1',
        ])->assertRedirect();

        $this->assertDatabaseHas('form_submissions', [
            'form_type' => 'contact',
            'email'     => 'ali@example.com',
            'locale'    => 'en',
        ]);
    }

    public function test_contact_submission_requires_consent(): void
    {
        $this->post('/en/contact', [
            'name'    => 'Ali Hassan',
            'email'   => 'ali@example.com',
            'subject' => 'Test',
            'message' => 'Hello.',
            // consent omitted
        ])->assertSessionHasErrors('consent');

        $this->assertDatabaseCount('form_submissions', 0);
    }

    public function test_honeypot_silently_discards_submission(): void
    {
        $this->post('/en/contact', [
            'name'    => 'Bot',
            'email'   => 'bot@example.com',
            'subject' => 'Spam',
            'message' => 'Buy now!',
            'consent' => '1',
            'website' => 'http://spam.example.com', // honeypot field
        ])->assertRedirect();

        // No real record stored
        $this->assertDatabaseCount('form_submissions', 0);
    }

    public function test_contact_rate_limited_after_three_attempts(): void
    {
        $validData = [
            'name'    => 'User',
            'email'   => 'user@example.com',
            'subject' => 'Hi',
            'message' => 'Message.',
            'consent' => '1',
        ];

        // Three successful submissions
        for ($i = 0; $i < 3; $i++) {
            $this->post('/en/contact', $validData);
        }

        // Fourth attempt should be rate-limited
        $response = $this->post('/en/contact', $validData);
        $response->assertSessionHasErrors('email');
    }

    public function test_join_page_loads(): void
    {
        $this->get('/en/join')->assertStatus(200);
    }

    public function test_join_submission_stores(): void
    {
        $this->post('/en/join', [
            'name'       => 'Fatima Al-Nour',
            'email'      => 'fatima@example.com',
            'party_role' => 'Member',
            'consent'    => '1',
        ])->assertRedirect();

        $this->assertDatabaseHas('form_submissions', [
            'form_type'  => 'join',
            'email'      => 'fatima@example.com',
            'party_role' => 'Member',
        ]);
    }

    public function test_join_page_shows_disabled_message_when_disabled(): void
    {
        Setting::set('join_form_enabled', '0');

        $this->get('/en/join')->assertStatus(200)->assertSee('unavailable', false);
    }

    public function test_owner_can_view_submissions_panel(): void
    {
        $owner = User::factory()->create(['role' => UserRole::Owner, 'two_factor_confirmed_at' => now()]);
        $this->actingAs($owner)->get('/panel/submissions')->assertStatus(200);
    }

    public function test_owner_can_mark_submission_read(): void
    {
        $owner = User::factory()->create(['role' => UserRole::Owner, 'two_factor_confirmed_at' => now()]);

        $submission = FormSubmission::create([
            'form_type'  => 'contact',
            'locale'     => 'en',
            'name'       => 'Test',
            'email'      => 'test@example.com',
            'consent'    => true,
            'consent_at' => now(),
            'ip_hash'    => 'testhash',
        ]);

        $this->actingAs($owner)
             ->post("/panel/submissions/{$submission->id}/read")
             ->assertRedirect();

        $this->assertNotNull($submission->fresh()->read_at);
    }

    public function test_owner_can_soft_delete_submission(): void
    {
        $owner = User::factory()->create(['role' => UserRole::Owner, 'two_factor_confirmed_at' => now()]);

        $submission = FormSubmission::create([
            'form_type'  => 'contact',
            'locale'     => 'en',
            'name'       => 'Test',
            'email'      => 'del@example.com',
            'consent'    => true,
            'consent_at' => now(),
            'ip_hash'    => 'testhash2',
        ]);

        $this->actingAs($owner)
             ->delete("/panel/submissions/{$submission->id}")
             ->assertRedirect();

        $this->assertSoftDeleted('form_submissions', ['id' => $submission->id]);
    }

    public function test_editor_cannot_access_submissions_panel(): void
    {
        $editor = User::factory()->create(['role' => UserRole::Editor]);
        $this->actingAs($editor)->get('/panel/submissions')->assertStatus(403);
    }

    public function test_csv_export_returns_correct_content_type(): void
    {
        $owner = User::factory()->create(['role' => UserRole::Owner, 'two_factor_confirmed_at' => now()]);
        $this->actingAs($owner)
             ->post('/panel/submissions/export')
             ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_gdpr_erase_command_deletes_old_soft_deleted_records(): void
    {
        // Create a submission that was soft-deleted > 90 days ago
        $old = FormSubmission::create([
            'form_type'  => 'contact',
            'locale'     => 'en',
            'name'       => 'Old',
            'email'      => 'old@example.com',
            'consent'    => true,
            'consent_at' => now()->subDays(100),
            'ip_hash'    => 'oldhash',
        ]);
        $old->delete();
        // Manually backdate deleted_at
        FormSubmission::withTrashed()->where('id', $old->id)
            ->update(['deleted_at' => now()->subDays(100)]);

        // Create a recently soft-deleted record (should NOT be purged)
        $recent = FormSubmission::create([
            'form_type'  => 'contact',
            'locale'     => 'en',
            'name'       => 'Recent',
            'email'      => 'recent@example.com',
            'consent'    => true,
            'consent_at' => now(),
            'ip_hash'    => 'recenthash',
        ]);
        $recent->delete();

        $this->artisan('cms:gdpr-erase')->assertSuccessful();

        // Old record is permanently gone
        $this->assertDatabaseMissing('form_submissions', ['email' => 'old@example.com']);

        // Recent record still present (soft-deleted)
        $this->assertSoftDeleted('form_submissions', ['email' => 'recent@example.com']);
    }
}
