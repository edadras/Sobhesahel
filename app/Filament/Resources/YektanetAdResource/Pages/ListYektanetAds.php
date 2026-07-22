<?php

namespace App\Filament\Resources\YektanetAdResource\Pages;

use App\Filament\Resources\YektanetAdResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListYektanetAds extends ListRecords
{
    protected static string $resource = YektanetAdResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
