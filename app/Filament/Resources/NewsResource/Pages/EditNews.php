<?php

namespace App\Filament\Resources\NewsResource\Pages;

use App\Filament\Resources\NewsResource;
use App\Services\WatermarkService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditNews extends EditRecord
{
    protected static string $resource = NewsResource::class;

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

        // Only watermark a newly uploaded image, so an unchanged image is
        // never watermarked twice.
        if (
            $applyWatermark
            && ! empty($data['image_original'])
            && $data['image_original'] !== $this->getRecord()->image_original
        ) {
            app(WatermarkService::class)->applyToUploadedFile($data['image_original']);
        }

        return $data;
    }
}
