<?php

namespace App\Models;

use App\Enums\TranslatedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostTranslation extends Model
{
    protected $fillable = [
        'post_id', 'locale', 'title', 'summary', 'body', 'meta_description',
        'translated_by', 'reviewed',
    ];

    protected $casts = [
        'translated_by' => TranslatedBy::class,
        'reviewed'      => 'boolean',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
