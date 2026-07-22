<?php

namespace App\Filament\Resources\AdvertiseResource\Pages;

use App\Filament\Resources\AdvertiseResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewAdvertise extends ViewRecord
{
    protected static string $resource = AdvertiseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
