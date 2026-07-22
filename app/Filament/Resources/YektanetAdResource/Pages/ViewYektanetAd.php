<?php

namespace App\Filament\Resources\YektanetAdResource\Pages;

use App\Filament\Resources\YektanetAdResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewYektanetAd extends ViewRecord
{
    protected static string $resource = YektanetAdResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
