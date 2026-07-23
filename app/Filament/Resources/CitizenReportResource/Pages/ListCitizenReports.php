<?php

namespace App\Filament\Resources\CitizenReportResource\Pages;

use App\Filament\Resources\CitizenReportResource;
use App\Models\CitizenReport;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListCitizenReports extends ListRecords
{
    protected static string $resource = CitizenReportResource::class;

    /**
     * کارتابل شهروند خبرنگار: جدید / در حال بررسی / تبدیل‌شده / ردشده.
     */
    public function getTabs(): array
    {
        return [
            'new' => Tab::make('جدید')
                ->badge(fn () => CitizenReport::where('status', CitizenReport::STATUS_NEW)->count())
                ->badgeColor('info')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', CitizenReport::STATUS_NEW)),
            'reviewing' => Tab::make('در حال بررسی')
                ->badge(fn () => CitizenReport::where('status', CitizenReport::STATUS_REVIEWING)->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', CitizenReport::STATUS_REVIEWING)),
            'accepted' => Tab::make('تبدیل‌شده به خبر')
                ->badge(fn () => CitizenReport::where('status', CitizenReport::STATUS_ACCEPTED)->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', CitizenReport::STATUS_ACCEPTED)),
            'rejected' => Tab::make('ردشده')
                ->badge(fn () => CitizenReport::where('status', CitizenReport::STATUS_REJECTED)->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', CitizenReport::STATUS_REJECTED)),
            'all' => Tab::make('همه'),
        ];
    }
}
