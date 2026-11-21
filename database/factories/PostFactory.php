<?php

namespace Database\Factories;

use App\Enums\ContentStatus;
use App\Enums\PostType;
use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        $title = fake()->sentence(5);
        return [
            'slug'         => Str::slug($title) . '-' . Str::random(4),
            'type'         => PostType::News,
            'status'       => ContentStatus::Published,
            'published_at' => now()->subDays(rand(1, 30)),
        ];
    }

    public function draft(): static
    {
        return $this->state(['status' => ContentStatus::Draft, 'published_at' => null]);
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Post $post) {
            $post->translations()->create([
                'locale'  => 'en',
                'title'   => fake()->sentence(5),
                'summary' => fake()->paragraph(),
                'body'    => '<p>' . fake()->paragraph(3) . '</p>',
            ]);
        });
    }
}
