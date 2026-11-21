<?php

namespace App\Models;

use App\Enums\ContentStatus;
use App\Enums\PostType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug', 'type', 'status', 'published_at',
        'featured_image_id', 'author_name', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'type'         => PostType::class,
        'status'       => ContentStatus::class,
        'published_at' => 'datetime',
    ];

    public function translations(): HasMany
    {
        return $this->hasMany(PostTranslation::class);
    }

    public function translation(string $locale): ?PostTranslation
    {
        return $this->translations->firstWhere('locale', $locale);
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(PostRevision::class);
    }

    public function featuredImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'featured_image_id');
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
