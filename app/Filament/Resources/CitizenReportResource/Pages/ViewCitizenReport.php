<?php

namespace App\Filament\Resources\CitizenReportResource\Pages;

use App\Filament\Resources\CitizenReportResource;
use App\Models\CitizenReport;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Pages\ViewRecord;

class ViewCitizenReport extends ViewRecord
{
    protected static string $resource = CitizenReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('startReview')
                ->label('شروع بررسی')
                ->icon('heroicon-o-magnifying-glass')
                ->color('warning')
                ->visible(fn () => $this->getRecord()->status === CitizenReport::STATUS_NEW)
                ->action(fn () => CitizenReportResource::startReview($this->getRecord())),

            Actions\Action::make('convertToNews')
                ->label('تبدیل به خبر')
                ->icon('heroicon-o-newspaper')
                ->color('success')
                ->visible(fn () => CitizenReportResource::canConvert($this->getRecord()))
                ->requiresConfirmation()
                ->modalHeading('تبدیل گزارش به خبر')
                ->modalDescription('از این گزارش یک خبر «پیش‌نویس» ساخته می‌شود؛ اولین تصویر پیوست به‌عنوان تصویر خبر کپی و عبارت «ارسالی شهروند خبرنگار» به انتهای متن افزوده خواهد شد.')
                ->action(fn () => CitizenReportResource::convertToNews($this->getRecord())),

            Actions\Action::make('reject')
                ->label('رد')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn () => in_array($this->getRecord()->status, [CitizenReport::STATUS_NEW, CitizenReport::STATUS_REVIEWING], true))
                ->form([
                    Forms\Components\Textarea::make('review_note')
                        ->label('دلیل رد گزارش')
                        ->required()
                        ->rows(3)
                        ->validationMessages([
                            'required' => 'وارد کردن دلیل رد الزامی است',
                        ]),
                ])
                ->modalHeading('رد گزارش شهروندی')
                ->action(fn (array $data) => CitizenReportResource::reject($this->getRecord(), (string) $data['review_note'])),

            Actions\DeleteAction::make()
                ->modalDescription('با حذف گزارش، فایل‌های پیوست آن نیز از سرور حذف می‌شوند.'),
        ];
    }
}
