<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class BackupCommand extends Command
{
    protected $signature   = 'cms:backup';
    protected $description = 'Backup the database and media files';

    public function handle(): int
    {
        $timestamp = date('Ymd-His');
        $backupDir = storage_path('backups');

        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        // 1. Database dump
        $driver = config('database.default');
        if ($driver === 'sqlite') {
            $this->info('SQLite detected — skipping mysqldump (not needed for SQLite).');
        } else {
            $host   = config('database.connections.mysql.host', '127.0.0.1');
            $port   = config('database.connections.mysql.port', '3306');
            $db     = config('database.connections.mysql.database');
            $user   = config('database.connections.mysql.username');
            $pass   = config('database.connections.mysql.password');

            $dbFile = "$backupDir/db-$timestamp.sql.gz";

            // Write a temporary .cnf file so the password is never on the CLI
            $cnfFile = tempnam(sys_get_temp_dir(), 'cms_my_');
            file_put_contents($cnfFile, "[client]\npassword=$pass\n");
            chmod($cnfFile, 0600);

            $cmd = "mysqldump --defaults-extra-file=$cnfFile --no-tablespaces"
                 . " -h" . escapeshellarg($host)
                 . " -P" . escapeshellarg($port)
                 . " -u" . escapeshellarg($user)
                 . " " . escapeshellarg($db)
                 . " | gzip > " . escapeshellarg($dbFile);

            exec($cmd, $output, $code);
            @unlink($cnfFile);

            if ($code !== 0) {
                $this->error('Database dump failed.');
                return self::FAILURE;
            }
            $this->info("Database backed up to backups/db-$timestamp.sql.gz");
        }

        // 2. Media backup
        $mediaDir = storage_path('app/public/media');
        if (is_dir($mediaDir)) {
            $mediaFile = "$backupDir/media-$timestamp.tar.gz";
            $cmd = "tar -czf " . escapeshellarg($mediaFile)
                 . " -C " . escapeshellarg(storage_path('app/public'))
                 . " media/";
            exec($cmd, $output, $code);
            if ($code !== 0) {
                $this->warn('Media archive failed — skipping.');
            } else {
                $this->info("Media backed up to backups/media-$timestamp.tar.gz");
            }
        }

        // 3. Retention — keep 10 most recent of each type
        foreach (['db-*.sql.gz', 'media-*.tar.gz'] as $pattern) {
            $files = glob("$backupDir/$pattern");
            if ($files && count($files) > 10) {
                sort($files);
                foreach (array_slice($files, 0, count($files) - 10) as $old) {
                    @unlink($old);
                }
            }
        }

        $this->info('Backup complete: db + media saved to storage/backups/');
        return self::SUCCESS;
    }
}
