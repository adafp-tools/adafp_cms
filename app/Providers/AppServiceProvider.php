<?php

namespace App\Providers;

use App\Listeners\FilterDemoMailRecipients;
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

        if (config('cms.instance') === 'demo') {
            Event::listen(MessageSending::class, FilterDemoMailRecipients::class);
        }
    }
}
