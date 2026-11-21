<?php

use App\Http\Controllers\Panel\AuthController;
use App\Http\Controllers\Panel\DashboardController;
use App\Http\Controllers\Panel\HealthController;
use App\Http\Controllers\Panel\TwoFactorController;
use App\Http\Controllers\Panel\UsersController;
use Illuminate\Support\Facades\Route;

$panelPath = config('cms.panel_path', 'panel');

Route::middleware(['web', \App\Http\Middleware\EnsureInstalled::class, \App\Http\Middleware\PanelNoindex::class])
    ->prefix($panelPath)
    ->name('panel.')
    ->group(function () {
        // Auth (unauthenticated)
        Route::middleware('guest')->group(function () {
            Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
            Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
        });

        // Password reset (via Fortify under the panel path — wired in FortifyServiceProvider)
        Route::get('/forgot-password', [\App\Http\Controllers\Panel\PasswordController::class, 'requestForm'])
            ->name('password.request')->middleware('guest');
        Route::post('/forgot-password', [\App\Http\Controllers\Panel\PasswordController::class, 'sendLink'])
            ->name('password.email')->middleware('guest');
        Route::get('/reset-password/{token}', [\App\Http\Controllers\Panel\PasswordController::class, 'resetForm'])
            ->name('password.reset')->middleware('guest');
        Route::post('/reset-password', [\App\Http\Controllers\Panel\PasswordController::class, 'resetPassword'])
            ->name('password.update')->middleware('guest');

        // Invitation acceptance (no auth required)
        Route::get('/invitation/{token}', [UsersController::class, 'acceptInvitation'])->name('invitation.accept');
        Route::post('/invitation/{token}', [UsersController::class, 'completeInvitation'])->name('invitation.complete');

        // Authenticated
        Route::middleware('auth')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

            // 2FA setup (before 2FA is confirmed)
            Route::get('/two-factor/setup', [TwoFactorController::class, 'setup'])->name('two-factor.setup');
            Route::post('/two-factor/setup', [TwoFactorController::class, 'confirmSetup'])->name('two-factor.setup.confirm');
            Route::get('/two-factor/challenge', [TwoFactorController::class, 'challenge'])->name('two-factor.challenge');
            Route::post('/two-factor/challenge', [TwoFactorController::class, 'verifyChallenge'])->name('two-factor.challenge.verify');

            // Fully authenticated + 2FA-enforced zone
            Route::middleware(\App\Http\Middleware\RequireTwoFactor::class)->group(function () {
                Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

                // Health (owner + admin)
                Route::get('/health', [HealthController::class, 'index'])->name('health');
                Route::post('/health/mail-test', [HealthController::class, 'mailTest'])->name('health.mail-test');

                // Users
                Route::get('/users', [UsersController::class, 'index'])->name('users.index');
                Route::post('/users/invite', [UsersController::class, 'invite'])->name('users.invite');
                Route::delete('/users/{user}', [UsersController::class, 'destroy'])->name('users.destroy');

                // 2FA management
                Route::delete('/two-factor', [TwoFactorController::class, 'disable'])->name('two-factor.disable');
                Route::post('/two-factor/regenerate', [TwoFactorController::class, 'regenerateCodes'])->name('two-factor.regenerate');
            });
        });
    });
