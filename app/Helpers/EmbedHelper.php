<?php

namespace App\Helpers;

use App\Http\Middleware\SecurityHeaders;

class EmbedHelper
{
    private static array $patterns = [
        'youtube'  => '/youtube-nocookie\.com|youtube\.com\/embed/i',
        'facebook' => '/facebook\.com\/plugins\/video|facebook\.com\/video/i',
        'x'        => '/twitter\.com\/i\/|x\.com\/i\//i',
    ];

    private static array $hosts = [
        'youtube'  => 'https://www.youtube-nocookie.com',
        'facebook' => 'https://www.facebook.com',
        'x'        => 'https://twitter.com',
    ];

    private static array $labels = [
        'youtube'  => 'YouTube',
        'facebook' => 'Facebook',
        'x'        => 'X (Twitter)',
    ];

    /**
     * Replace <iframe> embed tags with click-to-load placeholder divs.
     * Also registers embed hosts into the request's CSP list.
     */
    public static function transform(string $html): string
    {
        return preg_replace_callback(
            '/<iframe[^>]+src=["\']([^"\']+)["\'][^>]*>.*?<\/iframe>/is',
            function (array $m) {
                $src  = $m[1];
                $type = self::detectType($src);

                if ($type === null) {
                    return $m[0]; // not a known embed — leave unchanged
                }

                // Add host to CSP via the request
                try {
                    $request = app('request');
                    SecurityHeaders::addEmbedHost($request, self::$hosts[$type]);
                } catch (\Throwable) {
                    // Not in HTTP context (e.g. tests) — ignore
                }

                $label     = self::$labels[$type];
                $safeType  = e($type);
                $safeSrc   = e($src);
                $safeLabel = e($label);

                return <<<HTML
<div class="embed-placeholder" data-src="{$safeSrc}" data-type="{$safeType}"
     x-data="embedPlaceholder()"
     x-on:click="load()"
     role="button"
     tabindex="0"
     x-on:keydown.enter="load()"
     x-on:keydown.space.prevent="load()"
     aria-label="Load {$safeLabel} embed">
  <div class="flex flex-col items-center justify-center gap-3 p-6 bg-gray-100 rounded cursor-pointer hover:bg-gray-200 transition-colors min-h-[200px]">
    <span class="text-4xl" aria-hidden="true">▶</span>
    <span class="font-medium">Load {$safeLabel} embed</span>
    <p class="text-xs text-gray-500">Content from {$safeLabel} will be loaded when you click.</p>
  </div>
</div>
HTML;
            },
            $html
        ) ?? $html;
    }

    private static function detectType(string $src): ?string
    {
        foreach (self::$patterns as $type => $pattern) {
            if (preg_match($pattern, $src)) {
                return $type;
            }
        }
        return null;
    }
}
