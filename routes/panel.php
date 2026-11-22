<?php

use App\Http\Controllers\Panel\AuthController;
use App\Http\Controllers\Panel\BackupsController;
use App\Http\Controllers\Panel\DashboardController;
use App\Http\Controllers\Panel\HealthController;
use App\Http\Controllers\Panel\SsoController;
use App\Http\Controllers\Panel\SubmissionsController;
use App\Http\Controllers\Panel\TwoFactorController;
use App\Http\Controllers\Panel\UsersController;
use App\Http\Controllers\Panel\Content\AiTranslationController;
use App\Http\Controllers\Panel\Content\DocumentsController;
use App\Http\Controllers\Panel\Content\EventsController;
use App\Http\Controllers\Panel\Content\GalleriesController;
use App\Http\Controllers\Panel\Content\MediaController;
use App\Http\Controllers\Panel\Content\MenusController;
use App\Http\Controllers\Panel\Content\PagesController;
use App\Http\Controllers\Panel\Content\PeopleController;
use App\Http\Controllers\Panel\Content\PostsController;
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

        // SSO callback — authenticated by CRM-signed token, no Laravel auth required
        Route::get('/sso', [SsoController::class, 'callback'])->name('sso');

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

                // Content — Media
                Route::get('/media', [MediaController::class, 'index'])->name('media.index');
                Route::post('/media', [MediaController::class, 'store'])->name('media.store');
                Route::delete('/media/{medium}', [MediaController::class, 'destroy'])->name('media.destroy');

                // Content — Pages
                Route::resource('pages', PagesController::class)->except(['show'])->names([
                    'index'   => 'pages.index',
                    'create'  => 'pages.create',
                    'store'   => 'pages.store',
                    'edit'    => 'pages.edit',
                    'update'  => 'pages.update',
                    'destroy' => 'pages.destroy',
                ]);

                // Content — Posts
                Route::resource('posts', PostsController::class)->except(['show'])->names([
                    'index'   => 'posts.index',
                    'create'  => 'posts.create',
                    'store'   => 'posts.store',
                    'edit'    => 'posts.edit',
                    'update'  => 'posts.update',
                    'destroy' => 'posts.destroy',
                ]);

                // Content — Events
                Route::resource('events', EventsController::class)->except(['show'])->names([
                    'index'   => 'events.index',
                    'create'  => 'events.create',
                    'store'   => 'events.store',
                    'edit'    => 'events.edit',
                    'update'  => 'events.update',
                    'destroy' => 'events.destroy',
                ]);

                // Content — People
                Route::resource('people', PeopleController::class)->except(['show'])->names([
                    'index'   => 'people.index',
                    'create'  => 'people.create',
                    'store'   => 'people.store',
                    'edit'    => 'people.edit',
                    'update'  => 'people.update',
                    'destroy' => 'people.destroy',
                ]);

                // Content — Documents
                Route::resource('documents', DocumentsController::class)->except(['show'])->names([
                    'index'   => 'documents.index',
                    'create'  => 'documents.create',
                    'store'   => 'documents.store',
                    'edit'    => 'documents.edit',
                    'update'  => 'documents.update',
                    'destroy' => 'documents.destroy',
                ]);

                // Content — Galleries
                Route::resource('galleries', GalleriesController::class)->except(['show'])->names([
                    'index'   => 'galleries.index',
                    'create'  => 'galleries.create',
                    'store'   => 'galleries.store',
                    'edit'    => 'galleries.edit',
                    'update'  => 'galleries.update',
                    'destroy' => 'galleries.destroy',
                ]);

                // Content — Menus
                Route::get('/menus', [MenusController::class, 'index'])->name('menus.index');
                Route::get('/menus/{menu}/edit', [MenusController::class, 'edit'])->name('menus.edit');
                Route::put('/menus/{menu}', [MenusController::class, 'update'])->name('menus.update');

                // AI Translation (JSON endpoint)
                Route::post('/ai-translate', [AiTranslationController::class, 'translate'])->name('ai.translate');

                // Submissions
                Route::prefix('submissions')->name('submissions.')->group(function () {
                    Route::get('/',            [SubmissionsController::class, 'index'])->name('index');
                    Route::get('/{id}',        [SubmissionsController::class, 'show'])->name('show');
                    Route::delete('/{id}',     [SubmissionsController::class, 'destroy'])->name('destroy');
                    Route::post('/export',     [SubmissionsController::class, 'export'])->name('export');
                    Route::post('/{id}/read',  [SubmissionsController::class, 'markRead'])->name('read');
                    Route::post('/purge',      [SubmissionsController::class, 'purge'])->name('purge');
                });

                // Backups (Owner only — enforced inside controller)
                Route::prefix('backups')->name('backups.')->group(function () {
                    Route::get('/',                  [BackupsController::class, 'index'])->name('index');
                    Route::get('/download/{file}',   [BackupsController::class, 'download'])->name('download');
                    Route::delete('/{file}',         [BackupsController::class, 'destroy'])->name('destroy');
                    Route::post('/run',              [BackupsController::class, 'run'])->name('run');
                });

                // Settings
                Route::get('/settings', [\App\Http\Controllers\Panel\SettingsController::class, 'index'])->name('settings.index');
                Route::post('/settings', [\App\Http\Controllers\Panel\SettingsController::class, 'update'])->name('settings.update');
                Route::post('/settings/logo', [\App\Http\Controllers\Panel\SettingsController::class, 'logo'])->name('settings.logo');

                // Analytics
                Route::get('/analytics', [\App\Http\Controllers\Panel\AnalyticsController::class, 'index'])->name('analytics');
            });
        });
    });
