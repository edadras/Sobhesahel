<?php

namespace App\Filament\Resources\AdvertiseResource\Pages;

use App\Filament\Resources\AdvertiseResource;
use App\Models\Advertise;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Morilog\Jalali\Jalalian;

class ListAdvertises extends ListRecords
{
    protected static string $resource = AdvertiseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // گزارش تبلیغات: خروجی CSV آمار کلیک و نمایش (streamed, no packages).
            Actions\Action::make('export_report')
                ->label('خروجی CSV گزارش')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(function () {
                    $fileName = 'advertise-report-' . now()->format('Y-m-d') . '.csv';

                    return response()->streamDownload(function () {
                        $out = fopen('php://output', 'w');

                        // UTF-8 BOM so Persian text opens correctly in Excel.
                        fwrite($out, "\xEF\xBB\xBF");

                        fputcsv($out, [
                            'شناسه',
                            'نام',
                            'نوع',
                            'تعداد نمایش',
                            'تعداد کلیک',
                            'نرخ کلیک (٪)',
                            'فعال',
                            'شروع نمایش',
                            'پایان نمایش',
                            'حداکثر نمایش',
                            'حداکثر کلیک',
                        ]);

                        Advertise::query()->orderBy('id')->chunk(200, function ($ads) use ($out) {
                            foreach ($ads as $ad) {
                                $views = (int) $ad->view;
                                $clicks = (int) $ad->click;

                                fputcsv($out, [
                                    $ad->id,
                                    $ad->name,
                                    match ($ad->type ?? ($ad->image ? 'image' : 'text')) {
                                        'video' => 'ویدیو',
                                        'image' => 'عکس',
                                        default => 'متن',
                                    },
                                    $views,
                                    $clicks,
                                    $views > 0 ? round(($clicks / $views) * 100, 2) : 0,
                                    $ad->is_active ? 'بله' : 'خیر',
                                    $ad->starts_at ? Jalalian::fromCarbon(Carbon::parse($ad->starts_at))->format('H:i Y/m/d') : '',
                                    $ad->ends_at ? Jalalian::fromCarbon(Carbon::parse($ad->ends_at))->format('H:i Y/m/d') : '',
                                    $ad->max_views ?? '',
                                    $ad->max_clicks ?? '',
                                ]);
                            }
                        });

                        fclose($out);
                    }, $fileName, [
                        'Content-Type' => 'text/csv; charset=UTF-8',
                    ]);
                }),

            Actions\CreateAction::make(),
        ];
    }
}
