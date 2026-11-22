<?php

use App\Http\Controllers\Installer\InstallerController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', \App\Http\Middleware\EnsureNotInstalled::class, \App\Http\Middleware\InstallerLocaleMiddleware::class])
    ->prefix('install')
    ->name('installer.')
    ->group(function () {
        Route::get('/', [InstallerController::class, 'index'])->name('index');
        Route::get('/lang/{locale}', [InstallerController::class, 'setLang'])
            ->where('locale', 'en|fr|pt|ar')
            ->name('lang');
        Route::get('/requirements', [InstallerController::class, 'requirements'])->name('requirements');
        Route::get('/database', [InstallerController::class, 'database'])->name('database');
        Route::post('/database/test', [InstallerController::class, 'databaseTest'])->name('database.test');
        Route::get('/site', [InstallerController::class, 'site'])->name('site');
        Route::post('/site', [InstallerController::class, 'storeSite'])->name('site.store');
        Route::get('/owner', [InstallerController::class, 'owner'])->name('owner');
        Route::post('/owner', [InstallerController::class, 'storeOwner'])->name('owner.store');
        Route::get('/finalize', [InstallerController::class, 'finalize'])->name('finalize');
        Route::get('/complete', [InstallerController::class, 'complete'])->name('complete');
    });
