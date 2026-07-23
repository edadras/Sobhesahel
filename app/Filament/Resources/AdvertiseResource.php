<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdvertiseResource\Pages;
use App\Filament\Resources\AdvertiseResource\RelationManagers;
use App\Models\Advertise;
use App\Support\AdvertisePositionConfig;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

/**
 * تبلیغات (roadmap section 8).
 *
 * Video / VAST integration
 * ------------------------
 * Video ads (نوع «ویدیو») are NOT rendered by the site's banner placements.
 * Each active video ad is served as a minimal inline linear VAST 4.x document at:
 *
 *     GET /ads/vast/{id}        (route name: advertise_vast)
 *
 * Any VAST-compatible video player (JW Player, video.js + IMA/vast plugin,
 * Fluid Player, ...) can consume that URL as its ad tag. The document wires:
 *   - <Impression>   -> /ads/impression/{id}  (logs a "view" advertise_event)
 *   - <ClickThrough> -> /ads/click/{id}       (logs a "click" event + redirect)
 *   - <MediaFile>    -> the uploaded video file or the external video URL
 *   - <Duration>     -> the "مدت ویدیو (ثانیه)" field
 * The endpoint returns 404 while the ad is inactive, outside its schedule
 * window, or past its click/view limits — players fall back gracefully.
 */
class AdvertiseResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Advertise::class;

    protected static ?string $navigationIcon = 'fas-cube';


    protected static ?string $label = 'تبلیغات';

    protected static ?string $navigationGroup = 'سایر';

    protected static bool $hasTitleCaseModelLabel = false;

    public static function getPluralModelLabel(): string
    {
        return 'تبلیغات';
    }

    protected static ?int $navigationSort = 200;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')->label('نام')->required(),
                Forms\Components\TextInput::make('url')->label('آدرس (لینک مقصد)')->required()->url(),
                Forms\Components\ToggleButtons::make('type')
                    ->options([
                        'text' => 'متن',
                        'image' => 'عکس',
                        'video' => 'ویدیو',
                    ])
                    ->icons([
                        'text' => 'heroicon-o-pencil',
                        'image' => 'heroicon-o-photo',
                        'video' => 'heroicon-o-video-camera',
                    ])
                    ->colors([
                        'text' => 'warning',
                        'image' => 'danger',
                        'video' => 'info',
                    ])
                    ->label('نوع تبلیغ')
                    ->inline()
                    ->required()
                    ->reactive()
                    ->default('image')
                    ->formatStateUsing(fn ($state, $record) => $state ?? ($record?->image ? 'image' : 'text')),

                Forms\Components\TextInput::make('text')
                    ->label('متن تبلیغات')
                    ->hidden(fn ($get) => $get('type') !== 'text')
                    ->required(fn ($get) => $get('type') === 'text'),

                Forms\Components\FileUpload::make('image')
                    ->label('تصویر تبلیغات')
                    ->image()
                    ->hidden(fn ($get) => $get('type') !== 'image')
                    ->required(fn ($get) => $get('type') === 'image'),

                Forms\Components\Fieldset::make('تبلیغ ویدیویی (VAST)')
                    ->schema([
                        Forms\Components\FileUpload::make('video')
                            ->label('فایل ویدیو')
                            ->directory('advertises/videos')
                            ->acceptedFileTypes(['video/mp4', 'video/webm'])
                            ->maxSize(102400)
                            ->required(fn ($get) => $get('type') === 'video' && blank($get('video_url')))
                            ->helperText('در صورت وارد کردن آدرس ویدیوی خارجی، بارگذاری فایل الزامی نیست.'),

                        Forms\Components\TextInput::make('video_url')
                            ->label('آدرس ویدیوی خارجی')
                            ->url()
                            ->helperText('اختیاری؛ در صورت تکمیل، به‌جای فایل بارگذاری‌شده استفاده می‌شود.'),

                        Forms\Components\TextInput::make('video_duration')
                            ->label('مدت ویدیو (ثانیه)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(3600)
                            ->default(30),

                        Forms\Components\Placeholder::make('vast_url')
                            ->label('آدرس VAST برای پخش‌کننده‌ها')
                            ->content(fn ($record) => $record?->id
                                ? route('advertise_vast', ['id' => $record->id])
                                : 'پس از ذخیره نمایش داده می‌شود.')
                            ->helperText('این آدرس را به‌عنوان Ad Tag در پخش‌کننده ویدیویی سازگار با VAST وارد کنید.'),
                    ])
                    ->columns(2)
                    ->hidden(fn ($get) => $get('type') !== 'video'),

                Forms\Components\Select::make('positions')
                    ->label('جایگاه‌های نمایش')
                    ->multiple()
                    ->options(fn ($get) => $get('type') === 'text'
                        ? AdvertisePositionConfig::TEXT_POSITIONS
                        : AdvertisePositionConfig::IMAGE_POSITIONS)
                    ->helperText('این تبلیغ در جایگاه‌های انتخاب شده در سایت نمایش داده می‌شود.')
                    ->hidden(fn ($get) => $get('type') === 'video')
                    ->dehydrated(false),

                Forms\Components\Fieldset::make('زمان‌بندی و محدودیت نمایش')
                    ->schema([
                        Forms\Components\DateTimePicker::make('starts_at')
                            ->label('شروع نمایش')
                            ->jalali()
                            ->seconds(false)
                            ->helperText('خالی = نمایش از هم‌اکنون'),

                        Forms\Components\DateTimePicker::make('ends_at')
                            ->label('پایان نمایش')
                            ->jalali()
                            ->seconds(false)
                            ->after('starts_at')
                            ->helperText('خالی = بدون تاریخ پایان'),

                        Forms\Components\TextInput::make('max_views')
                            ->label('حداکثر تعداد نمایش')
                            ->numeric()
                            ->minValue(0)
                            ->helperText('خالی یا صفر = بدون محدودیت'),

                        Forms\Components\TextInput::make('max_clicks')
                            ->label('حداکثر تعداد کلیک')
                            ->numeric()
                            ->minValue(0)
                            ->helperText('خالی یا صفر = بدون محدودیت'),
                    ])
                    ->columns(2),

                Forms\Components\Toggle::make('is_active')->label('آیا فعال است؟')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('نام')->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('نوع')
                    ->badge()
                    ->formatStateUsing(fn ($state, $record) => match ($state ?? ($record?->image ? 'image' : 'text')) {
                        'video' => 'ویدیو',
                        'image' => 'عکس',
                        default => 'متن',
                    })
                    ->color(fn ($state, $record) => match ($state ?? ($record?->image ? 'image' : 'text')) {
                        'video' => 'info',
                        'image' => 'danger',
                        default => 'warning',
                    }),
                Tables\Columns\TextColumn::make('view')
                    ->label('نمایش')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('click')
                    ->label('کلیک')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ctr')
                    ->label('نرخ کلیک (CTR)')
                    ->state(fn ($record) => (int) $record->view > 0
                        ? round(((int) $record->click / (int) $record->view) * 100, 2) . '٪'
                        : '—'),
                Tables\Columns\TextColumn::make('starts_at')
                    ->label('شروع نمایش')
                    ->jalaliDateTime('H:i Y/m/d')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('ends_at')
                    ->label('پایان نمایش')
                    ->jalaliDateTime('H:i Y/m/d')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\ToggleColumn::make('is_active')->label('فعال'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('نوع تبلیغ')
                    ->options([
                        'text' => 'متن',
                        'image' => 'عکس',
                        'video' => 'ویدیو',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')->label('فعال'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListAdvertises::route('/'),
            'create' => Pages\CreateAdvertise::route('/create'),
            'view' => Pages\ViewAdvertise::route('/{record}'),
            'edit' => Pages\EditAdvertise::route('/{record}/edit'),
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
