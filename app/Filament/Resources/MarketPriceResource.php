<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MarketPriceResource\Pages;
use App\Models\MarketPrice;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MarketPriceResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = MarketPrice::class;

    protected static ?string $navigationIcon = 'fas-coins';

    protected static ?string $label = 'قیمت بازار';

    protected static ?string $navigationGroup = 'سایر';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?int $navigationSort = 30;

    public static function getPluralModelLabel(): string
    {
        return 'قیمت‌های بازار';
    }

    public static function getPluralLabel(): ?string
    {
        return 'قیمت‌های بازار';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->label('عنوان')
                    ->placeholder('مثلاً دلار آمریکا'),

                Forms\Components\TextInput::make('symbol')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->label('نماد (کلید یکتا)')
                    ->placeholder('مثلاً usd')
                    ->helperText('برای بروزرسانی خودکار، باید با نماد سرویس‌دهنده قیمت یکسان باشد.'),

                Forms\Components\Select::make('category')
                    ->required()
                    ->label('دسته')
                    ->options(MarketPrice::CATEGORIES)
                    ->default('currency')
                    ->native(false),

                Forms\Components\TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->label('قیمت')
                    ->default(0),

                Forms\Components\TextInput::make('change_amount')
                    ->numeric()
                    ->label('میزان تغییر')
                    ->helperText('مثبت = افزایش، منفی = کاهش'),

                Forms\Components\TextInput::make('change_percent')
                    ->numeric()
                    ->label('درصد تغییر')
                    ->helperText('مثبت = افزایش، منفی = کاهش'),

                Forms\Components\TextInput::make('unit')
                    ->required()
                    ->label('واحد')
                    ->default('تومان'),

                Forms\Components\TextInput::make('source')
                    ->label('منبع')
                    ->placeholder('مثلاً manual'),

                Forms\Components\TextInput::make('sort_order')
                    ->numeric()
                    ->label('ترتیب نمایش')
                    ->default(0),

                Forms\Components\DateTimePicker::make('fetched_at')
                    ->label('زمان بروزرسانی قیمت')
                    ->jalali()
                    ->default(now()),

                Forms\Components\Toggle::make('is_active')
                    ->label('فعال')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('عنوان')
                    ->searchable(),

                Tables\Columns\TextColumn::make('symbol')
                    ->label('نماد')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('category')
                    ->label('دسته')
                    ->formatStateUsing(fn (string $state): string => MarketPrice::CATEGORIES[$state] ?? $state)
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('قیمت')
                    ->formatStateUsing(fn (MarketPrice $record): string => MarketPrice::trimZeros($record->price) . ' ' . $record->unit)
                    ->sortable(),

                Tables\Columns\TextColumn::make('change_percent')
                    ->label('تغییر')
                    ->formatStateUsing(fn (MarketPrice $record): string => ($record->trend > 0 ? '+' : ($record->trend < 0 ? '-' : '')) . MarketPrice::trimZeros(abs((float) $record->change_percent), false) . '٪')
                    ->color(fn (MarketPrice $record): string => $record->trend > 0 ? 'success' : ($record->trend < 0 ? 'danger' : 'gray')),

                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('فعال'),

                Tables\Columns\TextColumn::make('fetched_at')
                    ->label('بروزرسانی قیمت')
                    ->jalaliDateTime('H:i Y/m/d')
                    ->sortable(),

                Tables\Columns\TextColumn::make('source')
                    ->label('منبع')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('تاریخ بروزرسانی')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->jalaliDateTime('H:i Y/m/d'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label('دسته')
                    ->options(MarketPrice::CATEGORIES),

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
            'index' => Pages\ListMarketPrices::route('/'),
            'create' => Pages\CreateMarketPrice::route('/create'),
            'edit' => Pages\EditMarketPrice::route('/{record}/edit'),
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
