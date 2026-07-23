<?php

namespace App\Filament\Resources\SubscriptionResource\Pages;

use App\Filament\Resources\SubscriptionResource;
use App\Models\Subscription;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListSubscriptions extends ListRecords
{
    protected static string $resource = SubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    /**
     * کارتابل اشتراک‌ها بر اساس وضعیت.
     */
    public function getTabs(): array
    {
        return [
            'pending' => Tab::make('در انتظار پرداخت')
                ->badge(fn () => Subscription::where('status', Subscription::STATUS_PENDING)->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', Subscription::STATUS_PENDING)),
            'active' => Tab::make('فعال')
                ->badge(fn () => Subscription::where('status', Subscription::STATUS_ACTIVE)->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', Subscription::STATUS_ACTIVE)),
            'expired' => Tab::make('منقضی‌شده')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', Subscription::STATUS_EXPIRED)),
            'all' => Tab::make('همه'),
        ];
    }
}
