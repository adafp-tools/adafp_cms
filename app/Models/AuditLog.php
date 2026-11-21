<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLog extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'user_id', 'user_name', 'action',
        'target_type', 'target_id', 'target_label',
        'ip_hash', 'created_at',
    ];

    public static function record(
        string $action,
        ?string $targetType = null,
        ?int $targetId = null,
        ?string $targetLabel = null,
    ): void {
        $user = Auth::user();
        static::create([
            'user_id'      => $user?->id,
            'user_name'    => $user?->name,
            'action'       => $action,
            'target_type'  => $targetType,
            'target_id'    => $targetId,
            'target_label' => $targetLabel,
            'ip_hash'      => hash('sha256', Request::ip() . config('app.key')),
            'created_at'   => now(),
        ]);
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
