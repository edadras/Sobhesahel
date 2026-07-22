<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Applies the "صبح ساحل" watermark to uploaded images using PHP GD.
 *
 * - The original (pristine) file is preserved in a configurable directory
 *   on the same disk before the watermark is applied in place.
 * - JPEG / PNG / WebP inputs are supported; anything else is skipped.
 * - Any failure (missing GD, missing watermark file, corrupt image, ...)
 *   is logged and skipped silently so uploads never break.
 *
 * Configuration lives in config/watermark.php.
 */
class WatermarkService
{
    /**
     * Watermark a file that Filament stored on a disk (path relative to the
     * disk root). Keeps an untouched copy of the original file.
     *
     * @param string|null $relativePath path as stored by the FileUpload field
     * @param string|null $disk         storage disk name (defaults to Filament's default disk)
     */
    public function applyToUploadedFile(?string $relativePath, ?string $disk = null): bool
    {
        if (blank($relativePath)) {
            return false;
        }

        $disk = $disk ?: config('filament.default_filesystem_disk', 'public');

        try {
            $storage = Storage::disk($disk);

            if (! $storage->exists($relativePath)) {
                Log::warning("Watermark: uploaded file not found on disk [{$disk}]: {$relativePath}");

                return false;
            }

            // Keep the pristine original before touching the file.
            $originalCopy = $this->preserveOriginal($disk, $relativePath);

            $applied = $this->apply($storage->path($relativePath));

            if (! $applied && $originalCopy !== null) {
                // Nothing was changed – no need to keep a duplicate around.
                $storage->delete($originalCopy);
            }

            return $applied;
        } catch (Throwable $e) {
            Log::warning('Watermark: failed for "' . $relativePath . '": ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Watermark several stored files at once (e.g. gallery attachments).
     *
     * @param array<int, string|null> $relativePaths
     */
    public function applyToUploadedFiles(array $relativePaths, ?string $disk = null): void
    {
        foreach ($relativePaths as $path) {
            if (is_string($path) && $path !== '') {
                $this->applyToUploadedFile($path, $disk);
            }
        }
    }

    /**
     * Apply the watermark to an image file (absolute filesystem path),
     * overwriting it in place. Returns true when the file was modified.
     */
    public function apply(string $absolutePath): bool
    {
        try {
            if (! extension_loaded('gd')) {
                Log::warning('Watermark: GD extension is not available, skipping.');

                return false;
            }

            if (! is_file($absolutePath)) {
                Log::warning("Watermark: image not found: {$absolutePath}");

                return false;
            }

            $watermarkPath = $this->resolveWatermarkPath();

            if ($watermarkPath === null) {
                return false;
            }

            $info = @getimagesize($absolutePath);

            if ($info === false) {
                Log::warning("Watermark: unreadable image, skipping: {$absolutePath}");

                return false;
            }

            $mime = $info['mime'] ?? '';

            $image = $this->loadImage($absolutePath, $mime);

            if ($image === null) {
                Log::info("Watermark: unsupported image type ({$mime}), skipping: {$absolutePath}");

                return false;
            }

            $baseWidth = imagesx($image);
            $baseHeight = imagesy($image);

            $minWidth = (int) config('watermark.min_width', 200);

            if ($baseWidth < $minWidth) {
                imagedestroy($image);

                return false;
            }

            $watermark = $this->prepareWatermark($watermarkPath, $baseWidth);

            if ($watermark === null) {
                imagedestroy($image);

                return false;
            }

            [$dstX, $dstY] = $this->resolvePosition(
                $baseWidth,
                $baseHeight,
                imagesx($watermark),
                imagesy($watermark)
            );

            imagealphablending($image, true);
            imagecopy($image, $watermark, $dstX, $dstY, 0, 0, imagesx($watermark), imagesy($watermark));
            imagedestroy($watermark);

            $saved = $this->saveImage($image, $absolutePath, $mime);
            imagedestroy($image);

            return $saved;
        } catch (Throwable $e) {
            Log::warning('Watermark: failed for "' . $absolutePath . '": ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Copy the untouched upload into the originals directory on the same
     * disk. Returns the relative path of the copy (or null on failure).
     */
    protected function preserveOriginal(string $disk, string $relativePath): ?string
    {
        try {
            $storage = Storage::disk($disk);

            $dir = trim((string) config('watermark.originals_dir', 'watermark-originals'), '/');
            $target = $dir . '/' . basename($relativePath);

            if ($storage->exists($target)) {
                $target = $dir . '/' . pathinfo($relativePath, PATHINFO_FILENAME)
                    . '_' . uniqid()
                    . '.' . pathinfo($relativePath, PATHINFO_EXTENSION);
            }

            return $storage->copy($relativePath, $target) ? $target : null;
        } catch (Throwable $e) {
            Log::warning('Watermark: could not preserve original of "' . $relativePath . '": ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Resolve the configured watermark image to an absolute path.
     */
    protected function resolveWatermarkPath(): ?string
    {
        $configured = (string) config('watermark.image', 'asset/img/logo.png');

        $path = is_file($configured) ? $configured : public_path($configured);

        if (! is_file($path)) {
            Log::warning("Watermark: watermark image not found: {$configured}");

            return null;
        }

        return $path;
    }

    /**
     * @return \GdImage|null
     */
    protected function loadImage(string $path, string $mime)
    {
        $image = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($path),
            'image/png' => @imagecreatefrompng($path),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : null,
            default => null,
        };

        return $image !== false ? $image : null;
    }

    /**
     * Load, scale and fade the watermark for a base image of the given width.
     *
     * @return \GdImage|null
     */
    protected function prepareWatermark(string $watermarkPath, int $baseWidth)
    {
        $info = @getimagesize($watermarkPath);

        if ($info === false) {
            Log::warning("Watermark: unreadable watermark image: {$watermarkPath}");

            return null;
        }

        $source = $this->loadImage($watermarkPath, $info['mime'] ?? '');

        if ($source === null) {
            Log::warning("Watermark: unsupported watermark image type: {$watermarkPath}");

            return null;
        }

        imagealphablending($source, false);
        imagesavealpha($source, true);

        $scale = (float) config('watermark.scale', 0.18);
        $scale = max(0.01, min(1.0, $scale));

        $targetWidth = max(1, (int) round($baseWidth * $scale));
        $targetHeight = max(1, (int) round(imagesy($source) * $targetWidth / imagesx($source)));

        $scaled = imagecreatetruecolor($targetWidth, $targetHeight);
        imagealphablending($scaled, false);
        imagesavealpha($scaled, true);
        imagefill($scaled, 0, 0, imagecolorallocatealpha($scaled, 0, 0, 0, 127));

        imagecopyresampled(
            $scaled,
            $source,
            0, 0, 0, 0,
            $targetWidth, $targetHeight,
            imagesx($source), imagesy($source)
        );
        imagedestroy($source);

        $opacity = (int) config('watermark.opacity', 55);
        $opacity = max(0, min(100, $opacity));

        if ($opacity < 100) {
            $this->fade($scaled, $opacity / 100);
        }

        return $scaled;
    }

    /**
     * Multiply the alpha channel of an image by the given opacity (0 - 1),
     * preserving the image's own transparency. The watermark is small, so a
     * per-pixel pass is cheap.
     *
     * @param \GdImage $image
     */
    protected function fade($image, float $opacity): void
    {
        $width = imagesx($image);
        $height = imagesy($image);

        imagealphablending($image, false);
        imagesavealpha($image, true);

        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                $rgba = imagecolorat($image, $x, $y);
                $alpha = ($rgba >> 24) & 0x7F;

                if ($alpha >= 127) {
                    continue; // already fully transparent
                }

                // GD alpha: 0 = opaque, 127 = transparent.
                $newAlpha = 127 - (int) round((127 - $alpha) * $opacity);

                $color = imagecolorallocatealpha(
                    $image,
                    ($rgba >> 16) & 0xFF,
                    ($rgba >> 8) & 0xFF,
                    $rgba & 0xFF,
                    min(127, max(0, $newAlpha))
                );

                imagesetpixel($image, $x, $y, $color);
            }
        }
    }

    /**
     * @return array{0: int, 1: int} [x, y] of the watermark's top-left corner
     */
    protected function resolvePosition(int $baseWidth, int $baseHeight, int $wmWidth, int $wmHeight): array
    {
        $padding = (int) config('watermark.padding', 24);
        $position = (string) config('watermark.position', 'bottom-right');

        return match ($position) {
            'top-left' => [$padding, $padding],
            'top-right' => [$baseWidth - $wmWidth - $padding, $padding],
            'bottom-left' => [$padding, $baseHeight - $wmHeight - $padding],
            'center' => [
                (int) round(($baseWidth - $wmWidth) / 2),
                (int) round(($baseHeight - $wmHeight) / 2),
            ],
            default => [$baseWidth - $wmWidth - $padding, $baseHeight - $wmHeight - $padding], // bottom-right
        };
    }

    /**
     * @param \GdImage $image
     */
    protected function saveImage($image, string $path, string $mime): bool
    {
        $quality = (int) config('watermark.quality', 90);
        $quality = max(0, min(100, $quality));

        return match ($mime) {
            'image/jpeg' => imagejpeg($image, $path, $quality),
            'image/png' => (function () use ($image, $path) {
                imagealphablending($image, false);
                imagesavealpha($image, true);

                return imagepng($image, $path);
            })(),
            'image/webp' => function_exists('imagewebp') ? imagewebp($image, $path, $quality) : false,
            default => false,
        };
    }
}
