<?php

namespace App\Listeners;

use Illuminate\Mail\Events\MessageSending;
use Symfony\Component\Mime\Address;

/**
 * When CMS_INSTANCE=demo, strip recipients whose address is not in
 * config('cms.demo_mail_allowlist'). If all recipients are removed, return
 * false to cancel delivery entirely and log the suppression instead.
 */
class DemoMailFilter
{
    public function handle(MessageSending $event): bool|null
    {
        if (config('cms.instance') !== 'demo') {
            return null;
        }

        $allowlist = array_map('strtolower', config('cms.demo_mail_allowlist', []));

        if (empty($allowlist)) {
            // No allow-list configured: suppress all outbound mail in demo mode.
            logger()->info('DemoMailFilter: suppressed mail (no allow-list)', [
                'subject' => $event->message->getSubject(),
            ]);
            return false;
        }

        // Filter To, Cc, Bcc to only allowed addresses.
        $filter = fn (array $addresses) => array_filter(
            $addresses,
            fn (Address $a) => in_array(strtolower($a->getAddress()), $allowlist, true)
        );

        $to  = $filter($event->message->getTo());
        $cc  = $filter($event->message->getCc());
        $bcc = $filter($event->message->getBcc());

        if (empty($to) && empty($cc) && empty($bcc)) {
            logger()->info('DemoMailFilter: suppressed mail (no allowed recipients)', [
                'subject' => $event->message->getSubject(),
            ]);
            return false;
        }

        // Replace recipients with filtered sets.
        $event->message->to(...array_values($to));
        $event->message->cc(...array_values($cc));
        $event->message->bcc(...array_values($bcc));

        return null;
    }
}
