<?php

namespace Tests\Feature\Phase5;

use App\Mail\FormSubmissionNotification;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class NotificationTest extends TestCase
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
        Setting::set('contact_form_enabled', '1');
    }

    protected function tearDown(): void
    {
        @unlink(storage_path('installed'));
        parent::tearDown();
    }

    public function test_notification_email_sent_when_configured(): void
    {
        Mail::fake();
        Setting::set('notification_email', 'party@example.com');

        $this->post('/en/contact', [
            'name'    => 'Ibrahim Nour',
            'email'   => 'ibrahim@example.com',
            'subject' => 'Question',
            'message' => 'I have a question.',
            'consent' => '1',
        ])->assertRedirect();

        Mail::assertSent(FormSubmissionNotification::class, function ($mail) {
            return $mail->hasTo('party@example.com');
        });
    }

    public function test_no_email_sent_when_notification_email_not_configured(): void
    {
        Mail::fake();
        Setting::set('notification_email', ''); // empty — no notification

        $this->post('/en/contact', [
            'name'    => 'Sara Ahmed',
            'email'   => 'sara@example.com',
            'subject' => 'Hello',
            'message' => 'Just saying hello.',
            'consent' => '1',
        ])->assertRedirect();

        Mail::assertNothingSent();
    }
}
