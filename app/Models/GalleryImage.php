<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GalleryImage extends Model
{
    protected $fillable = [
        'gallery_id', 'media_id', 'sort',
    ];

    protected $casts = [
        'sort' => 'integer',
    ];

    public function gallery(): BelongsTo
    {
        return $this->belongsTo(Gallery::class);
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(GalleryImageTranslation::class);
    }

    public function translation(string $locale): ?GalleryImageTranslation
    {
        return $this->translations->firstWhere('locale', $locale);
    }
}
