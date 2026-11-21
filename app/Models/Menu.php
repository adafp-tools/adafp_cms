<?php

namespace App\Models;

use App\Enums\MenuLocation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    protected $fillable = [
        'location', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'location' => MenuLocation::class,
    ];

    public function translations(): HasMany
    {
        return $this->hasMany(MenuTranslation::class);
    }

    public function translation(string $locale): ?MenuTranslation
    {
        return $this->translations->firstWhere('locale', $locale);
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
