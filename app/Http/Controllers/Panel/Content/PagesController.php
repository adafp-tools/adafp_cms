<?php

namespace App\Http\Controllers\Panel\Content;

use App\Enums\ContentStatus;
use App\Enums\PageTemplate;
use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Services\ContentRevisionService;
use App\Services\SlugService;
use Illuminate\Http\Request;

class PagesController extends Controller
{
    public function __construct(
        private SlugService $slugs,
        private ContentRevisionService $revisions,
    ) {}

    public function index()
    {
        $pages = Page::with('translations')->orderBy('sort')->paginate(30);

        return view('panel.content.pages.index', compact('pages'));
    }

    public function create()
    {
        $locales   = config('cms.locales', ['en']);
        $templates = PageTemplate::cases();
        $parents   = Page::orderBy('sort')->get();

        return view('panel.content.pages.form', compact('locales', 'templates', 'parents'));
    }

    public function store(Request $request)
    {
        $data = $this->validatePage($request);

        $slug = $this->slugs->unique(
            $data['slug'] ?: ($data['translations'][config('cms.default_locale', 'en')]['title'] ?? 'page'),
            'pages'
        );

        $page = Page::create([
            'slug'              => $slug,
            'parent_id'         => $data['parent_id'] ?? null,
            'template'          => $data['template'],
            'status'            => $data['status'],
            'sort'              => $data['sort'] ?? 0,
            'show_in_menu'      => $data['show_in_menu'] ?? false,
            'featured_image_id' => $data['featured_image_id'] ?? null,
            'published_at'      => $data['published_at'] ?? null,
            'created_by'        => $request->user()->id,
            'updated_by'        => $request->user()->id,
        ]);

        $this->saveTranslations($page, $data['translations'], $request->user()->id);

        return redirect()->route('panel.pages.index')->with('success', __('panel.page_created'));
    }

    public function edit(Page $page)
    {
        $page->load('translations');
        $locales   = config('cms.locales', ['en']);
        $templates = PageTemplate::cases();
        $parents   = Page::where('id', '!=', $page->id)->orderBy('sort')->get();

        return view('panel.content.pages.form', compact('page', 'locales', 'templates', 'parents'));
    }

    public function update(Request $request, Page $page)
    {
        $data = $this->validatePage($request);

        if ($data['slug'] && $data['slug'] !== $page->slug) {
            $data['slug'] = $this->slugs->unique($data['slug'], 'pages', 'slug', $page->id);
        }

        foreach (config('cms.locales', ['en']) as $locale) {
            if (isset($data['translations'][$locale])) {
                $this->revisions->savePageRevision($page, $locale, $request->user()->id);
            }
        }

        $page->update([
            'slug'              => $data['slug'] ?: $page->slug,
            'parent_id'         => $data['parent_id'] ?? null,
            'template'          => $data['template'],
            'status'            => $data['status'],
            'sort'              => $data['sort'] ?? $page->sort,
            'show_in_menu'      => $data['show_in_menu'] ?? false,
            'featured_image_id' => $data['featured_image_id'] ?? null,
            'published_at'      => $data['published_at'] ?? null,
            'updated_by'        => $request->user()->id,
        ]);

        $this->saveTranslations($page, $data['translations'], $request->user()->id);

        return redirect()->route('panel.pages.index')->with('success', __('panel.page_updated'));
    }

    public function destroy(Page $page)
    {
        $page->delete();

        return back()->with('success', __('panel.page_deleted'));
    }

    private function validatePage(Request $request): array
    {
        return $request->validate([
            'slug'              => 'nullable|string|max:200|alpha_dash',
            'parent_id'         => 'nullable|exists:pages,id',
            'template'          => 'required|in:' . implode(',', array_column(PageTemplate::cases(), 'value')),
            'status'            => 'required|in:' . implode(',', array_column(ContentStatus::cases(), 'value')),
            'sort'              => 'nullable|integer|min:0',
            'show_in_menu'      => 'boolean',
            'featured_image_id' => 'nullable|exists:media,id',
            'published_at'      => 'nullable|date',
            'translations'      => 'array',
            'translations.*.title'            => 'nullable|string|max:255',
            'translations.*.body'             => 'nullable|string',
            'translations.*.meta_description' => 'nullable|string|max:160',
        ]);
    }

    private function saveTranslations(Page $page, array $translations, int $userId): void
    {
        foreach ($translations as $locale => $data) {
            if (empty($data['title']) && empty($data['body'])) {
                continue;
            }

            $page->translations()->updateOrCreate(
                ['locale' => $locale],
                [
                    'title'            => $data['title'] ?? null,
                    'body'             => $data['body'] ?? null,
                    'meta_description' => $data['meta_description'] ?? null,
                    'translated_by'    => 'human',
                    'reviewed'         => true,
                ]
            );
        }
    }
}
