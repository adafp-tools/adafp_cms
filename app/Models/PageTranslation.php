<?php

namespace App\Models;

use App\Enums\TranslatedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageTranslation extends Model
{
    protected $fillable = [
        'page_id', 'locale', 'title', 'body', 'meta_description',
        'translated_by', 'reviewed',
    ];

    protected $casts = [
        'translated_by' => TranslatedBy::class,
        'reviewed'      => 'boolean',
    ];

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }
}
