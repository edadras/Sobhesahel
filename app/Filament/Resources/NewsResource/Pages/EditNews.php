<?php

namespace App\Filament\Resources\NewsResource\Pages;

use App\Filament\Resources\NewsResource;
use App\Models\News;
use App\Services\WatermarkService;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Pages\EditRecord;

class EditNews extends EditRecord
{
    protected static string $resource = NewsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // گردش کار تحریریه — approve / reject pending items.
            Actions\Action::make('approve')
                ->label('تأیید و انتشار')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->visible(fn () => $this->getRecord()->status === News::STATUS_PENDING_REVIEW
                    && ! $this->getRecord()->trashed()
                    && News::userCanPublish())
                ->requiresConfirmation()
                ->modalHeading('تأیید و انتشار خبر')
                ->modalDescription(fn () => 'خبر «' . $this->getRecord()->title . '» منتشر شود؟')
                ->action(function () {
                    NewsResource::approveNews($this->getRecord());

                    $this->refreshFormData(['status', 'publish_at']);
                }),
            Actions\Action::make('reject')
                ->label('بازگشت برای اصلاح')
                ->icon('heroicon-o-arrow-uturn-right')
                ->color('danger')
                ->visible(fn () => $this->getRecord()->status === News::STATUS_PENDING_REVIEW
                    && ! $this->getRecord()->trashed()
                    && News::userCanPublish())
                ->form([
                    Forms\Components\Textarea::make('review_reason')
                        ->label('دلیل بازگشت برای اصلاح')
                        ->required()
                        ->rows(3),
                ])
                ->modalHeading('بازگشت خبر برای اصلاح')
                ->action(function (array $data) {
                    NewsResource::rejectNews($this->getRecord(), $data['review_reason']);

                    $this->refreshFormData(['status']);
                }),
            Actions\DeleteAction::make(),
            Actions\RestoreAction::make()
                ->label('بازیابی'),
            Actions\ForceDeleteAction::make()
                ->label('حذف دائمی'),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $applyWatermark = (bool) ($data['apply_watermark'] ?? false);
        unset($data['apply_watermark']);

        // Only watermark a newly uploaded image, so an unchanged image is
        // never watermarked twice.
        if (
            $applyWatermark
            && ! empty($data['image_original'])
            && $data['image_original'] !== $this->getRecord()->image_original
        ) {
            app(WatermarkService::class)->applyToUploadedFile($data['image_original']);
        }

        return $data;
    }
}
