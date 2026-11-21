<?php

namespace Tests\Feature\Phase3;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaTest extends TestCase
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

    public function test_editor_can_upload_image(): void
    {
        $this->markInstalled();
        Storage::fake('local');

        $user = User::factory()->create(['role' => UserRole::Editor]);
        $this->actingAs($user);

        $file = UploadedFile::fake()->image('photo.jpg', 400, 300);

        $this->post('/panel/media', ['file' => $file])
             ->assertRedirect();

        $this->assertDatabaseHas('media', ['original_filename' => 'photo.jpg']);
    }

    public function test_svg_upload_is_rejected(): void
    {
        $this->markInstalled();
        Storage::fake('local');

        $user = User::factory()->create(['role' => UserRole::Editor]);
        $this->actingAs($user);

        $svg = UploadedFile::fake()->create('icon.svg', 1, 'image/svg+xml');

        $this->post('/panel/media', ['file' => $svg])
             ->assertSessionHasErrors('file');
    }

    public function test_media_index_loads(): void
    {
        $this->markInstalled();
        $user = User::factory()->create(['role' => UserRole::Editor]);
        $this->actingAs($user)->get('/panel/media')->assertOk();
    }
}
