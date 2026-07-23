<?php

namespace App\Filament\Resources;

use AmidEsfahani\FilamentTinyEditor\TinyEditor;
use App\Filament\Resources\NewsResource\Pages;
use App\Filament\Resources\NewsResource\RelationManagers;
use App\Models\Author;
use App\Models\Category;
use App\Models\FeaturedNews;
use App\Models\News;
use App\Models\Tag;
use App\Traits\ContentTrait;
use App\Traits\FilamentContent;
use App\Traits\HasCommonNewsTable;
use App\Traits\HasContentField;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Carbon\Carbon;
use App\Services\SocialPublishService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use FilamentTiptapEditor\Enums\TiptapOutput;
use FilamentTiptapEditor\TiptapEditor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use Morilog\Jalali\Jalalian;

class NewsResource extends Resource implements HasShieldPermissions
{
    use FilamentContent,HasContentField,HasCommonNewsTable;

    protected static ?string $model = News::class;

    protected static ?string $navigationIcon = 'fas-pen-nib';

    protected static ?string $label = 'خبر';

    protected static ?string $navigationGroup = 'محتوا';

    protected static bool $hasTitleCaseModelLabel = false;

    public static function getPluralModelLabel(): string
    {
        return 'اخبار';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(12)->schema([
                    Forms\Components\Grid::make()->schema([
                        Forms\Components\TextInput::make('sub_title')->label('رو تیتر')->columnSpanFull(),
                        ...self::liveTitleAndSlugInputs(),
                        Forms\Components\RichEditor::make('short_description')->label('متن کوتاه')->columnSpanFull()->toolbarButtons([
                            'link',
                            'redo',
                            'strike',
                            'underline',
                            'undo',
                        ]),
                        TiptapEditor::make('body')
                            ->label('متن خبر')
                            ->output(TiptapOutput::Html)
                            ->maxContentWidth('5xl')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\Repeater::make('subtitles')
                            ->label('سوتیترها')
                            ->simple(
                                Forms\Components\TextInput::make('subtitle')
                                    ->label('سوتیتر')
                                    ->required(),
                            )
                            ->addActionLabel('افزودن سوتیتر')
                            ->defaultItems(0)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('pre_message')
                            ->label('پیام ابتدای خبر')
                            ->rows(2)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('post_message')
                            ->label('پیام انتهای خبر')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])->columnSpan(8),
                    Forms\Components\Grid::make()->schema([
                        Forms\Components\FileUpload::make('image_original')
                            ->label('تصویر خبر')
//                            ->disk('media')
//                            ->default(fn ($record) => $record?->getTitleValue('original_image')->value ?? null)
                            ->columnSpanFull()
                            ->image()
                            ->imageEditor()
                            ->required(),
                        Forms\Components\Toggle::make('apply_watermark')
                            ->label('اعمال واترمارک صبح ساحل')
                            ->helperText('با فعال کردن این گزینه، لوگوی صبح ساحل روی تصویر خبر درج می‌شود. فایل اصلی بدون واترمارک حفظ خواهد شد.')
                            ->default(false)
                            ->columnSpanFull(),
                        Forms\Components\Section::make('رسانه‌های تکمیلی')->schema([
                            Forms\Components\FileUpload::make('image_second')
                                ->label('تصویر مکمل ۱')
                                ->columnSpanFull()
                                ->image()
                                ->imageEditor(),
                            Forms\Components\FileUpload::make('image_third')
                                ->label('تصویر مکمل ۲')
                                ->columnSpanFull()
                                ->image()
                                ->imageEditor(),
                            Forms\Components\FileUpload::make('media_file')
                                ->label('فایل صوتی/تصویری خبر')
                                ->helperText('فایل mp3 یا mp4 — حداکثر ۱۰۰ مگابایت')
                                ->acceptedFileTypes(['audio/mpeg', 'video/mp4'])
                                ->maxSize(102400)
                                ->columnSpanFull(),
                        ])->collapsed(fn ($record) => $record === null || (blank($record->image_second) && blank($record->image_third) && blank($record->media_file))),
                        Forms\Components\Section::make('تنظیمات انتشار')->schema([
                            Forms\Components\Select::make('category')
                                ->relationship('categories', 'title')
                                ->label('دسته بندی')
                                ->searchable()
                                ->multiple(),
                            Forms\Components\SpatieTagsInput::make('tags')
                                ->label('برچسب‌ها')->type('fa'),
                            Forms\Components\ToggleButtons::make('status')
                                // گردش کار تحریریه: کاربران بدون مجوز publish_news فقط
                                // پیش‌نویس و «در انتظار تأیید» را می‌بینند.
                                ->options(fn () => self::statusOptionsForCurrentUser())
                                ->icons([
                                    'draft' => 'heroicon-o-pencil',
                                    'pending_review' => 'heroicon-o-inbox-arrow-down',
                                    'scheduled' => 'heroicon-o-clock',
                                    'published' => 'heroicon-o-check-circle',
                                    'suspended' => 'heroicon-o-pause-circle',
                                ])
                                ->colors([
                                    'draft' => 'warning',
                                    'pending_review' => 'info',
                                    'scheduled' => 'danger',
                                    'published' => 'success',
                                    'suspended' => 'gray',
                                ])
                                ->label('وضعیت انتشار')
                                ->inline()
                                ->required()
                                ->reactive(),
                            Forms\Components\DateTimePicker::make('publish_at')
                                ->jalali()
                                ->label('تاریخ انتشار')
                                ->visible(fn($get) => in_array($get('status'), ['scheduled'])),
                            Forms\Components\Repeater::make('featured_news')
                                ->label('اخبار ویژه')
                                ->relationship('featuredNews')
                                ->schema([
                                    Forms\Components\Select::make('section')
                                        ->label('بخش')
                                        ->options(array_map(function ($item){
                                            return $item['title'];
                                        },FeaturedNews::BOXES))
                                        ->columnSpanFull()
                                        ->required(),
                                    Forms\Components\DateTimePicker::make('expires_at')
                                        ->label('تاریخ انقضا')
                                        ->columnSpanFull()
                                        ->jalali(),
                                    Forms\Components\TextInput::make('display_order')
                                        ->label('ترتیب نمایش')
                                        ->numeric()
                                        ->columnSpanFull()
                                        ->default(100)
                                        ->minValue(0)
                                        ->required(),
                                ])
                                ->defaultItems(0)
                                ->visible(fn($get) => $get('status') === 'published')
                                ->mutateRelationshipDataBeforeFillUsing(function ($data) {
                                    // Convert Gregorian expires_at to Jalali for display
                                    if (isset($data['expires_at'])) {
                                        $data['expires_at'] = FeaturedNews::convertJalaliToGregorian($data['expires_at'])
                                            ? \Morilog\Jalali\Jalalian::fromCarbon($data['expires_at'])->format('Y-m-d H:i')
                                            : $data['expires_at'];
                                    }
                                    return $data;
                                })
                                ->mutateRelationshipDataBeforeSaveUsing(function ($data) {
                                    // Convert Jalali expires_at to Gregorian for storage
                                    if (isset($data['expires_at'])) {
                                        $data['expires_at'] = FeaturedNews::convertJalaliToGregorian($data['expires_at']);
                                    }
                                    return $data;
                                })
                                ->columns(3),
                        ]),
                        Forms\Components\Section::make('شبکه‌های اجتماعی')->schema([
                            Forms\Components\Toggle::make('auto_send_telegram')
                                ->label('ارسال خودکار به تلگرام')
                                ->helperText(fn ($record) => $record?->telegram_sent_at
                                    ? 'به تلگرام ارسال شده است.'
                                    : 'هنگام انتشار خبر، یک‌بار به کانال تلگرام ارسال می‌شود.')
                                ->default(false),
                            Forms\Components\Toggle::make('auto_send_whatsapp')
                                ->label('ارسال خودکار به واتساپ')
                                ->helperText(fn ($record) => $record?->whatsapp_sent_at
                                    ? 'به واتساپ ارسال شده است.'
                                    : 'هنگام انتشار خبر، یک‌بار به واتساپ ارسال می‌شود.')
                                ->default(false),
                        ])->description('انتشار خودکار خبر پس از انتشار در سایت'),
                        Forms\Components\Section::make('تنظیمات نمایش')->schema([
                            Forms\Components\ColorPicker::make('title_color')
                                ->label('رنگ تیتر'),
                            Forms\Components\Toggle::make('show_visits')
                                ->label('نمایش تعداد بازدید')
                                ->default(true),
                            Forms\Components\Toggle::make('show_comments')
                                ->label('نمایش دیدگاه‌ها')
                                ->default(true),
                        ])->description('نحوه نمایش خبر در سایت'),
                        Forms\Components\Section::make('نویسنده')->schema([
                            Forms\Components\Select::make('author_id')->options([78 => 'بدون نام'] + Author::where('id','!=',78)->pluck('name', 'id')->toArray())->label('')->searchable()->required(),
                        ])->description('محتوا با نام چه کسی در سایت منتشر شود؟'),
                        Forms\Components\Section::make('سئو')->schema([
                            Forms\Components\TextInput::make('seo_title')
                                ->label('عنوان سئو')
                                ->helperText('عنوان خبر به عنوان پیشفرض'),
                            Forms\Components\Textarea::make('meta_desc')->label('کلمات کلیدی')
                        ])->description('تنظیمات خبر در گوگل')
                    ])->columnSpan(4),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(self::getCommonNewsColumns())
            ->filters([
                Tables\Filters\SelectFilter::make('categories')
                    ->relationship('categories', 'title')
                    ->label('دسته‌بندی')
                    ->searchable(),

                Tables\Filters\SelectFilter::make('author_id')
                    ->relationship('author', 'name')
                    ->label('نویسنده')
                    ->searchable()
                ,

                Tables\Filters\SelectFilter::make('user_id')
                    ->relationship('user', 'name')
                    ->label('کاربر')
                    ->searchable(),

                Tables\Filters\Filter::make('id')
                    ->form([
                        Forms\Components\TextInput::make('id')->label('کد خبر'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query->when($data['id'], fn($query, $id) => $query->where('id', $id));
                    }),

                Tables\Filters\Filter::make('publish_at')
                    ->form([
                        Forms\Components\DatePicker::make('from')->jalali()->label('از تاریخ'),
                        Forms\Components\DatePicker::make('to')->jalali()->label('تا تاریخ'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['from'] ?? null, fn($query, $from) => $query->whereDate('publish_at', '>=', $from))
                            ->when($data['to'] ?? null, fn($query, $to) => $query->whereDate('publish_at', '<=', $to));
                    }),

            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('approve')
                    ->label('تأیید و انتشار')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (News $record) => $record->status === News::STATUS_PENDING_REVIEW
                        && ! $record->trashed()
                        && News::userCanPublish())
                    ->requiresConfirmation()
                    ->modalHeading('تأیید و انتشار خبر')
                    ->modalDescription(fn (News $record) => 'خبر «' . $record->title . '» منتشر شود؟')
                    ->action(fn (News $record) => self::approveNews($record)),
                Tables\Actions\Action::make('reject')
                    ->label('بازگشت برای اصلاح')
                    ->icon('heroicon-o-arrow-uturn-right')
                    ->color('danger')
                    ->visible(fn (News $record) => $record->status === News::STATUS_PENDING_REVIEW
                        && ! $record->trashed()
                        && News::userCanPublish())
                    ->form([
                        Forms\Components\Textarea::make('review_reason')
                            ->label('دلیل بازگشت برای اصلاح')
                            ->required()
                            ->rows(3),
                    ])
                    ->modalHeading('بازگشت خبر برای اصلاح')
                    ->action(fn (News $record, array $data) => self::rejectNews($record, $data['review_reason'])),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('social_publish')
                        ->label('ارسال به شبکه‌های اجتماعی')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('info')
                        ->visible(fn (News $record) => $record->status === 'published' && ! $record->trashed())
                        ->requiresConfirmation()
                        ->modalHeading('ارسال به شبکه‌های اجتماعی')
                        ->modalDescription(fn (News $record) => 'خبر «' . $record->title . '» به تلگرام و واتساپ ارسال شود؟')
                        ->action(function (News $record) {
                            $results = app(SocialPublishService::class)->manualPublish($record);

                            $success = collect($results)->contains(fn ($result) => $result['ok']);

                            Notification::make()
                                ->title('نتیجه ارسال به شبکه‌های اجتماعی')
                                ->body('تلگرام: ' . $results['telegram']['message']
                                    . ' — واتساپ: ' . $results['whatsapp']['message'])
                                ->{$success ? 'success' : 'warning'}()
                                ->send();
                        }),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\RestoreAction::make()
                        ->label('بازیابی'),
                    Tables\Actions\ForceDeleteAction::make()
                        ->label('حذف دائمی'),
                    Tables\Actions\Action::make('history')
                        ->label('تاریخچه')
                        ->icon('heroicon-o-clock')
                        ->color('gray')
                        ->modalHeading(fn ($record) => 'تاریخچه تغییرات خبر ' . $record->id)
                        ->modalContent(fn ($record) => view('filament.news-history', [
                            'revisions' => $record->revisions()->with('user')->limit(50)->get(),
                        ]))
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('بستن'),
                    Tables\Actions\Action::make('copy_link')
                        ->label('لینک پست')
                        ->icon('heroicon-o-clipboard')
//                        ->action(fn (News $record) => Component::dispatchBrowserEvent('copyToClipboard', ['text' => $record->getUrl()]))
                        ->url(fn($record) => $record->getUrl())
                        ->color('success')
                        ->openUrlInNewTab(true),
                    Tables\Actions\Action::make('copy_link')
                        ->label('لینک کوتاه')
                        ->icon('heroicon-o-clipboard')
//                        ->action(fn (News $record) => Component::dispatchBrowserEvent('copyToClipboard', ['text' => $record->getUrl()]))
                        ->url(fn($record) => $record->getShortUrl())
                        ->openUrlInNewTab(true),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make()
                        ->label('بازیابی'),
                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->label('حذف دائمی'),
                ]),
            ])
            ->defaultSort('id', 'DESC')
            ->poll('15s');
    }

