<?php

namespace App\Filament\Resources\PointRuleResource\Pages;

use App\Filament\Resources\PointRuleResource;
use App\Models\PointRule;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListPointRules extends ListRecords
{
    protected static string $resource = PointRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('createDefaults')
                ->label('ایجاد قوانین پیش‌فرض')
                ->icon('fas-wand-magic-sparkles')
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading('ایجاد قوانین پیش‌فرض')
                ->modalDescription('مجموعه قوانین استاندارد باشگاه (ورود روزانه، نظر تأییدشده، نظرسنجی و…) ساخته می‌شود. قوانین موجود دست نمی‌خورند و همه مقادیر بعداً قابل‌ویرایش‌اند.')
                ->action(function () {
                    $created = 0;

                    foreach (PointRuleResource::defaultRules() as $index => $rule) {
                        $model = PointRule::query()->firstOrCreate(
                            ['code' => $rule['code']],
                            [
                                'title' => $rule['title'],
                                'points' => $rule['points'],
                                'daily_cap' => $rule['daily_cap'],
                                'is_active' => true,
                                'sort' => $index,
                            ],
                        );

                        if ($model->wasRecentlyCreated) {
                            $created++;
                        }
                    }

                    Notification::make()
                        ->title($created > 0 ? "{$created} قانون پیش‌فرض ایجاد شد" : 'همه قوانین پیش‌فرض از قبل موجود بودند')
                        ->success()
                        ->send();
                }),

            Actions\CreateAction::make(),
        ];
    }
}
