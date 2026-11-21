<?php

namespace App\Enums;

enum UserRole: string
{
    case Owner      = 'owner';
    case Admin      = 'admin';
    case Editor     = 'editor';
    case Translator = 'translator';

    public function label(): string
    {
        return match ($this) {
            self::Owner      => 'Owner',
            self::Admin      => 'Admin',
            self::Editor     => 'Editor',
            self::Translator => 'Translator',
        };
    }

    public function canManageSettings(): bool
    {
        return in_array($this, [self::Owner, self::Admin]);
    }

    public function canManageUsers(): bool
    {
        return in_array($this, [self::Owner, self::Admin]);
    }

    public function canPublishContent(): bool
    {
        return in_array($this, [self::Owner, self::Admin, self::Editor]);
    }

    public function canEditTranslations(): bool
    {
        return true;
    }

    public function canViewSubmissions(): bool
    {
        return in_array($this, [self::Owner, self::Admin]);
    }

    public function canExportBackup(): bool
    {
        return $this === self::Owner;
    }

    public function require2FA(): bool
    {
        return in_array($this, [self::Owner, self::Admin]);
    }
}
