<?php

namespace App\Filament\Resources\ArchiveCategoryResource\Pages;

use App\Filament\Resources\ArchiveCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListArchiveCategories extends ListRecords
{
    protected static string $resource = ArchiveCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