    /**
     * Include soft-deleted news so the "حذف‌شده" cartable and the
     * restore/force-delete actions can reach trashed records.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
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
            'index' => Pages\ListNews::route('/'),
            'create' => Pages\CreateNews::route('/create'),
            'edit' => Pages\EditNews::route('/{record}/edit'),
        ];
    }

    /**
     * Shield permissions for this resource. Overrides the shared
     * FilamentContent trait to add the custom "publish" prefix, generating
     * the `publish_news` permission that gates the editorial workflow
     * (انتشار، زمان‌بندی، تعلیق، تأیید و بازگشت برای اصلاح).
     */
    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'create',
            'update',
            'delete',
            'publish',
        ];
    }

    /**
     * گردش کار تحریریه — status choices by role:
     * users lacking publish_news can only save drafts or submit for review.
     */
    public static function statusOptionsForCurrentUser(): array
    {
        $options = [
            'draft' => 'پیش نویس',
            'pending_review' => 'در انتظار تأیید',
        ];

        if (News::userCanPublish()) {
            $options += [
                'scheduled' => 'زمان بندی شده',
                'published' => 'منتشر شده',
                'suspended' => 'معلق',
            ];
        }

        return $options;
    }

    /**
     * Approve a pending news item: publish it and confirm to the editor.
     * The observer records the "approved" revision and fires social sends.
     */
    public static function approveNews(News $record): void
    {
        $record->status = 'published';

        try {
            $publishAt = $record->publish_at ? Carbon::parse($record->publish_at) : null;
        } catch (\Throwable) {
            $publishAt = null;
        }

        if (! $publishAt || $publishAt->isFuture()) {
            $record->publish_at = now();
        }

        $record->save();

        Notification::make()
            ->title('خبر تأیید و منتشر شد')
            ->success()
            ->send();
    }

    /**
     * Send a pending news item back to draft with a required reason.
     * The observer records the "rejected" revision (including the reason)
     * and notifies the author.
     */
    public static function rejectNews(News $record, string $reason): void
    {
        $record->workflowRejectReason = $reason;
        $record->status = 'draft';
        $record->save();

        Notification::make()
            ->title('خبر برای اصلاح بازگردانده شد')
            ->warning()
            ->send();
    }
}
