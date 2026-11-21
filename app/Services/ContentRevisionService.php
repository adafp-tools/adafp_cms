<?php

namespace App\Services;

use App\Models\Page;
use App\Models\PageRevision;
use App\Models\PageTranslation;
use App\Models\Post;
use App\Models\PostRevision;
use App\Models\PostTranslation;
use Illuminate\Database\Eloquent\Model;

class ContentRevisionService
{
    private const MAX_REVISIONS = 10;

    public function savePageRevision(Page $page, string $locale, int $createdBy): void
    {
        $translation = $page->translations()->where('locale', $locale)->first();

        if (! $translation) {
            return;
        }

        PageRevision::create([
            'page_id'          => $page->id,
            'locale'           => $locale,
            'title'            => $translation->title,
            'body'             => $translation->body,
            'meta_description' => $translation->meta_description,
            'created_by'       => $createdBy,
        ]);

        $this->pruneRevisions(PageRevision::class, 'page_id', $page->id, $locale);
    }

    public function savePostRevision(Post $post, string $locale, int $createdBy): void
    {
        $translation = $post->translations()->where('locale', $locale)->first();

        if (! $translation) {
            return;
        }

        PostRevision::create([
            'post_id'          => $post->id,
            'locale'           => $locale,
            'title'            => $translation->title,
            'summary'          => $translation->summary,
            'body'             => $translation->body,
            'meta_description' => $translation->meta_description,
            'created_by'       => $createdBy,
        ]);

        $this->pruneRevisions(PostRevision::class, 'post_id', $post->id, $locale);
    }

    public function restorePageRevision(PageRevision $revision): void
    {
        PageTranslation::updateOrCreate(
            ['page_id' => $revision->page_id, 'locale' => $revision->locale],
            [
                'title'            => $revision->title,
                'body'             => $revision->body,
                'meta_description' => $revision->meta_description,
            ]
        );
    }

    public function restorePostRevision(PostRevision $revision): void
    {
        PostTranslation::updateOrCreate(
            ['post_id' => $revision->post_id, 'locale' => $revision->locale],
            [
                'title'            => $revision->title,
                'summary'          => $revision->summary,
                'body'             => $revision->body,
                'meta_description' => $revision->meta_description,
            ]
        );
    }

    private function pruneRevisions(string $model, string $fk, int $id, string $locale): void
    {
        $keep = $model::where($fk, $id)
            ->where('locale', $locale)
            ->orderByDesc('created_at')
            ->limit(self::MAX_REVISIONS)
            ->pluck('id');

        $model::where($fk, $id)
            ->where('locale', $locale)
            ->whereNotIn('id', $keep)
            ->delete();
    }
}
