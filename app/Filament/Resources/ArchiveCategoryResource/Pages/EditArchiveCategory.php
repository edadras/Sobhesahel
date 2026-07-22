<?php

namespace App\Filament\Resources\ArchiveCategoryResource\Pages;

use App\Filament\Resources\ArchiveCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditArchiveCategory extends EditRecord
{
    protected static string $resource = ArchiveCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
