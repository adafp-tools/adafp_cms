<?php

namespace Tests\Feature;

use Database\Seeders\DemoContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoContentSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_runs_without_error(): void
    {
        $this->seed(DemoContentSeeder::class);

        // Settings populated
        $this->assertDatabaseHas('settings', ['key' => 'default_locale', 'value' => 'en']);
        $this->assertDatabaseHas('settings', ['key' => 'active_locales', 'value' => 'en,fr,pt,ar']);
        $this->assertDatabaseHas('settings', ['key' => 'noindex', 'value' => '1']);

        // 4 pages × 4 locales
        $this->assertEquals(4,  \App\Models\Page::count());
        $this->assertEquals(16, \App\Models\PageTranslation::count());

        // 3 posts × 4 locales
        $this->assertEquals(3,  \App\Models\Post::count());
        $this->assertEquals(12, \App\Models\PostTranslation::count());

        // 3 events × 4 locales
        $this->assertEquals(3,  \App\Models\Event::count());
        $this->assertEquals(12, \App\Models\EventTranslation::count());

        // 4 people × 4 locales
        $this->assertEquals(4,  \App\Models\Person::count());
        $this->assertEquals(16, \App\Models\PersonTranslation::count());

        // 3 documents × 4 locales
        $this->assertEquals(3,  \App\Models\Document::count());
        $this->assertEquals(12, \App\Models\DocumentTranslation::count());

        // 2 galleries, 5 images total, all 4 locales
        $this->assertEquals(2,  \App\Models\Gallery::count());
        $this->assertEquals(5,  \App\Models\GalleryImage::count());
        $this->assertEquals(20, \App\Models\GalleryImageTranslation::count());

        // 2 menus × 4 locales
        $this->assertEquals(2,  \App\Models\Menu::count());
        $this->assertEquals(8,  \App\Models\MenuTranslation::count());
    }

    public function test_no_adafp_string_in_seed_content(): void
    {
        $this->seed(DemoContentSeeder::class);

        $check = function (string $table, string $column) {
            $count = \Illuminate\Support\Facades\DB::table($table)
                ->whereRaw("LOWER({$column}) LIKE '%adafp%'")
                ->count();
            $this->assertEquals(0, $count, "Found 'adafp' in {$table}.{$column}");
        };

        $check('settings',           'value');
        $check('page_translations',  'title');
        $check('page_translations',  'body');
        $check('post_translations',  'title');
        $check('post_translations',  'body');
        $check('event_translations', 'title');
        $check('person_translations', 'bio');
        $check('document_translations', 'title');
        $check('menu_translations',  'items');
    }

    public function test_menu_items_stored_as_valid_json(): void
    {
        $this->seed(DemoContentSeeder::class);

        \App\Models\MenuTranslation::all()->each(function ($mt) {
            $decoded = json_decode($mt->items, true);
            $this->assertIsArray($decoded, "menu_translations.items is not valid JSON (locale {$mt->locale})");
            $this->assertNotEmpty($decoded);
        });
    }

    public function test_active_locales_stored_as_csv_not_json(): void
    {
        $this->seed(DemoContentSeeder::class);

        $value = \App\Models\Setting::get('active_locales');
        $this->assertStringNotContainsString('[', $value, 'active_locales must be CSV, not JSON');
        $this->assertStringNotContainsString('"', $value, 'active_locales must be CSV, not JSON');
        $this->assertEquals(['en', 'fr', 'pt', 'ar'], explode(',', $value));
    }
}
