<?php

namespace App\Filament\Resources\GalleryResource\Pages;

use App\Filament\Resources\GalleryResource;
use App\Services\WatermarkService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGallery extends EditRecord
{
    protected static string $resource = GalleryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $applyWatermark = (bool) ($data['apply_watermark'] ?? false);
        unset($data['apply_watermark']);

        if ($applyWatermark) {
            $watermark = app(WatermarkService::class);
            $record = $this->getRecord();

            // Only watermark newly uploaded files, so files that are already
            // stored are never watermarked twice.
            if (
                ! empty($data['image_large'])
                && $data['image_large'] !== $record->image_large
            ) {
                $watermark->applyToUploadedFile($data['image_large']);
            }

            if (! empty($data['attachments']) && is_array($data['attachments'])) {
                $existing = (array) ($record->attachments ?? []);

                $watermark->applyToUploadedFiles(
                    array_values(array_diff($data['attachments'], $existing))
                );
            }
        }

        return $data;
    }
}
