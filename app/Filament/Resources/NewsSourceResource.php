<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NewsSourceResource\Pages;
use App\Models\NewsSource;
use App\Services\NewsCrawlerService;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class NewsSourceResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = NewsSource::class;

    protected static ?string $navigationIcon = 'fas-rss';

    protected static ?string $label = 'منبع خبری';

    protected static ?string $navigationGroup = 'رصد منابع';

    protected static ?int $navigationSort = 10;

    protected static bool $hasTitleCaseModelLabel = false;

    public static function getPluralModelLabel(): string
    {
        return 'منابع خبری';
    }

    public static function getPluralLabel(): ?string
    {
        return 'منابع خبری';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->label('نام منبع')
                    ->placeholder('مثلاً خبرگزاری ایرنا'),

                Forms\Components\TextInput::make('url')
                    ->url()
                    ->label('نشانی سایت')
                    ->placeholder('https://example.com'),

                Forms\Components\TextInput::make('feed_url')
                    ->required()
                    ->url()
                    ->label('نشانی فید (RSS/Atom)')
                    ->placeholder('https://example.com/rss')
                    ->columnSpanFull(),

                Forms\Components\Select::make('type')
                    ->required()
                    ->label('نوع فید')
                    ->options(NewsSource::TYPES)
                    ->default('rss')
                    ->native(false)
                    ->helperText('در صورت تشخیص خودکار از روی محتوای فید، این مقدار فقط نقش پیش‌فرض دارد.'),

                Forms\Components\Select::make('category_id')
                    ->relationship('category', 'title')
                    ->label('سرویس (دسته‌بندی) پیش‌فرض')
                    ->searchable()
                    ->preload()
                    ->helperText('اخباری که از این منبع در سامانه ذخیره می‌شوند به این دسته منتسب خواهند شد.'),

                Forms\Components\TextInput::make('fetch_interval_minutes')
                    ->required()
                    ->numeric()
                    ->minValue(15)
                    ->default(60)
                    ->label('بازه دریافت (دقیقه)')
                    ->helperText('حداقل ۱۵ دقیقه — خزنده هر ۱۵ دقیقه اجرا و فقط منابع سررسیدشده را دریافت می‌کند.'),

                Forms\Components\Toggle::make('is_active')
                    ->label('فعال')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('نام منبع')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('feed_url')
                    ->label('نشانی فید')
                    ->limit(50)
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('نوع')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => NewsSource::TYPES[$state] ?? $state),

                Tables\Columns\TextColumn::make('category.title')
                    ->label('سرویس پیش‌فرض')
                    ->placeholder('—')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('fetch_interval_minutes')
                    ->label('بازه (دقیقه)')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('فعال')
                    ->boolean(),

                Tables\Columns\TextColumn::make('fetched_items_count')
                    ->counts('fetchedItems')
                    ->label('اخبار دریافتی')
                    ->sortable(),

                Tables\Columns\TextColumn::make('last_fetched_at')
                    ->label('آخرین دریافت')
                    ->jalaliDateTime('H:i Y/m/d')
                    ->placeholder('هنوز دریافت نشده')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('وضعیت فعال بودن'),
            ])
            ->actions([
                Tables\Actions\Action::make('testFetch')
                    ->label('دریافت آزمایشی')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->action(function (NewsSource $record) {
                        $result = app(NewsCrawlerService::class)->crawlSource($record);

                        if ($result['error'] !== null) {
                            Notification::make()
                                ->danger()
                                ->title('خطا در دریافت از منبع')
                                ->body($result['error'])
                                ->persistent()
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->success()
                            ->title('دریافت با موفقیت انجام شد')
                            ->body("تعداد آیتم‌های خوانده‌شده از فید: {$result['fetched']} — آیتم‌های جدید ذخیره‌شده: {$result['new']}")
                            ->send();
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListNewsSources::route('/'),
            'create' => Pages\CreateNewsSource::route('/create'),
            'edit' => Pages\EditNewsSource::route('/{record}/edit'),
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
