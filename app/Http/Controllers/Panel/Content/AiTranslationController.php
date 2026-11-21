<?php

namespace App\Http\Controllers\Panel\Content;

use App\Http\Controllers\Controller;
use App\Services\AiTranslationService;
use Illuminate\Http\Request;

class AiTranslationController extends Controller
{
    public function translate(Request $request)
    {
        $data = $request->validate([
            'text'          => 'required|string|max:50000',
            'target_locale' => 'required|string|max:10',
            'source_locale' => 'nullable|string|max:10',
        ]);

        try {
            $service    = app(AiTranslationService::class);
            $translated = $service->translate(
                $data['text'],
                $data['target_locale'],
                $data['source_locale'] ?? config('cms.default_locale', 'en')
            );
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        return response()->json(['text' => $translated]);
    }
}
