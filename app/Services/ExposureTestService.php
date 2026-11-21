<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ExposureTestService
{
    private const PATHS = [
        '/.env',
        '/storage/logs/laravel.log',
        '/composer.json',
        '/vendor/',
        '/storage/app/backups/',
    ];

    public function run(string $baseUrl = ''): array
    {
        $baseUrl = rtrim($baseUrl ?: config('app.url'), '/');
        $results = [];

        foreach (self::PATHS as $path) {
            $url    = $baseUrl . $path;
            $status = $this->probe($url);
            $results[] = [
                'path'   => $path,
                'url'    => $url,
                'status' => $status,
                'pass'   => in_array($status, [403, 404]),
            ];
        }

        return $results;
    }

    public function allPass(string $baseUrl = ''): bool
    {
        return collect($this->run($baseUrl))->every(fn ($r) => $r['pass']);
    }

    private function probe(string $url): int
    {
        try {
            $response = Http::timeout(5)
                ->withoutVerifying()
                ->get($url);
            return $response->status();
        } catch (\Throwable) {
            return 0;
        }
    }
}
