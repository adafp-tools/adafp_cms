<?php

namespace App\Http\Controllers\Panel;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BackupsController extends Controller
{
    public function index(): View
    {
        $this->authorizeOwner();

        $dir   = storage_path('backups');
        $files = [];

        if (is_dir($dir)) {
            foreach (glob("$dir/{db,media}-*.{sql.gz,tar.gz}", GLOB_BRACE) as $path) {
                $files[] = [
                    'name'     => basename($path),
                    'size'     => filesize($path),
                    'modified' => filemtime($path),
                ];
            }
            usort($files, fn ($a, $b) => $b['modified'] <=> $a['modified']);
        }

        return view('panel.backups.index', compact('files'));
    }

    public function download(string $filename): StreamedResponse|RedirectResponse
    {
        $this->authorizeOwner();
        $path = $this->resolveBackupFile($filename);

        return response()->streamDownload(function () use ($path) {
            $handle = fopen($path, 'rb');
            while (!feof($handle)) {
                echo fread($handle, 8192);
            }
            fclose($handle);
        }, basename($path));
    }

    public function destroy(string $filename): RedirectResponse
    {
        $this->authorizeOwner();
        $path = $this->resolveBackupFile($filename);
        @unlink($path);

        return redirect()->route('panel.backups.index')
            ->with('success', 'Backup file deleted.');
    }

    public function run(Request $r): RedirectResponse
    {
        $this->authorizeOwner();
        Artisan::call('cms:backup');
        return redirect()->route('panel.backups.index')
            ->with('success', 'Backup started — check the list below.');
    }

    private function authorizeOwner(): void
    {
        $user = auth()->user();
        if (!$user || $user->role !== UserRole::Owner) {
            abort(403);
        }
    }

    private function resolveBackupFile(string $filename): string
    {
        $safe = basename($filename);
        if (!preg_match('/^(db|media)-[\d\-]+\.(sql\.gz|tar\.gz)$/', $safe)) {
            abort(404);
        }
        $path = storage_path("backups/$safe");
        if (!file_exists($path)) {
            abort(404);
        }
        return $path;
    }
}
