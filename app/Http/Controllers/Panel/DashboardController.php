<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $recentActivity = AuditLog::with('user')
            ->latest('created_at')
            ->limit(20)
            ->get();

        return view('panel.dashboard', compact('recentActivity'));
    }
}
