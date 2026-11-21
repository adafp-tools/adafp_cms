<?php

namespace App\Helpers;

use App\Models\Media;
use Illuminate\Support\Facades\URL;

class SeoHelper
{
    /**
     * Build hreflang alternate links for a given route with locale swapped.
     *
     * @param  array<string> $activeLocales
     * @return array<string, string>
     */
    public static function hreflangLinks(string $routeName, array $params, array $activeLocales): array
    {
        $links = [];
        foreach ($activeLocales as $lang) {
            try {
                $links[$lang] = route($routeName, array_merge($params, ['locale' => $lang]));
            } catch (\Throwable) {
                // Route doesn't accept locale param — skip
            }
        }
        return $links;
    }

    /**
     * Build canonical URL for the current page.
     *
     * @param  array<string, mixed> $params
     */
    public static function canonical(string $routeName, array $params): string
    {
        try {
            return route($routeName, $params);
        } catch (\Throwable) {
            return URL::current();
        }
    }

    /**
     * Get the OG image URL from a Media model, falling back to null.
     */
    public static function ogImage(?Media $media): ?string
    {
        if (! $media) {
            return null;
        }
        return $media->variantUrl('800w') ?? $media->url();
    }
}
