<?php

namespace App\Filament\Resources\YektanetAdResource\Pages;

use App\Filament\Resources\YektanetAdResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditYektanetAd extends EditRecord
{
    protected static string $resource = YektanetAdResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
