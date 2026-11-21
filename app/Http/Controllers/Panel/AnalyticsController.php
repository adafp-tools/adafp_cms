<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\PageView;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    public function index(): View
    {
        $topPages = PageView::selectRaw('path, COUNT(*) as views')
            ->groupBy('path')
            ->orderByDesc('views')
            ->limit(20)
            ->get();

        $totalViews = PageView::count();

        return view('panel.analytics', compact('topPages', 'totalViews'));
    }
}
