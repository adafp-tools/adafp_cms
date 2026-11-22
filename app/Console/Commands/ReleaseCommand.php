<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use ZipArchive;

class ReleaseCommand extends Command
{
    protected $signature = 'cms:release
                            {version? : Version string (defaults to VERSION file)}
                            {--skip-build : Skip npm build step (for CI/testing)}
                            {--skip-tests : Skip test suite (for CI where tests ran separately)}';

    protected $description = 'Build a release ZIP of the CMS (excluding dev files and secrets)';

    private array $excludeDirs = [
        '.git', 'node_modules', 'vendor',
        'storage/app/public', 'storage/framework', 'storage/logs',
        'tests', '.claude',
    ];

    private array $excludeFiles = [
        '.gitignore', '.env', '.env.example', 'CLAUDE.md',
        'scripts/deploy.sh', 'scripts/provision-tenant.sh',
    ];

    public function handle(): int
    {
        $base = base_path();

        // 1. Resolve version
        $version = $this->argument('version')
            ?? trim(file_get_contents($base . '/VERSION'));
        if (empty($version)) {
            $this->error('Could not determine version.');
            return self::FAILURE;
        }

        // 2. Neutrality check
        // Pattern split to avoid the check triggering on this file itself.
        $pattern = 'ada' . 'fp';
        $this->info('Running neutrality check...');
        $grep = "grep -rni \"$pattern\""
            . ' --include="*.php" --include="*.blade.php"'
            . ' --include="*.js" --include="*.css"'
            . ' --include="*.json" --include="*.md"'
            . ' --exclude-dir=.git --exclude-dir=docs'
            . ' --exclude-dir=tests --exclude-dir=storage'
            . ' --exclude=CLAUDE.md --exclude=CHANGELOG.md .';
        exec("cd $base && $grep 2>/dev/null", $neutralOutput, $neutralCode);
        if (!empty($neutralOutput)) {
            $this->error('Neutrality check FAILED — branding references found.');
            foreach ($neutralOutput as $line) {
                $this->line("  $line");
            }
            return self::FAILURE;
        }
        $this->info('Neutrality check passed.');

        // 3. Run tests (skippable for CI pipelines that run tests separately)
        if (!$this->option('skip-tests')) {
            $this->info('Running tests...');
            exec("cd $base && php artisan test --stop-on-failure 2>&1", $testOut, $testCode);
            if ($testCode !== 0) {
                $this->error('Tests FAILED.');
                foreach ($testOut as $line) {
                    $this->line("  $line");
                }
                return self::FAILURE;
            }
            $this->info('Tests passed.');
        }

        // 4. npm build (skippable)
        if (!$this->option('skip-build')) {
            $this->info('Building assets...');
            exec("cd $base && npm run build 2>&1", $npmOut, $npmCode);
            if ($npmCode !== 0) {
                $this->error('npm run build FAILED.');
                foreach ($npmOut as $line) {
                    $this->line("  $line");
                }
                return self::FAILURE;
            }
            $this->info('Assets built.');
        }

        // 5. Build ZIP
        $date     = date('Ymd');
        $filename = "cms-{$version}-{$date}.zip";
        $relDir   = storage_path('releases');
        if (!is_dir($relDir)) {
            mkdir($relDir, 0755, true);
        }
        $zipPath = "$relDir/$filename";

        $this->info("Building ZIP: $filename ...");
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            $this->error("Could not create ZIP at $zipPath");
            return self::FAILURE;
        }

        $this->addDirectory($zip, $base, $base);
        $zip->close();

        $this->info("Release ZIP built: storage/releases/$filename");

        // 6. Retention — keep only 5 most recent
        $zips = glob("$relDir/cms-*.zip");
        if ($zips && count($zips) > 5) {
            sort($zips);
            foreach (array_slice($zips, 0, count($zips) - 5) as $old) {
                @unlink($old);
            }
        }

        return self::SUCCESS;
    }

    private function addDirectory(ZipArchive $zip, string $dir, string $base): void
    {
        $items = array_diff(scandir($dir), ['.', '..']);
        foreach ($items as $item) {
            $fullPath    = "$dir/$item";
            $relative    = ltrim(str_replace($base, '', $fullPath), '/');

            if ($this->isExcluded($relative, $item)) {
                continue;
            }

            if (is_dir($fullPath)) {
                $zip->addEmptyDir($relative);
                $this->addDirectory($zip, $fullPath, $base);
            } elseif (is_file($fullPath)) {
                // Skip map files
                if (str_ends_with($item, '.map') || str_ends_with($item, '.output')) {
                    continue;
                }
                $zip->addFile($fullPath, $relative);
            }
        }
    }

    private function isExcluded(string $relative, string $item): bool
    {
        foreach ($this->excludeDirs as $dir) {
            if ($relative === $dir || str_starts_with($relative, $dir . '/')) {
                return true;
            }
        }
        foreach ($this->excludeFiles as $file) {
            if ($relative === $file) {
                return true;
            }
        }
        return false;
    }
}
