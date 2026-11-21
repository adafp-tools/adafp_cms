<?php

namespace App\Models;

use App\Enums\PersonGroup;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Person extends Model
{
    protected $fillable = [
        'name', 'photo_id', 'sort', 'group', 'email', 'phone',
        'show_email', 'show_phone', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'group'      => PersonGroup::class,
        'show_email' => 'boolean',
        'show_phone' => 'boolean',
    ];

    public function translations(): HasMany
    {
        return $this->hasMany(PersonTranslation::class);
    }

    public function translation(string $locale): ?PersonTranslation
    {
        return $this->translations->firstWhere('locale', $locale);
    }

    public function photo(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'photo_id');
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
