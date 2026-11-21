<?php

namespace Database\Factories;

use App\Enums\ContentStatus;
use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        $title = fake()->sentence(4);
        return [
            'slug'         => Str::slug($title) . '-' . Str::random(4),
            'starts_at'    => now()->addDays(rand(1, 30)),
            'ends_at'      => now()->addDays(rand(31, 60)),
            'status'       => ContentStatus::Published,
            'published_at' => now()->subDays(1),
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Event $event) {
            $event->translations()->create([
                'locale' => 'en',
                'title'  => fake()->sentence(4),
            ]);
        });
    }
}
