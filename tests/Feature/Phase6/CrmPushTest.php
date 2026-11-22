<?php

namespace Tests\Feature\Phase6;

use App\Jobs\PushSubmissionToCrmJob;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class CrmPushTest extends TestCase
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
        Setting::set('active_locales', 'en');
        Setting::set('join_form_enabled', '1');
    }

    protected function tearDown(): void
    {
        @unlink(storage_path('installed'));
        parent::tearDown();
    }

    private function validJoinData(): array
    {
        return [
            'name'       => 'Khalid Omar',
            'email'      => 'khalid@example.com',
            'party_role' => 'Member',
            'consent'    => '1',
        ];
    }

    public function test_push_job_dispatched_when_crm_enabled(): void
    {
        Queue::fake();
        Setting::set('crm_push_enabled', '1');
        Setting::set('crm_webhook_url', 'https://crm.example.com/hook');

        $this->post('/en/join', $this->validJoinData());

        Queue::assertPushed(PushSubmissionToCrmJob::class);
    }

    public function test_no_push_when_crm_disabled(): void
    {
        Queue::fake();
        Setting::set('crm_push_enabled', '0');

        $this->post('/en/join', $this->validJoinData());

        Queue::assertNotPushed(PushSubmissionToCrmJob::class);
    }

    public function test_push_sends_correct_payload_structure(): void
    {
        Http::fake(['https://crm.example.com/*' => Http::response('ok', 200)]);
        Setting::set('crm_push_enabled', '1');
        Setting::set('crm_webhook_url', 'https://crm.example.com/hook');
        Setting::set('crm_webhook_secret', Crypt::encryptString('test-secret'));

        $this->post('/en/join', $this->validJoinData());

        Http::assertSent(function ($request) {
            $body = json_decode($request->body(), true);
            return $body['event'] === 'join_submission'
                && $body['email'] === 'khalid@example.com'
                && $body['name'] === 'Khalid Omar'
                && $body['party_role'] === 'Member';
        });
    }

    public function test_push_includes_signature_header(): void
    {
        Http::fake(['https://crm.example.com/*' => Http::response('ok', 200)]);
        Setting::set('crm_push_enabled', '1');
        Setting::set('crm_webhook_url', 'https://crm.example.com/hook');
        Setting::set('crm_webhook_secret', Crypt::encryptString('test-secret'));

        $this->post('/en/join', $this->validJoinData());

        Http::assertSent(function ($request) {
            return str_starts_with($request->header('X-CMS-Signature')[0] ?? '', 'sha256=');
        });
    }

    public function test_push_failure_does_not_bubble_up(): void
    {
        Http::fake(['*' => fn() => throw new \Exception('Connection refused')]);
        Setting::set('crm_push_enabled', '1');
        Setting::set('crm_webhook_url', 'https://crm.example.com/hook');
        Setting::set('crm_webhook_secret', Crypt::encryptString('test-secret'));

        // Should not throw — submit still returns a redirect
        $this->post('/en/join', $this->validJoinData())->assertRedirect();
    }
}
