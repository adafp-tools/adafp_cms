<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    protected $fillable = [
        'disk', 'directory', 'filename', 'original_filename',
        'mime_type', 'extension', 'size', 'width', 'height',
        'variants', 'uploaded_by',
    ];

    protected $casts = [
        'variants' => 'array',
        'size'     => 'integer',
        'width'    => 'integer',
        'height'   => 'integer',
    ];

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function url(): string
    {
        return config('app.url') . Storage::url($this->directory . '/' . $this->filename);
    }

    public function variantUrl(string $variant): ?string
    {
        if (! isset($this->variants[$variant])) {
            return null;
        }
        return config('app.url') . Storage::url($this->variants[$variant]);
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function thumbnailUrl(): string
    {
        return $this->variantUrl('400w_webp') ?? $this->variantUrl('400w') ?? $this->url();
    }
}
