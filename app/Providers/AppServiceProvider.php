<?php

namespace App\Providers;

use App\Listeners\DemoMailFilter;
use App\Models\Setting;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            \App\Services\InstallerService::class,
            \App\Services\InstallerService::class,
        );
        $this->app->singleton(
            \App\Services\ExposureTestService::class,
            \App\Services\ExposureTestService::class,
        );
    }

    public function boot(): void
    {
        Gate::policy(User::class, UserPolicy::class);

        // In demo mode, filter outbound mail to the allow-list.
        if (config('cms.instance') === 'demo') {
            Event::listen(MessageSending::class, DemoMailFilter::class);
        }

        // Hydrate cms.default_locale from the settings table so panel controllers
        // can read config('cms.default_locale') without hitting the DB themselves.
        // Skipped when the DB isn't ready (installer, unit tests with no DB).
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
                config(['cms.default_locale' => Setting::get('default_locale', 'en')]);
            }
        } catch (\Throwable) {
            // DB not available — config key keeps its static default ('en').
        }
    }
}
