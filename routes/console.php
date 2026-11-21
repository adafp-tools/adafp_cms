<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Publish scheduled content every minute.
Schedule::command('cms:publish-scheduled')->everyMinute();

// Clean page view records older than 90 days, once a week.
Schedule::call(function () {
    \App\Models\PageView::where('viewed_at', '<', now()->subDays(90))->delete();
})->weekly();
