<?php

namespace App\Listeners;

use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\Log;

class FilterDemoMailRecipients
{
    public function handle(MessageSending $event): bool
    {
        $allowlist = config('cms.demo_mail_allowlist', []);

        // If the allowlist is empty, cancel all outgoing mail.
        if (empty($allowlist)) {
            Log::info('Demo mail blocked (empty allowlist): recipient(s) removed.');
            return false;
        }

        $allowed = array_map('strtolower', $allowlist);

        // Filter To, CC, BCC — keep only addresses on the allowlist.
        $message = $event->message;

        $filter = function (array $addresses) use ($allowed): array {
            return array_filter(
                $addresses,
                fn ($addr) => in_array(strtolower($addr), $allowed, true)
            );
        };

        $to  = $filter(array_keys($message->getTo() ?? []));
        $cc  = $filter(array_keys($message->getCc() ?? []));
        $bcc = $filter(array_keys($message->getBcc() ?? []));

        if (empty($to) && empty($cc) && empty($bcc)) {
            // Log the recipient domain only, never the full address.
            $firstRecipient = array_key_first($message->getTo() ?? []) ?? '';
            $domain = substr($firstRecipient, strpos($firstRecipient, '@') + 1) ?: 'unknown';
            Log::info("Demo mail blocked: no allowed recipients (domain: {$domain}).");
            return false;
        }

        return true;
    }
}
