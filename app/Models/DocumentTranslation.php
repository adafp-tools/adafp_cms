<?php

namespace App\Models;

use App\Enums\TranslatedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentTranslation extends Model
{
    protected $fillable = [
        'document_id', 'locale', 'title', 'description',
        'translated_by', 'reviewed',
    ];

    protected $casts = [
        'translated_by' => TranslatedBy::class,
        'reviewed'      => 'boolean',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }
}
