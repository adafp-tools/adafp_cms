<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class UserInvitation extends Model
{
    protected $fillable = [
        'email', 'role', 'token', 'invited_by', 'expires_at', 'accepted_at',
    ];

    protected function casts(): array
    {
        return [
            'role'        => UserRole::class,
            'expires_at'  => 'datetime',
            'accepted_at' => 'datetime',
        ];
    }

    public static function generate(string $email, UserRole $role, int $invitedBy): static
    {
        return static::create([
            'email'      => strtolower($email),
            'role'       => $role,
            'token'      => Str::random(64),
            'invited_by' => $invitedBy,
            'expires_at' => now()->addDays(7),
        ]);
    }

    public function isPending(): bool
    {
        return is_null($this->accepted_at) && $this->expires_at->isFuture();
    }

    public function inviter(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }
}
