<?php

namespace App\Filament\Resources\HomeBoxResource\Pages;

use App\Filament\Resources\HomeBoxResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHomeBoxes extends ListRecords
{
    protected static string $resource = HomeBoxResource::class;

    public function getSubheading(): ?string
    {
        return 'در صورت تعریف حداقل یک باکس فعال، چیدمان پیش‌فرض صفحه اصلی با باکس‌های تعریف‌شده جایگزین می‌شود؛ در غیر این صورت چیدمان پیش‌فرض نمایش داده می‌شود.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
