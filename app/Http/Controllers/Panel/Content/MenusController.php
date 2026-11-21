<?php

namespace App\Http\Controllers\Panel\Content;

use App\Enums\MenuLocation;
use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Http\Request;

class MenusController extends Controller
{
    public function index()
    {
        $menus = Menu::with('translations')->get();

        return view('panel.content.menus.index', compact('menus'));
    }

    public function edit(Menu $menu)
    {
        $menu->load('translations');
        $locales = config('cms.locales', ['en']);

        return view('panel.content.menus.edit', compact('menu', 'locales'));
    }

    public function update(Request $request, Menu $menu)
    {
        $data = $request->validate([
            'translations'        => 'array',
            'translations.*.items' => 'nullable|json',
        ]);

        foreach ($data['translations'] ?? [] as $locale => $localeData) {
            $items = $localeData['items'] ?? '[]';
            $menu->translations()->updateOrCreate(
                ['locale' => $locale],
                [
                    'items'         => json_decode($items, true) ?? [],
                    'translated_by' => 'human',
                    'reviewed'      => true,
                ]
            );
        }

        $menu->update(['updated_by' => $request->user()->id]);

        return back()->with('success', __('panel.menu_updated'));
    }
}
