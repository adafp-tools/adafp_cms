<?php

namespace App\Providers;

use App\Models\User;
use App\Policies\UserPolicy;
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
    }
}
