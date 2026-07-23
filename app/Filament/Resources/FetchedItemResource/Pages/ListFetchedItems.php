<?php

namespace App\Filament\Resources\FetchedItemResource\Pages;

use App\Filament\Resources\FetchedItemResource;
use App\Models\FetchedItem;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListFetchedItems extends ListRecords
{
    protected static string $resource = FetchedItemResource::class;

    /**
     * کارتابل‌های رصد منابع: جدید / ذخیره‌شده / ردشده.
     */
    public function getTabs(): array
    {
        return [
            'new' => Tab::make('جدید')
                ->badge(fn () => FetchedItem::where('status', FetchedItem::STATUS_NEW)->count())
                ->badgeColor('info')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', FetchedItem::STATUS_NEW)),
            'saved' => Tab::make('ذخیره‌شده')
                ->badge(fn () => FetchedItem::where('status', FetchedItem::STATUS_SAVED)->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', FetchedItem::STATUS_SAVED)),
            'dismissed' => Tab::make('ردشده')
                ->badge(fn () => FetchedItem::where('status', FetchedItem::STATUS_DISMISSED)->count())
                ->badgeColor('gray')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', FetchedItem::STATUS_DISMISSED)),
            'all' => Tab::make('همه'),
        ];
    }
}
