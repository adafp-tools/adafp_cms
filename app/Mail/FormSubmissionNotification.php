<?php

namespace App\Mail;

use App\Models\FormSubmission;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Queue\SerializesModels;

class FormSubmissionNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public FormSubmission $submission)
    {
    }

    public function envelope(): Envelope
    {
        $typeLabel = $this->submission->form_type === 'join' ? 'Join' : 'Contact';
        $fromName  = Setting::get('notification_from_name', config('cms.product_name'));
        $fromEmail = Setting::get('notification_from_email', config('mail.from.address', 'noreply@example.com'));

        return new Envelope(
            from: new Address($fromEmail, $fromName),
            subject: "[{$typeLabel}] {$this->submission->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.form-submission',
            with: ['submission' => $this->submission],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
