<?php

namespace App\Http\Controllers\Panel\Content;

use App\Enums\ContentStatus;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Services\SlugService;
use Illuminate\Http\Request;

class EventsController extends Controller
{
    public function __construct(private SlugService $slugs) {}

    public function index()
    {
        $events = Event::with('translations')->orderByDesc('starts_at')->paginate(30);

        return view('panel.content.events.index', compact('events'));
    }

    public function create()
    {
        $locales = config('cms.locales', ['en']);

        return view('panel.content.events.form', compact('locales'));
    }

    public function store(Request $request)
    {
        $data = $this->validateEvent($request);

        $defaultLocale = config('cms.default_locale', 'en');
        $titleForSlug  = $data['translations'][$defaultLocale]['title'] ?? 'event';

        $slug = $this->slugs->unique($data['slug'] ?: $titleForSlug, 'events');

        $event = Event::create([
            'slug'         => $slug,
            'starts_at'    => $data['starts_at'],
            'ends_at'      => $data['ends_at'] ?? null,
            'timezone'     => $data['timezone'] ?? 'UTC',
            'location'     => $data['location'] ?? null,
            'online_url'   => $data['online_url'] ?? null,
            'status'       => $data['status'],
            'published_at' => $data['published_at'] ?? null,
            'created_by'   => $request->user()->id,
            'updated_by'   => $request->user()->id,
        ]);

        $this->saveTranslations($event, $data['translations']);

        return redirect()->route('panel.events.index')->with('success', __('panel.event_created'));
    }

    public function edit(Event $event)
    {
        $event->load('translations');
        $locales = config('cms.locales', ['en']);

        return view('panel.content.events.form', compact('event', 'locales'));
    }

    public function update(Request $request, Event $event)
    {
        $data = $this->validateEvent($request);

        if ($data['slug'] && $data['slug'] !== $event->slug) {
            $data['slug'] = $this->slugs->unique($data['slug'], 'events', 'slug', $event->id);
        }

        $event->update([
            'slug'         => $data['slug'] ?: $event->slug,
            'starts_at'    => $data['starts_at'],
            'ends_at'      => $data['ends_at'] ?? null,
            'timezone'     => $data['timezone'] ?? 'UTC',
            'location'     => $data['location'] ?? null,
            'online_url'   => $data['online_url'] ?? null,
            'status'       => $data['status'],
            'published_at' => $data['published_at'] ?? null,
            'updated_by'   => $request->user()->id,
        ]);

        $this->saveTranslations($event, $data['translations']);

        return redirect()->route('panel.events.index')->with('success', __('panel.event_updated'));
    }

    public function destroy(Event $event)
    {
        $event->delete();

        return back()->with('success', __('panel.event_deleted'));
    }

    private function validateEvent(Request $request): array
    {
        return $request->validate([
            'slug'         => 'nullable|string|max:200|alpha_dash',
            'starts_at'    => 'required|date',
            'ends_at'      => 'nullable|date|after_or_equal:starts_at',
            'timezone'     => 'nullable|string|max:50',
            'location'     => 'nullable|string|max:255',
            'online_url'   => 'nullable|url|max:500',
            'status'       => 'required|in:' . implode(',', array_column(ContentStatus::cases(), 'value')),
            'published_at' => 'nullable|date',
            'translations' => 'array',
            'translations.*.title'          => 'nullable|string|max:255',
            'translations.*.description'    => 'nullable|string',
            'translations.*.location_label' => 'nullable|string|max:255',
        ]);
    }

    private function saveTranslations(Event $event, array $translations): void
    {
        foreach ($translations as $locale => $data) {
            if (empty($data['title'])) {
                continue;
            }

            $event->translations()->updateOrCreate(
                ['locale' => $locale],
                [
                    'title'          => $data['title'],
                    'description'    => $data['description'] ?? null,
                    'location_label' => $data['location_label'] ?? null,
                    'translated_by'  => 'human',
                    'reviewed'       => true,
                ]
            );
        }
    }
}
