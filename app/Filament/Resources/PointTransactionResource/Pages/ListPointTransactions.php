<?php

namespace App\Filament\Resources\PointTransactionResource\Pages;

use App\Filament\Resources\PointTransactionResource;
use App\Models\PointTransaction;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Morilog\Jalali\Jalalian;

class ListPointTransactions extends ListRecords
{
    protected static string $resource = PointTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('exportCsv')
                ->label('خروجی CSV')
                ->icon('fas-file-csv')
                ->color('gray')
                ->action(function () {
                    $ruleOptions = PointTransactionResource::ruleOptions();
                    $fileName = 'point-transactions-' . now()->format('Y-m-d-His') . '.csv';

                    return response()->streamDownload(function () use ($ruleOptions) {
                        $out = fopen('php://output', 'w');

                        // UTF-8 BOM تا اکسل متن فارسی را درست باز کند
                        fwrite($out, "\xEF\xBB\xBF");

                        fputcsv($out, [
                            'شناسه',
                            'شناسه عضو',
                            'امتیاز',
                            'موجودی پس از تراکنش',
                            'کد قانون',
                            'قانون',
                            'توضیح',
                            'تاریخ',
                        ]);

                        PointTransaction::query()->orderBy('id')->chunk(500, function ($rows) use ($out, $ruleOptions) {
                            foreach ($rows as $row) {
                                fputcsv($out, [
                                    $row->id,
                                    $row->member_id,
                                    $row->points,
                                    $row->balance_after,
                                    $row->rule_code,
                                    $ruleOptions[$row->rule_code] ?? $row->rule_code,
                                    $row->description,
                                    $row->created_at
                                        ? Jalalian::fromCarbon(Carbon::parse($row->created_at))->format('H:i Y/m/d')
                                        : '',
                                ]);
                            }
                        });

                        fclose($out);
                    }, $fileName, ['Content-Type' => 'text/csv; charset=UTF-8']);
                }),
        ];
    }
}
