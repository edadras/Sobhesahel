<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LanguageResource\Pages;
use App\Models\Language;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * مدیریت زبان‌های سایت (چندزبانه — WP-15).
 *
 * تا زمانی که هیچ زبانی ثبت نشده باشد، فارسی (rtl، پیش‌فرض) و انگلیسی (ltr)
 * به عنوان زبان‌های داخلی در نظر گرفته می‌شوند (Language::active()).
 */
class LanguageResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Language::class;

    protected static ?string $navigationIcon = 'fas-language';

    protected static ?string $label = 'زبان';

    protected static ?string $navigationGroup = 'تنظیمات';

    protected static bool $hasTitleCaseModelLabel = false;

    public static function getPluralModelLabel(): string
    {
        return 'زبان‌ها';
    }

    public static function getPluralLabel(): ?string
    {
        return 'زبان‌ها';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('نام (انگلیسی)')
                    ->helperText('مثلاً Persian یا English')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('native_name')
                    ->label('نام بومی')
                    ->helperText('مثلاً فارسی یا English')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('code')
                    ->label('کد زبان')
                    ->helperText('کد دو حرفی مانند fa یا en — قرارداد lang_id محتوا: fa=1 و en=2')
                    ->required()
                    ->maxLength(10)
                    ->unique(ignoreRecord: true),

                Forms\Components\Select::make('direction')
                    ->label('جهت نوشتار')
                    ->options([
                        'rtl' => 'راست به چپ (RTL)',
                        'ltr' => 'چپ به راست (LTR)',
                    ])
                    ->default('rtl')
                    ->required(),

                Forms\Components\TextInput::make('sort')
                    ->label('ترتیب')
                    ->numeric()
                    ->default(0),

                Forms\Components\Toggle::make('is_active')
                    ->label('فعال')
                    ->default(true),

                Forms\Components\Toggle::make('is_default')
                    ->label('زبان پیش‌فرض')
                    ->default(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('native_name')
                    ->label('نام بومی')
                    ->searchable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('نام (انگلیسی)')
                    ->searchable(),

                Tables\Columns\TextColumn::make('code')
                    ->label('کد')
                    ->badge(),

                Tables\Columns\TextColumn::make('direction')
                    ->label('جهت')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => $state === 'rtl' ? 'راست به چپ' : 'چپ به راست'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('فعال')
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_default')
                    ->label('پیش‌فرض')
                    ->boolean(),

                Tables\Columns\TextColumn::make('sort')
                    ->label('ترتیب')
                    ->sortable(),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLanguages::route('/'),
            'create' => Pages\CreateLanguage::route('/create'),
            'edit' => Pages\EditLanguage::route('/{record}/edit'),
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
