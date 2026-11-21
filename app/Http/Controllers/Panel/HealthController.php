<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Services\ExposureTestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

class HealthController extends Controller
{
    public function __construct(private ExposureTestService $exposure) {}

    public function index(Request $request)
    {
        $this->authorize('viewHealth', \App\Models\User::class);

        $phpVersion   = PHP_VERSION;
        $dbVersion    = $this->dbVersion();
        $cmsVersion   = config('cms.version');
        $instance     = config('cms.instance');
        $cronLastRun  = \App\Models\Setting::get('cron_last_run');
        $queueBacklog = DB::table('jobs')->count();
        $diskFree     = $this->diskFree();
        $exposureResults = $this->exposure->run();
        $exposurePassed  = collect($exposureResults)->every(fn ($r) => $r['pass']);
        $storageWritable = is_writable(storage_path('app'));
        $lastBackup      = \App\Models\Setting::get('last_backup_at');

        return view('panel.health', compact(
            'phpVersion', 'dbVersion', 'cmsVersion', 'instance',
            'cronLastRun', 'queueBacklog', 'diskFree',
            'exposureResults', 'exposurePassed',
            'storageWritable', 'lastBackup'
        ));
    }

    public function mailTest(Request $request)
    {
        $this->authorize('viewHealth', \App\Models\User::class);

        try {
            \Illuminate\Support\Facades\Mail::raw(
                'This is a test email from the health page.',
                fn ($m) => $m->to($request->user()->email)
                              ->subject('Mail test — ' . config('cms.product_name'))
            );
            return back()->with('success', 'Test email sent to ' . $request->user()->email);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Health mail test failed: ' . $e->getMessage());
            return back()->withErrors(['mail' => 'Mail failed: ' . $e->getMessage()]);
        }
    }

    private function dbVersion(): string
    {
        try {
            return DB::selectOne('SELECT VERSION() as v')?->v ?? 'unknown';
        } catch (\Throwable) {
            return 'unknown';
        }
    }

    private function diskFree(): string
    {
        $bytes = @disk_free_space(storage_path());
        if (! $bytes) return 'unknown';
        return round($bytes / 1073741824, 1) . ' GB';
    }
}
