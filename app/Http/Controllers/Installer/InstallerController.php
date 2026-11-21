<?php

namespace App\Http\Controllers\Installer;

use App\Actions\Fortify\CreateNewUser;
use App\Http\Controllers\Controller;
use App\Services\ExposureTestService;
use App\Services\InstallerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InstallerController extends Controller
{
    public function __construct(
        private InstallerService    $installer,
        private ExposureTestService $exposure,
    ) {}

    public function index()
    {
        return redirect()->route('installer.requirements');
    }

    // Step 1: requirements
    public function requirements()
    {
        $checks = $this->installer->checkRequirements();
        return view('installer.requirements', compact('checks'));
    }

    // Step 2: database
    public function database()
    {
        return view('installer.database');
    }

    public function databaseTest(Request $request)
    {
        $data = $request->validate([
            'db_host'     => 'required|string',
            'db_port'     => 'required|integer|min:1|max:65535',
            'db_database' => 'required|string',
            'db_username' => 'required|string',
            'db_password' => 'nullable|string',
        ]);

        $result = $this->installer->testDatabaseConnection([
            'host'     => $data['db_host'],
            'port'     => $data['db_port'],
            'database' => $data['db_database'],
            'username' => $data['db_username'],
            'password' => $data['db_password'] ?? '',
        ]);

        if ($result === true) {
            session(['installer.db' => $data]);
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'error' => $result]);
    }

    // Step 3: site basics
    public function site()
    {
        if (! session('installer.db')) {
            return redirect()->route('installer.database');
        }
        $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http')
             . '://' . ($_SERVER['HTTP_HOST'] ?? 'yoursite.example.com');
        return view('installer.site', ['defaultUrl' => $url]);
    }

    public function storeSite(Request $request)
    {
        $data = $request->validate([
            'org_name'       => 'required|string|max:200',
            'default_locale' => 'required|in:en,fr,pt,ar',
            'active_locales' => 'required|array|min:1',
            'active_locales.*' => 'in:en,fr,pt,ar',
            'site_url'       => 'required|url',
            'timezone'       => 'required|timezone',
            'contact_email'  => 'required|email',
        ]);
        session(['installer.site' => $data]);
        return redirect()->route('installer.owner');
    }

    // Step 4: owner account
    public function owner()
    {
        if (! session('installer.site')) {
            return redirect()->route('installer.site');
        }
        return view('installer.owner');
    }

    public function storeOwner(Request $request)
    {
        $data = $request->validate([
            'name'                  => 'required|string|max:200',
            'email'                 => 'required|email',
            'password'              => 'required|string|min:12|confirmed',
        ]);
        session(['installer.owner' => $data]);
        return redirect()->route('installer.finalize');
    }

    // Step 5: finalize (write .env, migrate, exposure test)
    public function finalize()
    {
        if (! session('installer.owner')) {
            return redirect()->route('installer.owner');
        }

        $db   = session('installer.db');
        $site = session('installer.site');

        $envData = [
            'org_name'       => $site['org_name'],
            'default_locale' => $site['default_locale'],
            'url'            => $site['site_url'],
            'timezone'       => $site['timezone'],
            'contact_email'  => $site['contact_email'],
            'db_host'        => $db['db_host'],
            'db_port'        => $db['db_port'],
            'db_database'    => $db['db_database'],
            'db_username'    => $db['db_username'],
            'db_password'    => $db['db_password'] ?? '',
        ];

        // Write .env and re-bootstrap config
        $this->installer->writeEnv($envData);
        $this->installer->cacheConfig();

        // Run migrations
        $migrated = $this->installer->runMigrations();

        // Create owner
        $ownerData = session('installer.owner');
        $user = null;
        if ($migrated) {
            try {
                $user = (new CreateNewUser)->create([
                    'name'                  => $ownerData['name'],
                    'email'                 => $ownerData['email'],
                    'password'              => $ownerData['password'],
                    'password_confirmation' => $ownerData['password'],
                    'role'                  => 'owner',
                ]);
            } catch (\Throwable $e) {
                $migrated = false;
            }
        }

        // Store active locales in settings
        if ($migrated && $user) {
            \App\Models\Setting::set('active_locales', json_encode($site['active_locales']));
            \App\Models\Setting::set('org_name', $site['org_name']);
        }

        // Exposure test
        $exposureResults = $this->exposure->run($site['site_url']);
        $exposurePassed  = collect($exposureResults)->every(fn ($r) => $r['pass']);

        // Storage test
        $storageOk = $this->testStorage($site['site_url']);

        $allPassed = $migrated && $exposurePassed && $storageOk;

        if ($allPassed) {
            $this->installer->markInstalled();
            session()->forget(['installer.db', 'installer.site', 'installer.owner']);
        }

        return view('installer.finalize', compact(
            'migrated', 'exposureResults', 'exposurePassed', 'storageOk', 'allPassed', 'user', 'site'
        ));
    }

    // Step 6: complete
    public function complete()
    {
        $panelPath = config('cms.panel_path', 'panel');
        $cronLine  = '* * * * * php ' . base_path('artisan') . ' schedule:run >> /dev/null 2>&1';
        return view('installer.complete', compact('panelPath', 'cronLine'));
    }

    private function testStorage(string $siteUrl): bool
    {
        try {
            $testFile = 'installer-test-' . uniqid() . '.txt';
            $path     = storage_path('app/public/' . $testFile);
            file_put_contents($path, 'ok');
            @symlink(storage_path('app/public'), public_path('storage'));

            $url      = rtrim($siteUrl, '/') . '/storage/' . $testFile;
            $response = \Illuminate\Support\Facades\Http::timeout(5)->withoutVerifying()->get($url);
            @unlink($path);

            return $response->successful();
        } catch (\Throwable) {
            return false;
        }
    }
}
