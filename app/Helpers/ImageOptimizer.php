<?php
/**
 * GitHub: RoyalHaze
 * Date: 6/10/25
 * Time: 4:20 AM
 **/

namespace App\Helpers;

use App\Constant\AppConstant;
use Illuminate\Support\Facades\Storage;
use Exception;
use Imagick;

class ImageOptimizer
{
    /**
     * Convert and resize an image to WebP format, with optional deletion of original file.
     *
     * @param string $disk Filesystem disk (e.g. 'public')
     * @param string $path Path to the original image on disk
     * @param string $destinationDir Directory for saving converted WebP images
     * @param int $quality WebP image quality (0–100)
     * @param bool $deleteOriginal Whether to delete the original image after conversion
     * @param bool $isDirectPath Whether the path is a direct filesystem path
     * @param string|null $parentClass Parent class name to determine path prefix
     * @return array URLs and sizes of converted WebP images
     * @throws Exception
     */
    public function convertImageToWebP(
        string $path,
        string $destinationDir,
        int $quality = 80,
        bool $deleteOriginal = false,
        string $disk = 'public',
        bool $isDirectPath = false,
        ?string $parentClass = null
    ): array {
        $localPath = $isDirectPath ? $path : Storage::disk($disk)->path($path);
        $pathPrefix = $this->getPathPrefix($parentClass);
        $destinationDir = $pathPrefix ? "{$destinationDir}{$pathPrefix}" : $destinationDir;



        $image = $this->loadImage($localPath);
        $filename = pathinfo($path, PATHINFO_FILENAME);

        $sizes = $this->getTargetSizes($image);
        $outputPaths = [];

        foreach ($sizes as $label => [$width, $height]) {
            $webpBinary = $this->resizeAndConvertToWebP($image, $width, $height, $quality);
            $outputFilename = "$destinationDir/{$filename}_{$label}.webp";

            if ($isDirectPath) {
                $dir = dirname($outputFilename);
                if (!is_dir($dir)) {
                    mkdir($dir, 0777, true);
                }
                file_put_contents($outputFilename, $webpBinary);
                $relativePath = str_replace(Storage::disk($disk)->path(''), '', $outputFilename);
                $outputPaths[$label] = [
                    'path' => $relativePath,
                    'size' => strlen($webpBinary),
                    'width' => $width,
                    'height' => $height
                ];
            } else {
                Storage::disk($disk)->put($outputFilename, $webpBinary);
                $outputPaths[$label] = [
                    'path' => $outputFilename,
                    'size' => strlen($webpBinary),
                    'width' => $width,
                    'height' => $height
                ];
            }
        }

        $image->clear();
        $image->destroy();

        if ($deleteOriginal) {
            if ($isDirectPath) {
                if (file_exists($path)) {
                    unlink($path);
                }
            } else {
                Storage::disk($disk)->delete($path);
            }
        }

        return $outputPaths;
    }

    /**
     * Get path prefix from parent class name
     *
     * @param string|null $parentClass
     * @return string|null
     */
    protected function getPathPrefix(?string $parentClass): ?string
    {
        if (!$parentClass) {
            return null;
        }

        // Get the model mapping from AppConstant
        $modelMap = AppConstant::filament_model_map();

        // Convert class name to lowercase and remove namespace
        $className = strtolower(class_basename($parentClass));

        // Remove 'Resource' suffix if present
        $className = str_replace('resource', '', $className);

        // Check if the class name exists in the model map
        foreach ($modelMap as $key => $resourceClass) {
            if (strtolower(class_basename($resourceClass)) === $className . 'resource') {
                return $key;
            }
        }

        return $prefixMap[$className] ?? null;
    }

    protected function loadImage(string $path): Imagick
    {
        if (!extension_loaded('imagick')) {
            throw new Exception("Imagick extension is not installed.");
        }

        if (!file_exists($path)) {
            throw new Exception("Image not found at: $path");
        }

        return new Imagick($path);
    }

    protected function getTargetSizes(Imagick $image): array
    {
        $width = $image->getImageWidth();
        $height = $image->getImageHeight();

        return [
            'original' => [$width, $height],
            'small' => [800, intval(($height * 800) / $width)],
            'thumbnail' => [150, 150],
        ];
    }

    protected function resizeAndConvertToWebP(Imagick $image, int $width, int $height, int $quality): string
    {
        $clone = clone $image;
        $clone->resizeImage($width, $height, Imagick::FILTER_LANCZOS, 1, true);
        $clone->setImageFormat('webp');
        $clone->setImageCompression(Imagick::COMPRESSION_WEBP);
        $clone->setImageCompressionQuality($quality);
        $clone->setOption('webp:method', '6');

        $blob = $clone->getImagesBlob();

        $clone->clear();
        $clone->destroy();

        return $blob;
    }
}
