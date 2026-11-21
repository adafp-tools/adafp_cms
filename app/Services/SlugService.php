<?php

namespace App\Services;

use Cocur\Slugify\Slugify;

class SlugService
{
    private Slugify $slugify;

    public function __construct()
    {
        $this->slugify = new Slugify(['rulesets' => ['default', 'arabic']]);
    }

    public function make(string $text, string $fallbackBase = 'item'): string
    {
        $slug = $this->slugify->slugify($text);

        if ($slug === '') {
            $slug = $fallbackBase . '-' . substr(md5($text . microtime()), 0, 8);
        }

        return $slug;
    }

    public function unique(string $text, string $table, string $column = 'slug', ?int $exceptId = null, string $fallbackBase = 'item'): string
    {
        $base = $this->make($text, $fallbackBase);
        $slug = $base;
        $i    = 1;

        while ($this->slugExists($table, $column, $slug, $exceptId)) {
            $slug = $base . '-' . $i;
            $i++;
        }

        return $slug;
    }

    private function slugExists(string $table, string $column, string $slug, ?int $exceptId): bool
    {
        $query = \DB::table($table)->where($column, $slug);

        if ($exceptId !== null) {
            $query->where('id', '!=', $exceptId);
        }

        return $query->exists();
    }
}
