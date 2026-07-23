<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BadgeResource\Pages;
use App\Models\Badge;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * مدیریت نشان‌ها (دستاوردهای) باشگاه اعضا.
 */
class BadgeResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Badge::class;

    protected static ?string $navigationIcon = 'fas-medal';

    protected static ?string $label = 'نشان';

    protected static ?string $navigationGroup = 'باشگاه اعضا';

    protected static ?int $navigationSort = 3;

    protected static bool $hasTitleCaseModelLabel = false;

    public static function getPluralModelLabel(): string
    {
        return 'نشان‌ها';
    }

    public static function getPluralLabel(): ?string
    {
        return 'نشان‌ها';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->label('کد یکتا')
                    ->helperText('شناسه انگلیسی یکتا؛ مثلاً first_steps یا loyal_reader')
                    ->required()
                    ->maxLength(100)
                    ->unique(ignoreRecord: true),

                Forms\Components\TextInput::make('title')
                    ->label('عنوان')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Textarea::make('description')
                    ->label('توضیح')
                    ->rows(2)
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('icon')
                    ->label('آیکون')
                    ->helperText('ایموجی (مثلاً 🏅)، متن کوتاه یا مسیر تصویر')
                    ->maxLength(255),

                Forms\Components\Select::make('condition_type')
                    ->label('نوع شرط دریافت')
                    ->options(Badge::CONDITION_TYPES)
                    ->default('manual')
                    ->live()
                    ->required(),

                Forms\Components\TextInput::make('condition_value')
                    ->label('آستانه شرط')
                    ->helperText('مثلاً ۱۰۰۰ امتیاز، ۷ روز زنجیره یا ۱۰ ماموریت — برای اهدای دستی بی‌اثر است')
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->visible(fn (Forms\Get $get) => $get('condition_type') !== 'manual'),

                Forms\Components\Toggle::make('is_active')
                    ->label('فعال')
                    ->default(true),

                Forms\Components\TextInput::make('sort')
                    ->label('ترتیب')
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('icon')
                    ->label('آیکون'),

                Tables\Columns\TextColumn::make('title')
                    ->label('عنوان')
                    ->searchable(),

                Tables\Columns\TextColumn::make('condition_type')
                    ->label('شرط')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => Badge::CONDITION_TYPES[$state] ?? $state),

                Tables\Columns\TextColumn::make('condition_value')
                    ->label('آستانه'),

                Tables\Columns\TextColumn::make('members_count')
                    ->label('دارندگان')
                    ->counts('members'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('فعال')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('condition_type')
                    ->label('نوع شرط')
                    ->options(Badge::CONDITION_TYPES),

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
            ])
            ->reorderable('sort')
            ->defaultSort('sort');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBadges::route('/'),
            'create' => Pages\CreateBadge::route('/create'),
            'edit' => Pages\EditBadge::route('/{record}/edit'),
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
