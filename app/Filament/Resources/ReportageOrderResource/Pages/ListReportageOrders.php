<?php

namespace App\Filament\Resources\ReportageOrderResource\Pages;

use App\Filament\Resources\ReportageOrderResource;
use App\Models\ReportageOrder;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListReportageOrders extends ListRecords
{
    protected static string $resource = ReportageOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    /**
     * کارتابل سفارش‌های رپورتاژ بر اساس وضعیت.
     */
    public function getTabs(): array
    {
        $countOf = fn (string $status) => ReportageOrder::where('status', $status)->count();

        return [
            'new' => Tab::make('جدید')
                ->badge(fn () => $countOf(ReportageOrder::STATUS_NEW))
                ->badgeColor('info')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', ReportageOrder::STATUS_NEW)),
            'awaiting_payment' => Tab::make('در انتظار پرداخت')
                ->badge(fn () => $countOf(ReportageOrder::STATUS_AWAITING_PAYMENT))
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', ReportageOrder::STATUS_AWAITING_PAYMENT)),
            'paid' => Tab::make('پرداخت‌شده')
                ->badge(fn () => $countOf(ReportageOrder::STATUS_PAID))
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', ReportageOrder::STATUS_PAID)),
            'published' => Tab::make('منتشرشده')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', ReportageOrder::STATUS_PUBLISHED)),
            'rejected' => Tab::make('ردشده')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', ReportageOrder::STATUS_REJECTED)),
            'all' => Tab::make('همه'),
        ];
    }
}
