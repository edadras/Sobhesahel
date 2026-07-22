<?php

namespace App\Filament\Resources\NewsResource\Pages;

use App\Filament\Resources\NewsResource;
use App\Services\WatermarkService;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateNews extends CreateRecord
{
    protected static string $resource = NewsResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $applyWatermark = (bool) ($data['apply_watermark'] ?? false);
        unset($data['apply_watermark']);

        if ($applyWatermark && ! empty($data['image_original'])) {
            app(WatermarkService::class)->applyToUploadedFile($data['image_original']);
        }

        return $data;
    }
}
