<?php

namespace App\Models;

use App\Enums\TranslatedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GalleryTranslation extends Model
{
    protected $fillable = [
        'gallery_id', 'locale', 'title',
        'translated_by', 'reviewed',
    ];

    protected $casts = [
        'translated_by' => TranslatedBy::class,
        'reviewed'      => 'boolean',
    ];

    public function gallery(): BelongsTo
    {
        return $this->belongsTo(Gallery::class);
    }
}
