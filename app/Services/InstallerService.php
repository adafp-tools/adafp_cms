<?php

namespace App\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InstallerService
{
    private const LOCK_FILE = 'installed';

    private const REQUIRED_EXTENSIONS = [
        'pdo', 'pdo_mysql', 'mbstring', 'openssl', 'tokenizer',
        'xml', 'ctype', 'json', 'bcmath', 'fileinfo', 'gd', 'zip', 'intl',
    ];

    public function isInstalled(): bool
    {
        return file_exists(storage_path(self::LOCK_FILE));
    }

    public function markInstalled(): void
    {
        file_put_contents(storage_path(self::LOCK_FILE), now()->toIso8601String());
    }

    public function checkRequirements(): array
    {
        $results = [];

        $results['php_version'] = [
            'label' => 'PHP version ≥ 8.2',
            'pass'  => version_compare(PHP_VERSION, '8.2.0', '>='),
            'found' => PHP_VERSION,
        ];

        foreach (self::REQUIRED_EXTENSIONS as $ext) {
            $results["ext_{$ext}"] = [
                'label' => "PHP extension: {$ext}",
                'pass'  => extension_loaded($ext),
                'found' => extension_loaded($ext) ? 'loaded' : 'missing',
            ];
        }

        $results['storage_writable'] = [
            'label' => 'storage/ directory writable',
            'pass'  => is_writable(storage_path()),
            'found' => is_writable(storage_path()) ? 'writable' : 'not writable',
        ];

        $results['bootstrap_cache_writable'] = [
            'label' => 'bootstrap/cache/ directory writable',
            'pass'  => is_writable(base_path('bootstrap/cache')),
            'found' => is_writable(base_path('bootstrap/cache')) ? 'writable' : 'not writable',
        ];

        $results['https'] = [
            'label'   => 'HTTPS connection',
            'pass'    => $this->isHttps(),
            'found'   => $this->isHttps() ? 'detected' : 'not detected (recommended)',
            'warning' => true,
        ];

        return $results;
    }

    public function allRequirementsMet(array $checks): bool
    {
        return collect($checks)
            ->reject(fn ($c) => $c['warning'] ?? false)
            ->every(fn ($c) => $c['pass']);
    }

    public function testDatabaseConnection(array $db): bool|string
    {
        try {
            config([
                'database.connections.installer_test' => [
                    'driver'   => 'mysql',
                    'host'     => $db['host'],
                    'port'     => $db['port'] ?? 3306,
                    'database' => $db['database'],
                    'username' => $db['username'],
                    'password' => $db['password'],
                    'charset'  => 'utf8mb4',
                    'prefix'   => '',
                ],
            ]);
            DB::connection('installer_test')->getPdo();
            DB::purge('installer_test');
            return true;
        } catch (\Throwable $e) {
            DB::purge('installer_test');
            return $e->getMessage();
        }
    }

    public function writeEnv(array $data): void
    {
        $key  = 'base64:' . base64_encode(random_bytes(32));
        $path = base_path('.env');

        $lines = [
            "APP_NAME=\"{$data['org_name']}\"",
            "APP_ENV=production",
            "APP_KEY={$key}",
            "APP_DEBUG=false",
            "APP_TIMEZONE=\"{$data['timezone']}\"",
            "APP_URL={$data['url']}",
            "",
            "APP_LOCALE={$data['default_locale']}",
            "APP_FALLBACK_LOCALE=en",
            "",
            "LOG_CHANNEL=stack",
            "LOG_STACK=single",
            "LOG_LEVEL=error",
            "",
            "DB_CONNECTION=mysql",
            "DB_HOST={$data['db_host']}",
            "DB_PORT={$data['db_port']}",
            "DB_DATABASE={$data['db_database']}",
            "DB_USERNAME={$data['db_username']}",
            "DB_PASSWORD={$data['db_password']}",
            "",
            "SESSION_DRIVER=file",
            "SESSION_LIFETIME=120",
            "SESSION_ENCRYPT=true",
            "",
            "FILESYSTEM_DISK=local",
            "QUEUE_CONNECTION=database",
            "CACHE_STORE=file",
            "",
            "MAIL_MAILER=smtp",
            "MAIL_FROM_ADDRESS=\"{$data['contact_email']}\"",
            "MAIL_FROM_NAME=\"{$data['org_name']}\"",
            "",
            "CMS_INSTANCE=production",
            "CMS_PANEL_PATH=panel",
        ];

        file_put_contents($path, implode("\n", $lines) . "\n");
    }

    public function runMigrations(): bool
    {
        try {
            Artisan::call('migrate', ['--force' => true, '--no-interaction' => true]);
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    public function cacheConfig(): void
    {
        try {
            Artisan::call('config:clear');
            Artisan::call('config:cache');
        } catch (\Throwable) {
        }
    }

    private function isHttps(): bool
    {
        return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || ($_SERVER['SERVER_PORT'] ?? '') === '443'
            || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    }
}
