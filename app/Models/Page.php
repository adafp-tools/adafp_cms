<?php

namespace App\Models;

use App\Enums\ContentStatus;
use App\Enums\PageTemplate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Page extends Model
{
    protected $fillable = [
        'slug', 'parent_id', 'template', 'status', 'sort',
        'show_in_menu', 'featured_image_id', 'published_at',
        'created_by', 'updated_by',
    ];

    protected $casts = [
        'template'     => PageTemplate::class,
        'status'       => ContentStatus::class,
        'show_in_menu' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function translations(): HasMany
    {
        return $this->hasMany(PageTranslation::class);
    }

    public function translation(string $locale): ?PageTranslation
    {
        return $this->translations->firstWhere('locale', $locale);
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(PageRevision::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Page::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Page::class, 'parent_id');
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
