<?php

namespace App\Console\Commands;

use App\Models\FormSubmission;
use Illuminate\Console\Command;

class GdprErasureCommand extends Command
{
    protected $signature   = 'cms:gdpr-erase';
    protected $description = 'Hard-delete soft-deleted form submissions past retention period';

    public function handle(): int
    {
        // Hard-delete submissions that were soft-deleted > 90 days ago
        $softDeleted = FormSubmission::onlyTrashed()
            ->where('deleted_at', '<', now()->subDays(90))
            ->forceDelete();

        // Hard-delete any submission older than 3 years regardless of deletion status
        $aged = FormSubmission::withTrashed()
            ->where('created_at', '<', now()->subYears(3))
            ->forceDelete();

        $total = $softDeleted + $aged;
        $this->info("GDPR erasure: {$total} submission(s) permanently deleted.");

        return self::SUCCESS;
    }
}
