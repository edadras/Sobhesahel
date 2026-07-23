<?php

namespace App\Filament\Resources\AdvertiseResource\Pages;

use App\Filament\Resources\AdvertiseResource;
use App\Filament\Resources\AdvertiseResource\Widgets\AdvertiseDailyStatsChart;
use App\Filament\Resources\AdvertiseResource\Widgets\AdvertiseStatsOverview;
use App\Models\Advertise;
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

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['positions'] = Advertise::getPositionsForAd($data['id'] ?? null);

        return $data;
    }

    /**
     * گزارش تبلیغات: totals + CTR + last-30-days chart for this ad.
     */
    protected function getFooterWidgets(): array
    {
        return [
            AdvertiseStatsOverview::make(['record' => $this->getRecord()]),
            AdvertiseDailyStatsChart::make(['record' => $this->getRecord()]),
        ];
    }
}
