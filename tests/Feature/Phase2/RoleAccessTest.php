<?php

namespace Tests\Feature\Phase2;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    private function user(UserRole $role): User
    {
        return User::factory()->create([
            'role' => $role,
            'two_factor_confirmed_at' => now(),
        ]);
    }

    // --- manageSettings ---
    public function test_owner_can_manage_settings(): void
    {
        $this->assertTrue($this->user(UserRole::Owner)->role->canManageSettings());
    }

    public function test_admin_can_manage_settings(): void
    {
        $this->assertTrue($this->user(UserRole::Admin)->role->canManageSettings());
    }

    public function test_editor_cannot_manage_settings(): void
    {
        $this->assertFalse($this->user(UserRole::Editor)->role->canManageSettings());
    }

    public function test_translator_cannot_manage_settings(): void
    {
        $this->assertFalse($this->user(UserRole::Translator)->role->canManageSettings());
    }

    // --- manageUsers ---
    public function test_owner_can_manage_users(): void
    {
        $this->assertTrue($this->user(UserRole::Owner)->role->canManageUsers());
    }

    public function test_admin_can_manage_users(): void
    {
        $this->assertTrue($this->user(UserRole::Admin)->role->canManageUsers());
    }

    public function test_editor_cannot_manage_users(): void
    {
        $this->assertFalse($this->user(UserRole::Editor)->role->canManageUsers());
    }

    // --- publishContent ---
    public function test_editor_can_publish_content(): void
    {
        $this->assertTrue($this->user(UserRole::Editor)->role->canPublishContent());
    }

    public function test_translator_cannot_publish_content(): void
    {
        $this->assertFalse($this->user(UserRole::Translator)->role->canPublishContent());
    }

    // --- editTranslations: all roles ---
    public function test_all_roles_can_edit_translations(): void
    {
        foreach (UserRole::cases() as $role) {
            $this->assertTrue($this->user($role)->role->canEditTranslations(), "Failed for {$role->value}");
        }
    }

    // --- viewSubmissions ---
    public function test_owner_can_view_submissions(): void
    {
        $this->assertTrue($this->user(UserRole::Owner)->role->canViewSubmissions());
    }

    public function test_editor_cannot_view_submissions(): void
    {
        $this->assertFalse($this->user(UserRole::Editor)->role->canViewSubmissions());
    }

    // --- exportBackup ---
    public function test_only_owner_can_export_backup(): void
    {
        $this->assertTrue($this->user(UserRole::Owner)->role->canExportBackup());
        $this->assertFalse($this->user(UserRole::Admin)->role->canExportBackup());
        $this->assertFalse($this->user(UserRole::Editor)->role->canExportBackup());
        $this->assertFalse($this->user(UserRole::Translator)->role->canExportBackup());
    }

    // --- require2FA ---
    public function test_owner_requires_2fa(): void
    {
        $this->assertTrue($this->user(UserRole::Owner)->role->require2FA());
    }

    public function test_admin_requires_2fa(): void
    {
        $this->assertTrue($this->user(UserRole::Admin)->role->require2FA());
    }

    public function test_editor_does_not_require_2fa(): void
    {
        $this->assertFalse($this->user(UserRole::Editor)->role->require2FA());
    }

    public function test_translator_does_not_require_2fa(): void
    {
        $this->assertFalse($this->user(UserRole::Translator)->role->require2FA());
    }
}
