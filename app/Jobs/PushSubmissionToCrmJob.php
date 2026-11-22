<?php

namespace App\Jobs;

use App\Models\FormSubmission;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PushSubmissionToCrmJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public readonly FormSubmission $submission) {}

    public function handle(): void
    {
        if (Setting::get('crm_push_enabled', '0') !== '1') {
            return;
        }

        $url = Setting::get('crm_webhook_url', '');
        if (empty($url)) {
            return;
        }

        try {
            $rawSecret = Setting::get('crm_webhook_secret', '');
            $secret    = $rawSecret ? Crypt::decryptString($rawSecret) : '';
        } catch (\Illuminate\Contracts\Encryption\DecryptException) {
            Log::warning('CRM push: could not decrypt crm_webhook_secret — skipping');
            return;
        }

        $sub = $this->submission;

        $payload = json_encode([
            'event'              => 'join_submission',
            'cms_submission_id'  => $sub->id,
            'submitted_at'       => $sub->created_at?->toIso8601String(),
            'locale'             => $sub->locale,
            'name'               => $sub->name,
            'email'              => $sub->email,
            'phone'              => $sub->phone,
            'dob'                => $sub->dob?->toDateString(),
            'address'            => $sub->address,
            'party_role'         => $sub->party_role,
        ], JSON_UNESCAPED_UNICODE);

        $sig = 'sha256=' . hash_hmac('sha256', $payload, $secret);

        try {
            Http::withHeaders([
                'Content-Type'    => 'application/json',
                'X-CMS-Signature' => $sig,
            ])->withBody($payload, 'application/json')->post($url);
        } catch (\Throwable $e) {
            Log::warning('CRM push failed: ' . $e->getMessage(), [
                'submission_id' => $sub->id,
            ]);
        }
    }
}
