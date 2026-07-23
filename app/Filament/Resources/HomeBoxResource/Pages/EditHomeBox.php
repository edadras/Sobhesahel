<?php

namespace App\Filament\Resources\HomeBoxResource\Pages;

use App\Filament\Resources\HomeBoxResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHomeBox extends EditRecord
{
    protected static string $resource = HomeBoxResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
