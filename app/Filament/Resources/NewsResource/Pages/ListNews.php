<?php

namespace App\Filament\Resources\NewsResource\Pages;

use App\Filament\Resources\NewsResource;
use App\Models\News;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Kainiklas\FilamentScout\Traits\InteractsWithScout;

class ListNews extends ListRecords
{
    use InteractsWithScout;

    protected static string $resource = NewsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    /**
     * کارتابل‌های خبری — editorial cartables as tabs.
     */
    public function getTabs(): array
    {
        return [
            'all' => Tab::make('همه')
                ->modifyQueryUsing(fn (Builder $query) => $query->withoutTrashed()),
            'draft' => Tab::make('پیش‌نویس')
                ->badge(fn () => News::where('status', 'draft')->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->withoutTrashed()->where('status', 'draft')),
            'scheduled' => Tab::make('زمان‌بندی‌شده')
                ->badge(fn () => News::where('status', 'scheduled')->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->withoutTrashed()->where('status', 'scheduled')),
            'published' => Tab::make('منتشرشده')
                ->modifyQueryUsing(fn (Builder $query) => $query->withoutTrashed()->where('status', 'published')),
            'suspended' => Tab::make('معلق')
                ->badge(fn () => News::where('status', 'suspended')->count())
                ->badgeColor('gray')
                ->modifyQueryUsing(fn (Builder $query) => $query->withoutTrashed()->where('status', 'suspended')),
            'trashed' => Tab::make('حذف‌شده')
                ->badge(fn () => News::onlyTrashed()->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->onlyTrashed()),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            NewsResource\Widgets\NewsCountStatWidget::class
        ];
    }
}
