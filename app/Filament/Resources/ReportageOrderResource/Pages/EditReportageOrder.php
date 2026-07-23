<?php

namespace App\Filament\Resources\ReportageOrderResource\Pages;

use App\Filament\Resources\ReportageOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditReportageOrder extends EditRecord
{
    protected static string $resource = ReportageOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
