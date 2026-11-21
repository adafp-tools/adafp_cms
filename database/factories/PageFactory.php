<?php

namespace Database\Factories;

use App\Enums\ContentStatus;
use App\Enums\PageTemplate;
use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PageFactory extends Factory
{
    protected $model = Page::class;

    public function definition(): array
    {
        $title = fake()->sentence(3);
        return [
            'slug'         => Str::slug($title) . '-' . Str::random(4),
            'template'     => PageTemplate::Default,
            'status'       => ContentStatus::Published,
            'published_at' => now()->subDays(rand(1, 30)),
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Page $page) {
            $page->translations()->create([
                'locale' => 'en',
                'title'  => fake()->sentence(3),
                'body'   => '<p>' . fake()->paragraph(2) . '</p>',
            ]);
        });
    }
}
