<?php

namespace App\Models;

use App\Enums\TranslatedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuTranslation extends Model
{
    protected $fillable = [
        'menu_id', 'locale', 'items', 'translated_by', 'reviewed',
    ];

    protected $casts = [
        'items'         => 'array',
        'translated_by' => TranslatedBy::class,
        'reviewed'      => 'boolean',
    ];

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }
}
