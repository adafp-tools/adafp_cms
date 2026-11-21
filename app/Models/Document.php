<?php

namespace App\Models;

use App\Enums\DocumentCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Document extends Model
{
    protected $fillable = [
        'file', 'file_size', 'mime_type', 'category',
        'published_at', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'category'     => DocumentCategory::class,
        'file_size'    => 'integer',
        'published_at' => 'datetime',
    ];

    public function translations(): HasMany
    {
        return $this->hasMany(DocumentTranslation::class);
    }

    public function translation(string $locale): ?DocumentTranslation
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

    public function humanFileSize(): string
    {
        $bytes = $this->file_size;
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 1) . ' MB';
        }
        return round($bytes / 1024, 0) . ' KB';
    }
}
