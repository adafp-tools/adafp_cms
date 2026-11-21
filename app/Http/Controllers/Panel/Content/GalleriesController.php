<?php

namespace App\Http\Controllers\Panel\Content;

use App\Enums\ContentStatus;
use App\Http\Controllers\Controller;
use App\Models\Gallery;
use App\Services\SlugService;
use Illuminate\Http\Request;

class GalleriesController extends Controller
{
    public function __construct(private SlugService $slugs) {}

    public function index()
    {
        $galleries = Gallery::with('translations')->latest()->paginate(30);

        return view('panel.content.galleries.index', compact('galleries'));
    }

    public function create()
    {
        $locales = config('cms.locales', ['en']);

        return view('panel.content.galleries.form', compact('locales'));
    }

    public function store(Request $request)
    {
        $data = $this->validateGallery($request);

        $defaultLocale = config('cms.default_locale', 'en');
        $titleForSlug  = $data['translations'][$defaultLocale]['title'] ?? 'gallery';

        $slug = $this->slugs->unique($data['slug'] ?: $titleForSlug, 'galleries');

        $gallery = Gallery::create([
            'slug'       => $slug,
            'status'     => $data['status'],
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]);

        $this->saveTranslations($gallery, $data['translations']);
        $this->syncImages($gallery, $data['image_ids'] ?? []);

        return redirect()->route('panel.galleries.index')->with('success', __('panel.gallery_created'));
    }

    public function edit(Gallery $gallery)
    {
        $gallery->load(['translations', 'images.media', 'images.translations']);
        $locales = config('cms.locales', ['en']);

        return view('panel.content.galleries.form', compact('gallery', 'locales'));
    }

    public function update(Request $request, Gallery $gallery)
    {
        $data = $this->validateGallery($request);

        if ($data['slug'] && $data['slug'] !== $gallery->slug) {
            $data['slug'] = $this->slugs->unique($data['slug'], 'galleries', 'slug', $gallery->id);
        }

        $gallery->update([
            'slug'       => $data['slug'] ?: $gallery->slug,
            'status'     => $data['status'],
            'updated_by' => $request->user()->id,
        ]);

        $this->saveTranslations($gallery, $data['translations']);
        $this->syncImages($gallery, $data['image_ids'] ?? []);

        return redirect()->route('panel.galleries.index')->with('success', __('panel.gallery_updated'));
    }

    public function destroy(Gallery $gallery)
    {
        $gallery->delete();

        return back()->with('success', __('panel.gallery_deleted'));
    }

    private function validateGallery(Request $request): array
    {
        return $request->validate([
            'slug'              => 'nullable|string|max:200|alpha_dash',
            'status'            => 'required|in:' . implode(',', array_column(ContentStatus::cases(), 'value')),
            'image_ids'         => 'nullable|array',
            'image_ids.*'       => 'exists:media,id',
            'translations'      => 'array',
            'translations.*.title' => 'nullable|string|max:255',
        ]);
    }

    private function saveTranslations(Gallery $gallery, array $translations): void
    {
        foreach ($translations as $locale => $data) {
            if (empty($data['title'])) {
                continue;
            }

            $gallery->translations()->updateOrCreate(
                ['locale' => $locale],
                [
                    'title'         => $data['title'],
                    'translated_by' => 'human',
                    'reviewed'      => true,
                ]
            );
        }
    }

    private function syncImages(Gallery $gallery, array $mediaIds): void
    {
        $gallery->images()->delete();

        foreach ($mediaIds as $sort => $mediaId) {
            $gallery->images()->create([
                'media_id' => $mediaId,
                'sort'     => $sort,
            ]);
        }
    }
}
