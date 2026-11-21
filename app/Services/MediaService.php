<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;

class MediaService
{
    private const MAX_DIMENSION = 1600;
    private const VARIANTS      = [400, 800];
    private const FORBIDDEN_MIME = ['image/svg+xml', 'image/svg'];

    private ImageManager $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver());
    }

    public function store(UploadedFile $file, int $uploadedBy): Media
    {
        $mime = $file->getMimeType();

        if (in_array($mime, self::FORBIDDEN_MIME, true)) {
            throw new \InvalidArgumentException('SVG uploads are not permitted.');
        }

        $maxMb = (int) config('cms.media_max_size_mb', 10);
        if ($file->getSize() > $maxMb * 1024 * 1024) {
            throw new \InvalidArgumentException("File exceeds the {$maxMb} MB limit.");
        }

        $dir      = 'media/' . now()->format('Y/m');
        $ext      = strtolower($file->getClientOriginalExtension() ?: $file->guessExtension());
        $filename = \Str::uuid() . '.' . $ext;

        $isImage  = str_starts_with($mime, 'image/');
        $width    = null;
        $height   = null;
        $variants = [];

        if ($isImage) {
            $img = $this->manager->decodePath($file->getRealPath());

            if ($img->width() > self::MAX_DIMENSION || $img->height() > self::MAX_DIMENSION) {
                $img->scaleDown(self::MAX_DIMENSION, self::MAX_DIMENSION);
            }

            $width  = $img->width();
            $height = $img->height();

            $encoded = $img->encode(new JpegEncoder(85));
            Storage::put($dir . '/' . $filename, (string) $encoded);

            foreach (self::VARIANTS as $w) {
                if ($img->width() > $w) {
                    $thumb   = clone $img;
                    $thumb->scaleDown($w);
                    $varName = $dir . '/' . pathinfo($filename, PATHINFO_FILENAME) . "_{$w}w.webp";
                    Storage::put($varName, (string) $thumb->encode(new WebpEncoder(80)));
                    $variants["{$w}w_webp"] = $varName;
                }
            }
        } else {
            $file->storeAs($dir, $filename);
        }

        return Media::create([
            'disk'              => config('filesystems.default', 'local'),
            'directory'         => $dir,
            'filename'          => $filename,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type'         => $mime,
            'extension'         => $ext,
            'size'              => $file->getSize(),
            'width'             => $width,
            'height'            => $height,
            'variants'          => $variants ?: null,
            'uploaded_by'       => $uploadedBy,
        ]);
    }

    public function delete(Media $media): void
    {
        Storage::delete($media->directory . '/' . $media->filename);

        if ($media->variants) {
            foreach ($media->variants as $path) {
                Storage::delete($path);
            }
        }

        $media->delete();
    }
}
