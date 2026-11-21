<?php

namespace App\Console\Commands;

use App\Enums\ContentStatus;
use App\Models\Event;
use App\Models\Page;
use App\Models\Post;
use Illuminate\Console\Command;

class ContentSchedulerCommand extends Command
{
    protected $signature   = 'cms:publish-scheduled';
    protected $description = 'Publish content whose scheduled publish_at time has passed.';

    public function handle(): int
    {
        $now = now();

        $pages = Page::where('status', ContentStatus::Scheduled)
            ->where('published_at', '<=', $now)
            ->get();

        foreach ($pages as $page) {
            $page->update(['status' => ContentStatus::Published]);
        }

        $posts = Post::where('status', ContentStatus::Scheduled)
            ->where('published_at', '<=', $now)
            ->get();

        foreach ($posts as $post) {
            $post->update(['status' => ContentStatus::Published]);
        }

        $events = Event::where('status', ContentStatus::Scheduled)
            ->where('published_at', '<=', $now)
            ->get();

        foreach ($events as $event) {
            $event->update(['status' => ContentStatus::Published]);
        }

        $count = $pages->count() + $posts->count() + $events->count();
        $this->info("Published {$count} scheduled item(s).");

        return self::SUCCESS;
    }
}
