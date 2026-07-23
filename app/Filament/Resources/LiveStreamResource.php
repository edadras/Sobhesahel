<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LiveStreamResource\Pages;
use App\Models\LiveStream;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LiveStreamResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = LiveStream::class;

    protected static ?string $navigationIcon = 'fas-tower-broadcast';

    protected static ?string $label = 'پخش زنده';

    protected static ?string $navigationGroup = 'محتوا';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?int $navigationSort = 25;

    public static function getPluralModelLabel(): string
    {
        return 'پخش‌های زنده';
    }

    public static function getPluralLabel(): ?string
    {
        return 'پخش‌های زنده';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->label('عنوان')
                    ->placeholder('مثلاً پخش زنده نشست خبری استاندار هرمزگان')
                    ->columnSpanFull(),

                Forms\Components\Select::make('platform')
                    ->required()
                    ->label('پلتفرم')
                    ->options(LiveStream::PLATFORMS)
                    ->default('aparat')
                    ->native(false)
                    ->live(),

                Forms\Components\TextInput::make('embed_url')
                    ->required()
                    ->url()
                    ->label('آدرس پخش (URL)')
                    ->placeholder('https://www.aparat.com/live/...')
                    ->helperText('فقط آدرس صفحه یا استریم را وارد کنید؛ کد iframe به‌صورت خودکار و امن ساخته می‌شود.')
                    ->rule(fn (Forms\Get $get) => function (string $attribute, $value, \Closure $fail) use ($get) {
                        if (!LiveStream::validateUrlForPlatform((string) $value, (string) $get('platform'))) {
                            $fail('آدرس وارد شده با پلتفرم انتخابی هم‌خوانی ندارد یا معتبر نیست.');
                        }
                    }),

                Forms\Components\Textarea::make('description')
                    ->label('توضیحات')
                    ->rows(3)
                    ->columnSpanFull(),

                Forms\Components\FileUpload::make('image')
                    ->label('تصویر (پوستر)')
                    ->image()
                    ->directory('live-streams'),

                Forms\Components\DateTimePicker::make('starts_at')
                    ->label('زمان شروع')
                    ->jalali()
                    ->helperText('برای پخش‌های آینده؛ خالی یعنی بدون زمان‌بندی.'),

                Forms\Components\TextInput::make('sort')
                    ->numeric()
                    ->label('ترتیب نمایش')
                    ->default(0),

                Forms\Components\Toggle::make('is_live')
                    ->label('درحال پخش')
                    ->helperText('با فعال بودن، نشان «پخش زنده» در سایت نمایش داده می‌شود.'),

                Forms\Components\Toggle::make('is_active')
                    ->label('فعال')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort')
            ->defaultSort('sort')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('عنوان')
                    ->searchable()
                    ->limit(60),

                Tables\Columns\TextColumn::make('platform')
                    ->label('پلتفرم')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => LiveStream::PLATFORMS[$state] ?? $state)
                    ->sortable(),

                Tables\Columns\ToggleColumn::make('is_live')
                    ->label('درحال پخش'),

                Tables\Columns\TextColumn::make('starts_at')
                    ->label('زمان شروع')
                    ->jalaliDateTime('H:i Y/m/d')
                    ->sortable(),

                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('فعال'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاریخ ایجاد')
                    ->jalaliDateTime('H:i Y/m/d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('platform')
                    ->label('پلتفرم')
                    ->options(LiveStream::PLATFORMS),

                Tables\Filters\TernaryFilter::make('is_live')
                    ->label('درحال پخش'),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('فعال'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListLiveStreams::route('/'),
            'create' => Pages\CreateLiveStream::route('/create'),
            'edit' => Pages\EditLiveStream::route('/{record}/edit'),
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
