<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;

class UserPolicy
{
    public function manageSettings(User $user): bool
    {
        return $user->role->canManageSettings();
    }

    public function manageUsers(User $user): bool
    {
        return $user->role->canManageUsers();
    }

    public function publishContent(User $user): bool
    {
        return $user->role->canPublishContent();
    }

    public function viewSubmissions(User $user): bool
    {
        return $user->role->canViewSubmissions();
    }

    public function exportBackup(User $user): bool
    {
        return $user->role->canExportBackup();
    }

    public function viewHealth(User $user): bool
    {
        return $user->role->canManageSettings();
    }

    public function update(User $actor, User $target): bool
    {
        if ($actor->id === $target->id) return true;
        if ($target->isOwner()) return $actor->isOwner();
        return $actor->role->canManageUsers();
    }

    public function delete(User $actor, User $target): bool
    {
        if ($actor->id === $target->id) return false;
        if ($target->isOwner()) return $actor->isOwner();
        return $actor->role->canManageUsers();
    }
}
