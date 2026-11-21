<?php

namespace App\Models;

use App\Enums\TranslatedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GalleryImageTranslation extends Model
{
    protected $fillable = [
        'gallery_image_id', 'locale', 'caption',
        'translated_by', 'reviewed',
    ];

    protected $casts = [
        'translated_by' => TranslatedBy::class,
        'reviewed'      => 'boolean',
    ];

    public function galleryImage(): BelongsTo
    {
        return $this->belongsTo(GalleryImage::class);
    }
}
