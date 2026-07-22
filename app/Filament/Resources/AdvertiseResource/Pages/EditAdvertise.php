<?php

namespace App\Filament\Resources\AdvertiseResource\Pages;

use App\Filament\Resources\AdvertiseResource;
use App\Models\Advertise;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAdvertise extends EditRecord
{
    protected static string $resource = AdvertiseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['positions'] = Advertise::getPositionsForAd($data['id'] ?? null);

        return $data;
    }

    protected function afterSave(): void
    {
        $this->record->syncPositions($this->data['positions'] ?? []);
    }
}
