<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Response;

class RobotsController extends Controller
{
    public function index(): Response
    {
        $noindex = config('cms.instance') === 'demo'
                || Setting::get('maintenance_mode', '0') === '1'
                || Setting::get('noindex', '0') === '1';

        $content = $noindex
            ? "User-agent: *\nDisallow: /"
            : "User-agent: *\nAllow: /\nSitemap: " . url('/sitemap.xml');

        return response($content, 200)
            ->header('Content-Type', 'text/plain; charset=utf-8');
    }
}
