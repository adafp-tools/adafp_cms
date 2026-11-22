<?php

use App\Http\Controllers\Public\ContactController;
use App\Http\Controllers\Public\DemoPageController;
use App\Http\Controllers\Public\DocumentsController;
use App\Http\Controllers\Public\EventController;
use App\Http\Controllers\Public\FeedController;
use App\Http\Controllers\Public\GalleryController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\JoinController;
use App\Http\Controllers\Public\PageController;
use App\Http\Controllers\Public\PeopleController;
use App\Http\Controllers\Public\PostController;
use App\Http\Controllers\Public\RobotsController;
use App\Http\Controllers\Public\SitemapController;
use App\Http\Middleware\LocaleMiddleware;
use App\Http\Middleware\PublicPageCache;
use Illuminate\Support\Facades\Route;

// Root redirect to default locale
Route::get('/', [HomeController::class, 'redirect'])->name('public.root');

// Non-locale utility routes
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('public.sitemap');
Route::get('/robots.txt', [RobotsController::class, 'index'])->name('public.robots');
Route::get('/feed.xml', [FeedController::class, 'index'])->name('public.feed');

// Locale-prefixed public routes
Route::prefix('{locale}')
    ->where(['locale' => implode('|', config('cms.supported_locales', ['en', 'fr', 'pt', 'ar']))])
    ->middleware([LocaleMiddleware::class, PublicPageCache::class, \App\Http\Middleware\DemoNoIndex::class])
    ->group(function () {
        Route::get('/', [HomeController::class, 'index'])->name('public.home');
        Route::get('/news', [PostController::class, 'index'])->name('public.news.index');
        Route::get('/news/{slug}', [PostController::class, 'show'])->name('public.news.show');
        Route::get('/events', [EventController::class, 'index'])->name('public.events.index');
        Route::get('/events/{slug}', [EventController::class, 'show'])->name('public.events.show');
        Route::get('/leadership', [PeopleController::class, 'index'])->name('public.leadership');
        Route::get('/documents', [DocumentsController::class, 'index'])->name('public.documents');
        Route::get('/gallery/{slug}', [GalleryController::class, 'show'])->name('public.gallery.show');
        Route::get('/contact',  [ContactController::class, 'show'])->name('public.contact');
        Route::post('/contact', [ContactController::class, 'submit'])->name('public.contact.submit');
        Route::get('/join',     [JoinController::class, 'show'])->name('public.join');
        Route::post('/join',    [JoinController::class, 'submit'])->name('public.join.submit');
        // Demo information page — registered before the catch-all slug route, demo instances only.
        if (config('cms.instance') === 'demo') {
            Route::get('/demo', [DemoPageController::class, 'index'])->name('public.demo');
        }

        // Generic page slug last (catch-all within locale)
        Route::get('/{slug}', [PageController::class, 'show'])->name('public.page.show');
    });
