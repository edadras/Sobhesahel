<?php
/**
 * GitHub: RoyalHaze
 * Date: 6/10/25
 * Time: 3:40 AM
 **/

namespace App\Traits;
use Illuminate\Support\Facades\Storage;
use Imagick;
use Exception;
trait ImageOptimizer
{
    public function optimize_image()
    {
        $original_image_path = Storage::disk('public')->path($this->image_large);

        if (!file_exists($original_image_path)){
            $original_image_path = Storage::disk('media')->path($this->image_large);
        }

        $original_image_info = getimagesize($original_image_path);

        $optimized_image_path = Storage::disk('media')->path('');

        $op = new \App\Helpers\ImageOptimizer();

        $optimized_images = $op->convertImageToWebP(
            path: $original_image_path,
            destinationDir: $optimized_image_path,
            isDirectPath: true,
            parentClass: get_class($this)
        );

        // Add original image information
        $optimized_images['original_image'] = [
            'path' => $this->image_large,
            'size' => filesize($original_image_path),
            'width' => $original_image_info[0],
            'height' => $original_image_info[1]
        ];

        // Fix paths to be relative to media directory
        foreach ($optimized_images as $key => $image) {
            if (isset($image['path'])) {
                $path = $image['path'];
                // Extract the part after 'media/'
                if (preg_match('/media\/(.*)/', $path, $matches)) {
                    $optimized_images[$key]['path'] = $matches[1];
                }
            }
        }

        $this->setTitleValue('original_image',$this->image_large);

        $this->update([
           'image_large' => $optimized_images['original']['path'],
           'image_medium' => $optimized_images['small']['path'],
           'image_small' => $optimized_images['thumbnail']['path'],
        ]);

        return $optimized_images;
    }
}
