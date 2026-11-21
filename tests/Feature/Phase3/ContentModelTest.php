<?php

namespace Tests\Feature\Phase3;

use App\Enums\ContentStatus;
use App\Enums\PostType;
use App\Enums\UserRole;
use App\Models\Page;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentModelTest extends TestCase
{
    use RefreshDatabase;

    private function markInstalled(): void
    {
        file_put_contents(storage_path('installed'), 'test');
    }

    protected function tearDown(): void
    {
        @unlink(storage_path('installed'));
        parent::tearDown();
    }

    private function actingAsEditor(): User
    {
        $this->markInstalled();
        $user = User::factory()->create(['role' => UserRole::Editor]);
        $this->actingAs($user);

        return $user;
    }

    // --- Pages ---

    public function test_pages_index_accessible_to_editor(): void
    {
        $this->actingAsEditor();
        $this->get('/panel/pages')->assertOk();
    }

    public function test_editor_can_create_page(): void
    {
        $user = $this->actingAsEditor();

        $this->post('/panel/pages', [
            'slug'     => '',
            'template' => 'default',
            'status'   => 'draft',
            'translations' => [
                'en' => ['title' => 'About us', 'body' => '<p>Hello</p>', 'meta_description' => ''],
            ],
        ])->assertRedirect('/panel/pages');

        $this->assertDatabaseHas('pages', ['slug' => 'about-us']);
        $this->assertDatabaseHas('page_translations', ['title' => 'About us', 'locale' => 'en']);
    }

    public function test_page_slug_is_unique(): void
    {
        $this->actingAsEditor();

        $this->post('/panel/pages', [
            'slug'     => '',
            'template' => 'default',
            'status'   => 'draft',
            'translations' => ['en' => ['title' => 'Home']],
        ]);

        $this->post('/panel/pages', [
            'slug'     => '',
            'template' => 'default',
            'status'   => 'draft',
            'translations' => ['en' => ['title' => 'Home']],
        ]);

        $this->assertDatabaseHas('pages', ['slug' => 'home']);
        $this->assertDatabaseHas('pages', ['slug' => 'home-1']);
    }

    // --- Posts ---

    public function test_editor_can_create_post(): void
    {
        $this->actingAsEditor();

        $this->post('/panel/posts', [
            'slug'     => '',
            'type'     => 'news',
            'status'   => 'draft',
            'translations' => [
                'en' => ['title' => 'Big news', 'summary' => 'Short', 'body' => '<p>body</p>'],
            ],
        ])->assertRedirect('/panel/posts');

        $this->assertDatabaseHas('posts', ['slug' => 'big-news']);
        $this->assertDatabaseHas('post_translations', ['title' => 'Big news', 'locale' => 'en']);
    }

    public function test_post_update_saves_revision(): void
    {
        $user = $this->actingAsEditor();

        $this->post('/panel/posts', [
            'type'   => 'news',
            'status' => 'draft',
            'translations' => ['en' => ['title' => 'Original', 'body' => 'v1']],
        ]);

        $post = Post::first();

        $this->put("/panel/posts/{$post->id}", [
            'type'   => 'news',
            'status' => 'draft',
            'translations' => ['en' => ['title' => 'Updated', 'body' => 'v2']],
        ])->assertRedirect('/panel/posts');

        $this->assertDatabaseHas('post_revisions', ['post_id' => $post->id, 'title' => 'Original']);
        $this->assertDatabaseHas('post_translations', ['title' => 'Updated']);
    }

    // --- Slug Service ---

    public function test_arabic_title_generates_non_empty_slug(): void
    {
        // cocur/slugify with the Arabic ruleset romanises Arabic text (e.g. مرحبا → mrhb).
        // The item-{hash} fallback only fires when the result is truly empty (e.g. CJK-only input).
        $this->actingAsEditor();

        $this->post('/panel/posts', [
            'type'   => 'news',
            'status' => 'draft',
            'translations' => ['en' => ['title' => 'مرحبا']],
        ])->assertRedirect('/panel/posts');

        $post = Post::first();
        $this->assertNotEmpty($post->slug);
    }

    // --- Content Scheduler ---

    public function test_scheduler_publishes_scheduled_posts(): void
    {
        $this->markInstalled();

        Post::create([
            'slug'         => 'sched-post',
            'type'         => PostType::News,
            'status'       => ContentStatus::Scheduled,
            'published_at' => now()->subMinute(),
            'created_by'   => null,
            'updated_by'   => null,
        ]);

        $this->artisan('cms:publish-scheduled')->assertSuccessful();

        $this->assertDatabaseHas('posts', [
            'slug'   => 'sched-post',
            'status' => ContentStatus::Published->value,
        ]);
    }

    public function test_scheduler_does_not_publish_future_scheduled_posts(): void
    {
        $this->markInstalled();

        Post::create([
            'slug'         => 'future-post',
            'type'         => PostType::News,
            'status'       => ContentStatus::Scheduled,
            'published_at' => now()->addDay(),
            'created_by'   => null,
            'updated_by'   => null,
        ]);

        $this->artisan('cms:publish-scheduled')->assertSuccessful();

        $this->assertDatabaseHas('posts', [
            'slug'   => 'future-post',
            'status' => ContentStatus::Scheduled->value,
        ]);
    }
}
