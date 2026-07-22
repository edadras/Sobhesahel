<?php

namespace App\Filament\Widgets;

use App\Services\ContentVisitStats;
use App\Services\MatomoService;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Carbon\Carbon;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;
use Morilog\Jalali\Jalalian;

class MostViewedContentWidget extends Widget
{
    use HasWidgetShield;

    protected static string $view = 'filament.widgets.most-viewed-content-widget';

    protected static bool $isLazy = false;

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    protected function getViewData(): array
    {
        return [
            'items' => $this->mostViewedItems(),
            'matomoPages' => $this->matomoTopPages(),
            'matomoConfigured' => rescue(fn () => MatomoService::isConfigured(), false, false),
        ];
    }

    protected function mostViewedItems(): Collection
    {
        try {
            return ContentVisitStats::mostViewed(10)->map(function (array $item) {
                $publishAt = $item['publish_at'];

                try {
                    $item['publish_at_jalali'] = $publishAt
                        ? Jalalian::fromCarbon(Carbon::parse($publishAt))->format('Y/m/d')
                        : '—';
                } catch (\Throwable $e) {
                    $item['publish_at_jalali'] = '—';
                }

                return $item;
            });
        } catch (\Throwable $e) {
            return collect();
        }
    }

    protected function matomoTopPages(): ?array
    {
        try {
            return MatomoService::topPages(10);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
