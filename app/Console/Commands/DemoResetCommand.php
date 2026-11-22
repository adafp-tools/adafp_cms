<?php

namespace App\Console\Commands;

use Database\Seeders\DemoContentSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DemoResetCommand extends Command
{
    protected $signature   = 'cms:demo-reset {--force : Skip the instance guard (for testing)}';
    protected $description = 'Wipe all content and re-seed the demo installation.';

    // Truncation order respects foreign-key constraints
    private const CONTENT_TABLES = [
        'page_revisions',
        'page_translations',
        'pages',
        'post_revisions',
        'post_translations',
        'posts',
        'event_translations',
        'events',
        'person_translations',
        'people',
        'document_translations',
        'documents',
        'gallery_image_translations',
        'gallery_images',
        'gallery_translations',
        'galleries',
        'menu_translations',
        'menus',
        'form_submissions',
        'media',
        'users',
        'settings',
    ];

    public function handle(): int
    {
        if (config('cms.instance') !== 'demo' && ! $this->option('force')) {
            $this->error('cms:demo-reset only runs when CMS_INSTANCE=demo. Use --force to override.');
            return 1;
        }

        $this->info('Starting demo reset…');

        // Disable FK checks, truncate, re-enable
        DB::statement('PRAGMA foreign_keys = OFF');  // SQLite
        try {
            Schema::disableForeignKeyConstraints();
            foreach (self::CONTENT_TABLES as $table) {
                if (Schema::hasTable($table)) {
                    DB::table($table)->truncate();
                }
            }
        } finally {
            Schema::enableForeignKeyConstraints();
            DB::statement('PRAGMA foreign_keys = ON');  // SQLite
        }

        $this->line('  Tables cleared.');

        // Re-seed
        Artisan::call('db:seed', ['--class' => DemoContentSeeder::class, '--force' => true]);
        $this->line('  Content re-seeded.');

        // Clear all caches (settings cache, page cache, view cache)
        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        $this->line('  Caches cleared.');

        $this->info('Demo reset complete.');
        return 0;
    }
}
