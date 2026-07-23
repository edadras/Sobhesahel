<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CitizenReportResource\Pages;
use App\Models\CitizenReport;
use App\Models\News;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Actions\Action as NotificationAction;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

/**
 * شهروند خبرنگار — editorial review inbox for reports submitted by the
 * public from /citizen-report. Read-only records; editors can start a
 * review, convert a report into a draft news item, or reject it.
 */
class CitizenReportResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = CitizenReport::class;

    protected static ?string $navigationIcon = 'fas-bullhorn';

    protected static ?string $label = 'گزارش شهروندی';

    protected static ?string $navigationGroup = 'رصد منابع';

    protected static ?int $navigationSort = 30;

    protected static bool $hasTitleCaseModelLabel = false;

    public static function getPluralModelLabel(): string
    {
        return 'شهروند خبرنگار';
    }

    public static function getPluralLabel(): ?string
    {
        return 'شهروند خبرنگار';
    }

    public static function getNavigationBadge(): ?string
    {
        $count = CitizenReport::where('status', CitizenReport::STATUS_NEW)->count();

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
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('عنوان گزارش')
                    ->searchable()
                    ->limit(60)
                    ->wrap()
                    ->description(fn (CitizenReport $record) => Str::limit((string) $record->body, 90)),

                Tables\Columns\TextColumn::make('name')
                    ->label('نام فرستنده')
                    ->searchable(),

                Tables\Columns\TextColumn::make('mobile')
                    ->label('موبایل')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('attachments')
                    ->label('پیوست')
                    ->badge()
                    ->color('gray')
                    ->state(fn (CitizenReport $record) => count((array) $record->attachments) . ' فایل'),

                Tables\Columns\TextColumn::make('status')
                    ->label('وضعیت')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => CitizenReport::STATUSES[$state] ?? $state)
                    ->color(fn (string $state) => static::statusColor($state)),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاریخ ارسال')
                    ->jalaliDateTime('H:i Y/m/d')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(CitizenReport::STATUSES)
                    ->label('وضعیت'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                Tables\Actions\Action::make('startReview')
                    ->label('شروع بررسی')
                    ->icon('heroicon-o-magnifying-glass')
                    ->color('warning')
                    ->visible(fn (CitizenReport $record) => $record->status === CitizenReport::STATUS_NEW)
                    ->action(fn (CitizenReport $record) => static::startReview($record)),

                Tables\Actions\Action::make('convertToNews')
                    ->label('تبدیل به خبر')
                    ->icon('heroicon-o-newspaper')
                    ->color('success')
                    ->visible(fn (CitizenReport $record) => static::canConvert($record))
                    ->requiresConfirmation()
                    ->modalHeading('تبدیل گزارش به خبر')
                    ->modalDescription('از این گزارش یک خبر «پیش‌نویس» ساخته می‌شود؛ اولین تصویر پیوست به‌عنوان تصویر خبر کپی و عبارت «ارسالی شهروند خبرنگار» به انتهای متن افزوده خواهد شد.')
                    ->action(fn (CitizenReport $record) => static::convertToNews($record)),

                Tables\Actions\Action::make('reject')
                    ->label('رد')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (CitizenReport $record) => in_array($record->status, [CitizenReport::STATUS_NEW, CitizenReport::STATUS_REVIEWING], true))
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
                    ->action(fn (CitizenReport $record, array $data) => static::reject($record, (string) $data['review_note'])),

                Tables\Actions\DeleteAction::make()
                    ->modalDescription('با حذف گزارش، فایل‌های پیوست آن نیز از سرور حذف می‌شوند.'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('rejectBulk')
                        ->label('رد گروهی')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->form([
                            Forms\Components\Textarea::make('review_note')
                                ->label('دلیل رد گزارش‌ها')
                                ->required()
                                ->rows(3)
                                ->validationMessages([
                                    'required' => 'وارد کردن دلیل رد الزامی است',
                                ]),
                        ])
                        ->modalHeading('رد گزارش‌های انتخاب‌شده')
                        ->modalDescription('فقط گزارش‌های «جدید» و «در حال بررسی» رد می‌شوند؛ گزارش‌های تبدیل‌شده به خبر تغییری نمی‌کنند.')
                        ->deselectRecordsAfterCompletion()
                        ->action(function (Collection $records, array $data) {
                            $count = 0;

                            foreach ($records as $record) {
                                if (in_array($record->status, [CitizenReport::STATUS_NEW, CitizenReport::STATUS_REVIEWING], true)) {
                                    static::reject($record, (string) $data['review_note'], notify: false);
                                    $count++;
                                }
                            }

                            Notification::make()
                                ->success()
                                ->title("{$count} گزارش رد شد")
                                ->send();
                        }),

                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('id', 'DESC');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('گزارش')
                    ->schema([
                        Infolists\Components\TextEntry::make('title')
                            ->label('عنوان')
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('body')
                            ->label('متن گزارش')
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('location')
                            ->label('محل رویداد')
                            ->placeholder('—'),

                        Infolists\Components\TextEntry::make('created_at')
                            ->label('تاریخ ارسال')
                            ->jalaliDateTime('H:i Y/m/d'),
                    ])->columns(2),

                Infolists\Components\Section::make('فرستنده')
                    ->schema([
                        Infolists\Components\TextEntry::make('name')
                            ->label('نام و نام خانوادگی'),

                        Infolists\Components\TextEntry::make('mobile')
                            ->label('شماره موبایل')
                            ->copyable(),

                        Infolists\Components\TextEntry::make('email')
                            ->label('ایمیل')
                            ->placeholder('—'),

                        Infolists\Components\TextEntry::make('ip')
                            ->label('آدرس IP')
                            ->copyable()
                            ->placeholder('ثبت نشده'),
                    ])->columns(2),

                Infolists\Components\Section::make('پیوست‌ها')
                    ->visible(fn (CitizenReport $record) => count((array) $record->attachments) > 0)
                    ->schema([
                        Infolists\Components\ImageEntry::make('image_previews')
                            ->label('تصاویر')
                            ->getStateUsing(fn (CitizenReport $record) => array_map(
                                fn (array $attachment) => Storage::disk(CitizenReport::ATTACHMENT_DISK)->url($attachment['path']),
                                $record->attachmentsOfType('image')
                            ))
                            ->height(160)
                            ->visible(fn (CitizenReport $record) => count($record->attachmentsOfType('image')) > 0)
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('attachment_links')
                            ->label('دریافت فایل‌ها')
                            ->getStateUsing(function (CitizenReport $record) {
                                $links = [];

                                foreach ((array) $record->attachments as $attachment) {
                                    if (! is_array($attachment) || empty($attachment['path'])) {
                                        continue;
                                    }

                                    $url = Storage::disk(CitizenReport::ATTACHMENT_DISK)->url($attachment['path']);
                                    $label = ($attachment['type'] ?? '') === 'video' ? 'ویدیو' : 'تصویر';
                                    $name = e($attachment['name'] ?? basename($attachment['path']));

                                    $links[] = '<a href="'.e($url).'" target="_blank" rel="noopener" style="text-decoration: underline;">'.$label.': '.$name.'</a>';
                                }

                                return new HtmlString(implode('<br>', $links));
                            })
                            ->columnSpanFull(),
                    ]),

                Infolists\Components\Section::make('بررسی تحریریه')
                    ->schema([
                        Infolists\Components\TextEntry::make('status')
                            ->label('وضعیت')
                            ->badge()
                            ->formatStateUsing(fn (string $state) => CitizenReport::STATUSES[$state] ?? $state)
                            ->color(fn (string $state) => static::statusColor($state)),

                        Infolists\Components\TextEntry::make('reviewer.name')
                            ->label('بررسی‌کننده')
                            ->placeholder('—'),

                        Infolists\Components\TextEntry::make('review_note')
                            ->label('یادداشت بررسی')
                            ->placeholder('—')
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('convertedNews.title')
                            ->label('خبر ساخته‌شده')
                            ->placeholder('—')
                            ->url(fn (CitizenReport $record) => $record->converted_news_id
                                ? NewsResource::getUrl('edit', ['record' => $record->converted_news_id])
                                : null)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function statusColor(string $status): string
    {
        return match ($status) {
            CitizenReport::STATUS_NEW => 'info',
            CitizenReport::STATUS_REVIEWING => 'warning',
            CitizenReport::STATUS_ACCEPTED => 'success',
            CitizenReport::STATUS_REJECTED => 'danger',
            default => 'gray',
        };
    }

    public static function canConvert(CitizenReport $record): bool
    {
        return in_array($record->status, [CitizenReport::STATUS_NEW, CitizenReport::STATUS_REVIEWING], true)
            && ! $record->converted_news_id;
    }

    /**
     * شروع بررسی — mark the report as "reviewing" and stamp the reviewer.
     */
    public static function startReview(CitizenReport $record): void
    {
        $record->update([
            'status' => CitizenReport::STATUS_REVIEWING,
            'reviewer_id' => auth()->id(),
        ]);

        Notification::make()
            ->success()
            ->title('بررسی گزارش آغاز شد')
            ->send();
    }

    /**
     * رد — reject the report with a required Persian reason.
     */
    public static function reject(CitizenReport $record, string $reason, bool $notify = true): void
    {
        $record->update([
            'status' => CitizenReport::STATUS_REJECTED,
            'review_note' => $reason,
            'reviewer_id' => $record->reviewer_id ?? auth()->id(),
        ]);

        if ($notify) {
            Notification::make()
                ->success()
                ->title('گزارش رد شد')
                ->send();
        }
    }

    /**
     * تبدیل به خبر — build a DRAFT News record from the report: title/body,
     * first attached image copied as image_original, and the citizen
     * attribution appended. Marks the report "accepted".
     */
    public static function convertToNews(CitizenReport $record): ?News
    {
        if (! static::canConvert($record)) {
            return null;
        }

        try {
            $body = '<p>'.nl2br(e(trim($record->body))).'</p>';

            $attribution = '<p><strong>ارسالی شهروند خبرنگار'
                .(filled($record->name) ? ' — '.e($record->name) : '')
                .'</strong></p>';

            $body .= "\n".$attribution;

            $news = new News();
            $news->title = Str::limit($record->title, 250, '…');
            $news->short_description = Str::limit(trim($record->body), 200);
            $news->body = $body;
            $news->status = 'draft';
            $news->is_published = 0;
            $news->lang_id = 1;
            $news->user_id = auth()->id() ?? 1;

            // Copy (not move) the first attached image so deleting the report
            // later never removes the news image. Best-effort: a copy failure
            // must not block the conversion.
            $firstImage = $record->attachmentsOfType('image')[0] ?? null;

            if ($firstImage && ! empty($firstImage['path'])) {
                try {
                    $disk = Storage::disk(CitizenReport::ATTACHMENT_DISK);

                    if ($disk->exists($firstImage['path'])) {
                        $extension = strtolower(pathinfo($firstImage['path'], PATHINFO_EXTENSION)) ?: 'jpg';
                        $newPath = 'citizen-reports/news/'.Str::random(40).'.'.$extension;

                        $disk->copy($firstImage['path'], $newPath);

                        $news->image_original = $newPath;
                    }
                } catch (\Throwable $e) {
                    report($e);
                }
            }

            $news->save();

            $record->update([
                'status' => CitizenReport::STATUS_ACCEPTED,
                'reviewer_id' => $record->reviewer_id ?? auth()->id(),
                'converted_news_id' => $news->id,
            ]);

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

            return $news;
        } catch (\Throwable $e) {
            report($e);

            Notification::make()
                ->danger()
                ->title('خطا در تبدیل گزارش به خبر')
                ->body($e->getMessage())
                ->send();

            return null;
        }
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
            'index' => Pages\ListCitizenReports::route('/'),
            'view' => Pages\ViewCitizenReport::route('/{record}'),
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
