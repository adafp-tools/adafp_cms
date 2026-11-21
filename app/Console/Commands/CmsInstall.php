<?php

namespace App\Console\Commands;

use App\Actions\Fortify\CreateNewUser;
use App\Services\ExposureTestService;
use App\Services\InstallerService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class CmsInstall extends Command
{
    protected $signature   = 'cms:install';
    protected $description = 'Run the CMS installer interactively (for SSH installations)';

    public function __construct(
        private InstallerService    $installer,
        private ExposureTestService $exposure,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        if ($this->installer->isInstalled()) {
            $this->error('Already installed. Remove storage/installed to reinstall.');
            return 1;
        }

        $this->info('=== ' . config('cms.product_name') . ' Installer ===');

        // Requirements check
        $this->info('Checking requirements...');
        $checks = $this->installer->checkRequirements();
        foreach ($checks as $check) {
            $icon = $check['pass'] ? '✓' : (($check['warning'] ?? false) ? '⚠' : '✗');
            $this->line("  {$icon} {$check['label']} ({$check['found']})");
        }
        if (! $this->installer->allRequirementsMet($checks)) {
            $this->error('Some requirements are not met. Fix them before continuing.');
            return 1;
        }
        $this->line('');

        // Database
        $this->info('Database configuration');
        $dbData = [
            'host'     => $this->ask('DB Host', '127.0.0.1'),
            'port'     => $this->ask('DB Port', '3306'),
            'database' => $this->ask('DB Name'),
            'username' => $this->ask('DB Username'),
            'password' => $this->secret('DB Password'),
        ];

        $this->line('Testing connection...');
        $result = $this->installer->testDatabaseConnection($dbData);
        if ($result !== true) {
            $this->error("Connection failed: {$result}");
            return 1;
        }
        $this->info('Database connection OK');
        $this->line('');

        // Site details
        $this->info('Site details');
        $orgName       = $this->ask('Organisation name');
        $defaultLocale = $this->choice('Default language', ['en', 'fr', 'pt', 'ar'], 0);
        $siteUrl       = $this->ask('Site URL', 'https://' . gethostname());
        $timezone      = $this->ask('Timezone', 'Africa/Khartoum');
        $contactEmail  = $this->ask('Contact email');
        $this->line('');

        // Owner account
        $this->info('Owner account');
        $ownerName  = $this->ask('Owner full name');
        $ownerEmail = $this->ask('Owner email');
        $ownerPwd   = $this->secret('Owner password (min 12 chars)');
        $this->line('');

        // Write .env
        $this->line('Writing .env...');
        $this->installer->writeEnv([
            'org_name'       => $orgName,
            'default_locale' => $defaultLocale,
            'url'            => $siteUrl,
            'timezone'       => $timezone,
            'contact_email'  => $contactEmail,
            'db_host'        => $dbData['host'],
            'db_port'        => $dbData['port'],
            'db_database'    => $dbData['database'],
            'db_username'    => $dbData['username'],
            'db_password'    => $dbData['password'],
        ]);
        $this->installer->cacheConfig();

        // Run migrations
        $this->line('Running migrations...');
        if (! $this->installer->runMigrations()) {
            $this->error('Migrations failed.');
            return 1;
        }

        // Create owner
        try {
            (new CreateNewUser)->create([
                'name'                  => $ownerName,
                'email'                 => $ownerEmail,
                'password'              => $ownerPwd,
                'password_confirmation' => $ownerPwd,
                'role'                  => 'owner',
            ]);
        } catch (\Throwable $e) {
            $this->error('Could not create owner: ' . $e->getMessage());
            return 1;
        }

        // Store settings
        \App\Models\Setting::set('active_locales', json_encode([$defaultLocale]));
        \App\Models\Setting::set('org_name', $orgName);

        // Exposure test
        $this->line('Running exposure test...');
        $exposureResults = $this->exposure->run($siteUrl);
        $allPass = true;
        foreach ($exposureResults as $r) {
            $icon = $r['pass'] ? '✓' : '✗';
            $this->line("  {$icon} {$r['path']} (HTTP {$r['status']})");
            if (! $r['pass']) $allPass = false;
        }

        if (! $allPass) {
            $this->warn('Exposure test has failures. Fix your hosting before going live.');
        }

        $this->installer->markInstalled();
        $this->info('');
        $this->info('Installation complete!');
        $this->line("Admin panel: {$siteUrl}/panel/login");
        $this->line("Cron:        * * * * * php " . base_path('artisan') . ' schedule:run >> /dev/null 2>&1');
        $this->warn('Important: set up 2FA on first login.');

        return 0;
    }
}
