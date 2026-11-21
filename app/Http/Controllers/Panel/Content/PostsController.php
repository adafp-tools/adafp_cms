<?php

namespace App\Http\Controllers\Panel\Content;

use App\Enums\ContentStatus;
use App\Enums\PostType;
use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Services\ContentRevisionService;
use App\Services\SlugService;
use Illuminate\Http\Request;

class PostsController extends Controller
{
    public function __construct(
        private SlugService $slugs,
        private ContentRevisionService $revisions,
    ) {}

    public function index()
    {
        $posts = Post::with('translations')->latest()->paginate(30);

        return view('panel.content.posts.index', compact('posts'));
    }

    public function create()
    {
        $locales = config('cms.locales', ['en']);
        $types   = PostType::cases();

        return view('panel.content.posts.form', compact('locales', 'types'));
    }

    public function store(Request $request)
    {
        $data = $this->validatePost($request);

        $defaultLocale = config('cms.default_locale', 'en');
        $titleForSlug  = $data['translations'][$defaultLocale]['title'] ?? 'post';

        $slug = $this->slugs->unique(($data['slug'] ?? '') ?: $titleForSlug, 'posts');

        $post = Post::create([
            'slug'              => $slug,
            'type'              => $data['type'],
            'status'            => $data['status'],
            'published_at'      => $data['published_at'] ?? null,
            'featured_image_id' => $data['featured_image_id'] ?? null,
            'author_name'       => $data['author_name'] ?? null,
            'created_by'        => $request->user()->id,
            'updated_by'        => $request->user()->id,
        ]);

        $this->saveTranslations($post, $data['translations']);

        return redirect()->route('panel.posts.index')->with('success', __('panel.post_created'));
    }

    public function edit(Post $post)
    {
        $post->load('translations');
        $locales = config('cms.locales', ['en']);
        $types   = PostType::cases();

        return view('panel.content.posts.form', compact('post', 'locales', 'types'));
    }

    public function update(Request $request, Post $post)
    {
        $data = $this->validatePost($request);

        if (($data['slug'] ?? '') && ($data['slug'] ?? '') !== $post->slug) {
            $data['slug'] = $this->slugs->unique($data['slug'], 'posts', 'slug', $post->id);
        }

        foreach (config('cms.locales', ['en']) as $locale) {
            if (isset($data['translations'][$locale])) {
                $this->revisions->savePostRevision($post, $locale, $request->user()->id);
            }
        }

        $post->update([
            'slug'              => ($data['slug'] ?? '') ?: $post->slug,
            'type'              => $data['type'],
            'status'            => $data['status'],
            'published_at'      => $data['published_at'] ?? null,
            'featured_image_id' => $data['featured_image_id'] ?? null,
            'author_name'       => $data['author_name'] ?? null,
            'updated_by'        => $request->user()->id,
        ]);

        $this->saveTranslations($post, $data['translations']);

        return redirect()->route('panel.posts.index')->with('success', __('panel.post_updated'));
    }

    public function destroy(Post $post)
    {
        $post->delete();

        return back()->with('success', __('panel.post_deleted'));
    }

    private function validatePost(Request $request): array
    {
        return $request->validate([
            'slug'              => 'nullable|string|max:200|alpha_dash',
            'type'              => 'required|in:' . implode(',', array_column(PostType::cases(), 'value')),
            'status'            => 'required|in:' . implode(',', array_column(ContentStatus::cases(), 'value')),
            'published_at'      => 'nullable|date',
            'featured_image_id' => 'nullable|exists:media,id',
            'author_name'       => 'nullable|string|max:150',
            'translations'      => 'array',
            'translations.*.title'            => 'nullable|string|max:255',
            'translations.*.summary'          => 'nullable|string|max:500',
            'translations.*.body'             => 'nullable|string',
            'translations.*.meta_description' => 'nullable|string|max:160',
        ]);
    }

    private function saveTranslations(Post $post, array $translations): void
    {
        foreach ($translations as $locale => $data) {
            if (empty($data['title']) && empty($data['body'])) {
                continue;
            }

            $post->translations()->updateOrCreate(
                ['locale' => $locale],
                [
                    'title'            => $data['title'] ?? null,
                    'summary'          => $data['summary'] ?? null,
                    'body'             => $data['body'] ?? null,
                    'meta_description' => $data['meta_description'] ?? null,
                    'translated_by'    => 'human',
                    'reviewed'         => true,
                ]
            );
        }
    }
}
