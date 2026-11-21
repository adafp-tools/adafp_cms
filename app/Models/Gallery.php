<?php

namespace App\Models;

use App\Enums\ContentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Gallery extends Model
{
    protected $fillable = [
        'slug', 'status', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'status' => ContentStatus::class,
    ];

    public function translations(): HasMany
    {
        return $this->hasMany(GalleryTranslation::class);
    }

    public function translation(string $locale): ?GalleryTranslation
    {
        return $this->translations->firstWhere('locale', $locale);
    }

    public function images(): HasMany
    {
        return $this->hasMany(GalleryImage::class)->orderBy('sort');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
