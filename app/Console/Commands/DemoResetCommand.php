<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\AuditLog;
use App\Models\Document;
use App\Models\Event;
use App\Models\FormSubmission;
use App\Models\Gallery;
use App\Models\Menu;
use App\Models\Page;
use App\Models\PageView;
use App\Models\Person;
use App\Models\Post;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserInvitation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DemoResetCommand extends Command
{
    protected $signature = 'cms:demo-reset';
    protected $description = 'Reset the demo installation to a clean seeded state (demo instances only)';

    public function handle(): int
    {
        if (config('cms.instance') !== 'demo') {
            $this->error('This command only runs when CMS_INSTANCE=demo.');
            return self::FAILURE;
        }

        $email    = config('cms.demo_login_email', '');
        $password = config('cms.demo_login_password', '');

        if (empty($email) || empty($password)) {
            $this->error('CMS_DEMO_LOGIN_EMAIL and CMS_DEMO_LOGIN_PASSWORD must be set in .env.');
            return self::FAILURE;
        }

        $this->info('Demo reset starting…');

        // 1. Enable maintenance mode
        Artisan::call('down');

        try {
            DB::transaction(function () use ($email, $password) {
                $this->purgeContent();
                $this->recreateDemoUser($email, $password);
                $this->resetSettings();
            });

            // 2. Run the demo seeder (outside the transaction — it creates models)
            Artisan::call('db:seed', ['--class' => \Database\Seeders\DemoSeeder::class, '--force' => true]);

            // 3. Clear caches
            Artisan::call('cache:clear');
            Artisan::call('view:clear');

        } catch (\Throwable $e) {
            Artisan::call('up');
            $this->error('Reset failed: ' . $e->getMessage());
            Log::error('cms:demo-reset failed', ['error' => $e->getMessage()]);
            return self::FAILURE;
        }

        // 4. Bring site back up
        Artisan::call('up');

        Log::info('cms:demo-reset completed successfully.');
        $this->info('Demo reset complete.');

        return self::SUCCESS;
    }

    private function purgeContent(): void
    {
        // Delete all media files for demo content
        $mediaFiles = DB::table('media')->pluck('filename', 'directory');
        foreach ($mediaFiles as $dir => $filename) {
            Storage::disk('local')->delete($dir . '/' . $filename);
        }

        // Delete content in dependency order
        DB::table('post_translations')->delete();
        DB::table('page_translations')->delete();
        DB::table('event_translations')->delete();
        DB::table('person_translations')->delete();
        DB::table('document_translations')->delete();
        DB::table('gallery_image_translations')->delete();
        DB::table('menu_translations')->delete();

        DB::table('page_revisions')->delete();
        DB::table('post_revisions')->delete();
        DB::table('gallery_images')->delete();

        Post::query()->delete();
        Page::query()->delete();
        Event::query()->delete();
        Person::query()->delete();
        Document::query()->delete();
        Gallery::query()->delete();
        Menu::query()->delete();

        DB::table('media')->delete();

        FormSubmission::query()->delete();
        PageView::query()->delete();
        UserInvitation::query()->delete();

        // Remove demo account sessions and audit log entries
        $demoEmail = config('cms.demo_login_email', '');
        $demoUser  = User::where('email', $demoEmail)->first();
        if ($demoUser) {
            DB::table('sessions')->where('user_id', $demoUser->id)->delete();
            AuditLog::where('user_id', $demoUser->id)->delete();
            $demoUser->delete();
        }
    }

    private function recreateDemoUser(string $email, string $password): void
    {
        User::create([
            'name'     => 'Demo Editor',
            'email'    => $email,
            'password' => Hash::make($password),
            'role'     => UserRole::Editor,
        ]);
    }

    private function resetSettings(): void
    {
        $defaults = [
            'site_name'               => 'Progressive Citizens Party',
            'theme'                   => 'classic',
            'primary_color'           => '#1a3f7a',
            'secondary_color'         => '#c8a84b',
            'text_on_primary'         => '#ffffff',
            'default_locale'          => 'en',
            'active_locales'          => 'en,fr,ar,pt',
            'maintenance_mode'        => '0',
            'noindex'                 => '1',
            'page_counter_enabled'    => '1',
            'contact_form_enabled'    => '1',
            'join_form_enabled'       => '1',
            'notification_email'      => '',
            'notification_from_name'  => 'Progressive Citizens Party',
            'notification_from_email' => '',
            'privacy_policy_url'      => '',
            'crm_push_enabled'        => '0',
            'crm_sso_enabled'         => '0',
        ];

        foreach ($defaults as $key => $value) {
            Setting::set($key, $value);
        }
    }
}
