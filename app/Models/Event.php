<?php

namespace App\Models;

use App\Enums\ContentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    protected $fillable = [
        'slug', 'starts_at', 'ends_at', 'timezone', 'location',
        'online_url', 'status', 'published_at', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'status'       => ContentStatus::class,
        'starts_at'    => 'datetime',
        'ends_at'      => 'datetime',
        'published_at' => 'datetime',
    ];

    public function translations(): HasMany
    {
        return $this->hasMany(EventTranslation::class);
    }

    public function translation(string $locale): ?EventTranslation
    {
        return $this->translations->firstWhere('locale', $locale);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function isPublished(): bool
    {
        return $this->status === ContentStatus::Published
            && ($this->published_at === null || $this->published_at->isPast());
    }
}
