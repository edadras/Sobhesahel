<?php

namespace App\Filament\Resources\GalleryResource\Pages;

use App\Filament\Resources\GalleryResource;
use App\Services\WatermarkService;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateGallery extends CreateRecord
{
    protected static string $resource = GalleryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $applyWatermark = (bool) ($data['apply_watermark'] ?? false);
        unset($data['apply_watermark']);

        if ($applyWatermark) {
            $watermark = app(WatermarkService::class);

            if (! empty($data['image_large'])) {
                $watermark->applyToUploadedFile($data['image_large']);
            }

            if (! empty($data['attachments']) && is_array($data['attachments'])) {
                $watermark->applyToUploadedFiles($data['attachments']);
            }
        }

        return $data;
    }
}
