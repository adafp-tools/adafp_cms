<?php

namespace App\Http\Controllers\Panel\Content;

use App\Enums\PersonGroup;
use App\Http\Controllers\Controller;
use App\Models\Person;
use Illuminate\Http\Request;

class PeopleController extends Controller
{
    public function index()
    {
        $people = Person::with('translations')->orderBy('sort')->paginate(30);

        return view('panel.content.people.index', compact('people'));
    }

    public function create()
    {
        $locales = config('cms.locales', ['en']);
        $groups  = PersonGroup::cases();

        return view('panel.content.people.form', compact('locales', 'groups'));
    }

    public function store(Request $request)
    {
        $data = $this->validatePerson($request);

        $person = Person::create([
            'name'       => $data['name'],
            'photo_id'   => $data['photo_id'] ?? null,
            'sort'       => $data['sort'] ?? 0,
            'group'      => $data['group'],
            'email'      => $data['email'] ?? null,
            'phone'      => $data['phone'] ?? null,
            'show_email' => $data['show_email'] ?? false,
            'show_phone' => $data['show_phone'] ?? false,
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]);

        $this->saveTranslations($person, $data['translations']);

        return redirect()->route('panel.people.index')->with('success', __('panel.person_created'));
    }

    public function edit(Person $person)
    {
        $person->load('translations');
        $locales = config('cms.locales', ['en']);
        $groups  = PersonGroup::cases();

        return view('panel.content.people.form', compact('person', 'locales', 'groups'));
    }

    public function update(Request $request, Person $person)
    {
        $data = $this->validatePerson($request);

        $person->update([
            'name'       => $data['name'],
            'photo_id'   => $data['photo_id'] ?? null,
            'sort'       => $data['sort'] ?? $person->sort,
            'group'      => $data['group'],
            'email'      => $data['email'] ?? null,
            'phone'      => $data['phone'] ?? null,
            'show_email' => $data['show_email'] ?? false,
            'show_phone' => $data['show_phone'] ?? false,
            'updated_by' => $request->user()->id,
        ]);

        $this->saveTranslations($person, $data['translations']);

        return redirect()->route('panel.people.index')->with('success', __('panel.person_updated'));
    }

    public function destroy(Person $person)
    {
        $person->delete();

        return back()->with('success', __('panel.person_deleted'));
    }

    private function validatePerson(Request $request): array
    {
        return $request->validate([
            'name'       => 'required|string|max:150',
            'photo_id'   => 'nullable|exists:media,id',
            'sort'       => 'nullable|integer|min:0',
            'group'      => 'required|in:' . implode(',', array_column(PersonGroup::cases(), 'value')),
            'email'      => 'nullable|email|max:150',
            'phone'      => 'nullable|string|max:30',
            'show_email' => 'boolean',
            'show_phone' => 'boolean',
            'translations'          => 'array',
            'translations.*.position' => 'nullable|string|max:150',
            'translations.*.bio'      => 'nullable|string',
        ]);
    }

    private function saveTranslations(Person $person, array $translations): void
    {
        foreach ($translations as $locale => $data) {
            if (empty($data['position']) && empty($data['bio'])) {
                continue;
            }

            $person->translations()->updateOrCreate(
                ['locale' => $locale],
                [
                    'position'      => $data['position'] ?? null,
                    'bio'           => $data['bio'] ?? null,
                    'translated_by' => 'human',
                    'reviewed'      => true,
                ]
            );
        }
    }
}
