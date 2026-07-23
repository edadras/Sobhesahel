<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FetchedItemResource\Pages;
use App\Models\FetchedItem;
use App\Services\NewsCrawlerService;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms\Form;
use Filament\Notifications\Actions\Action as NotificationAction;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

/**
 * کارتابل رصد منابع — read-only inbox of items fetched from external
 * news sources. Editors can view the summary, open the original page,
 * save an item as a draft news record, or dismiss it.
 */
class FetchedItemResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = FetchedItem::class;

    protected static ?string $navigationIcon = 'fas-satellite-dish';

    protected static ?string $label = 'خبر دریافتی';

    protected static ?string $navigationGroup = 'رصد منابع';

    protected static ?int $navigationSort = 20;

    protected static bool $hasTitleCaseModelLabel = false;

    public static function getPluralModelLabel(): string
    {
        return 'اخبار دریافتی';
    }

    public static function getPluralLabel(): ?string
    {
        return 'اخبار دریافتی';
    }

    public static function getNavigationBadge(): ?string
    {
        $count = FetchedItem::where('status', FetchedItem::STATUS_NEW)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'info';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('source'))
            ->columns([
                Tables\Columns\TextColumn::make('source.name')
                    ->label('منبع')
                    ->sortable()
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('title')
                    ->label('تیتر')
                    ->searchable()
                    ->limit(80)
                    ->wrap()
                    ->description(fn (FetchedItem $record) => \Illuminate\Support\Str::limit((string) $record->summary, 90)),

                Tables\Columns\TextColumn::make('published_at')
                    ->label('تاریخ انتشار در منبع')
                    ->jalaliDateTime('H:i Y/m/d')
                    ->placeholder('—')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('وضعیت')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => FetchedItem::STATUSES[$state] ?? $state)
                    ->color(fn (string $state) => match ($state) {
                        FetchedItem::STATUS_NEW => 'info',
                        FetchedItem::STATUS_SAVED => 'success',
                        FetchedItem::STATUS_DISMISSED => 'gray',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاریخ دریافت')
                    ->jalaliDateTime('H:i Y/m/d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('news_source_id')
                    ->relationship('source', 'name')
                    ->label('منبع'),
                Tables\Filters\SelectFilter::make('status')
                    ->options(FetchedItem::STATUSES)
                    ->label('وضعیت'),
            ])
            ->actions([
                Tables\Actions\Action::make('viewOriginal')
                    ->label('مشاهده اصل خبر')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (FetchedItem $record) => $record->original_url, shouldOpenInNewTab: true)
                    ->visible(fn (FetchedItem $record) => filled($record->original_url)),

                Tables\Actions\Action::make('viewSummary')
                    ->label('مشاهده خلاصه')
                    ->icon('heroicon-o-eye')
                    ->modalHeading(fn (FetchedItem $record) => $record->title)
                    ->modalContent(fn (FetchedItem $record) => new HtmlString(
                        '<div style="direction: rtl; text-align: right; line-height: 2;">'
                        .(filled($record->image_url)
                            ? '<img src="'.e($record->image_url).'" alt="" style="max-width: 100%; border-radius: 0.5rem; margin-bottom: 1rem;">'
                            : '')
                        .'<p>'.nl2br(e(filled($record->summary) ? $record->summary : 'خلاصه‌ای برای این خبر در فید منبع ثبت نشده است.')).'</p>'
                        .'<p style="margin-top: 1rem; opacity: 0.7;">منبع: '.e($record->source?->name ?? '—')
                        .(filled($record->published_at) ? ' — '.\Morilog\Jalali\Jalalian::fromCarbon($record->published_at)->format('Y/m/d H:i') : '')
                        .'</p>'
                        .'</div>'
                    ))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('بستن'),

                Tables\Actions\Action::make('saveToCms')
                    ->label('ذخیره در سامانه')
                    ->icon('heroicon-o-archive-box-arrow-down')
                    ->color('success')
                    ->visible(fn (FetchedItem $record) => $record->status !== FetchedItem::STATUS_SAVED)
                    ->requiresConfirmation()
                    ->modalHeading('ذخیره خبر در سامانه')
                    ->modalDescription('از این آیتم یک خبر «پیش‌نویس» ساخته می‌شود؛ منبع در انتهای متن درج و تصویر خبر (در صورت وجود) دانلود خواهد شد.')
                    ->action(function (FetchedItem $record) {
                        try {
                            $news = app(NewsCrawlerService::class)->saveToNews($record);

                            Notification::make()
                                ->success()
                                ->title('خبر پیش‌نویس ساخته شد')
                                ->body('خبر «'.$news->title.'» به‌صورت پیش‌نویس در سامانه ذخیره شد.')
                                ->actions([
                                    NotificationAction::make('editNews')
                                        ->label('ویرایش خبر')
                                        ->url(NewsResource::getUrl('edit', ['record' => $news])),
                                ])
                                ->send();
                        } catch (\Throwable $e) {
                            report($e);

                            Notification::make()
                                ->danger()
                                ->title('خطا در ذخیره خبر')
                                ->body($e->getMessage())
                                ->send();
                        }
                    }),

                Tables\Actions\Action::make('dismiss')
                    ->label('رد')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (FetchedItem $record) => $record->status === FetchedItem::STATUS_NEW)
                    ->requiresConfirmation()
                    ->modalHeading('رد کردن خبر دریافتی')
                    ->modalDescription('این آیتم به کارتابل «ردشده» منتقل و پس از مدتی به‌صورت خودکار حذف می‌شود.')
                    ->action(function (FetchedItem $record) {
                        $record->update(['status' => FetchedItem::STATUS_DISMISSED]);

                        Notification::make()
                            ->success()
                            ->title('خبر رد شد')
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('dismissBulk')
                        ->label('رد گروهی')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('رد کردن اخبار انتخاب‌شده')
                        ->modalDescription('فقط آیتم‌های «جدید» رد می‌شوند؛ آیتم‌های ذخیره‌شده تغییری نمی‌کنند.')
                        ->deselectRecordsAfterCompletion()
                        ->action(function (Collection $records) {
                            $count = 0;

                            foreach ($records as $record) {
                                if ($record->status === FetchedItem::STATUS_NEW) {
                                    $record->update(['status' => FetchedItem::STATUS_DISMISSED]);
                                    $count++;
                                }
                            }

                            Notification::make()
                                ->success()
                                ->title("{$count} خبر رد شد")
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('id', 'DESC');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFetchedItems::route('/'),
        ];
    }

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'create',
            'update',
            'delete',
        ];
    }
}
