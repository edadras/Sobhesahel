<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WheelPrizeResource\Pages;
use App\Models\WheelPrize;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * مدیریت جوایز چرخ شانس — تصمیم نهایی ۵-۳ نقشه راه: جوایز، وزن احتمال و
 * موجودی کاملاً از پنل مدیریت قابل‌تعریف است.
 */
class WheelPrizeResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = WheelPrize::class;

    protected static ?string $navigationIcon = 'fas-gift';

    protected static ?string $label = 'جایزه چرخ شانس';

    protected static ?string $navigationGroup = 'باشگاه اعضا';

    protected static ?int $navigationSort = 4;

    protected static bool $hasTitleCaseModelLabel = false;

    public static function getPluralModelLabel(): string
    {
        return 'جوایز چرخ شانس';
    }

    public static function getPluralLabel(): ?string
    {
        return 'جوایز چرخ شانس';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('عنوان')
                    ->helperText('همان متنی که روی چرخ نمایش داده می‌شود؛ مثلاً «۵۰ امتیاز» یا «پوچ»')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Select::make('type')
                    ->label('نوع جایزه')
                    ->options(WheelPrize::TYPES)
                    ->default('points')
                    ->live()
                    ->required(),

                Forms\Components\TextInput::make('value')
                    ->label('مقدار')
                    ->helperText(fn (Forms\Get $get) => $get('type') === 'coupon_text'
                        ? 'متن کد تخفیف که به عضو نشان داده می‌شود'
                        : 'تعداد امتیازی که به عضو اضافه می‌شود')
                    ->numeric(fn (Forms\Get $get) => $get('type') === 'points')
                    ->visible(fn (Forms\Get $get) => $get('type') !== 'nothing')
                    ->maxLength(255),

                Forms\Components\TextInput::make('weight')
                    ->label('وزن احتمال')
                    ->helperText('شانس نسبی این خانه؛ عدد بزرگ‌تر = احتمال بیشتر. صفر = هرگز برنده نمی‌شود.')
                    ->numeric()
                    ->minValue(0)
                    ->default(1)
                    ->required(),

                Forms\Components\TextInput::make('stock')
                    ->label('موجودی')
                    ->helperText('تعداد دفعاتی که این جایزه می‌تواند برده شود. خالی = نامحدود.')
                    ->numeric()
                    ->minValue(0)
                    ->nullable(),

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
                Tables\Columns\TextColumn::make('title')
                    ->label('عنوان')
                    ->searchable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('نوع')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'points' => 'success',
                        'coupon_text' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => WheelPrize::TYPES[$state] ?? $state),

                Tables\Columns\TextColumn::make('value')
                    ->label('مقدار')
                    ->limit(30),

                Tables\Columns\TextColumn::make('weight')
                    ->label('وزن')
                    ->sortable(),

                Tables\Columns\TextColumn::make('stock')
                    ->label('موجودی')
                    ->placeholder('نامحدود'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('فعال')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('نوع')
                    ->options(WheelPrize::TYPES),

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
            'index' => Pages\ListWheelPrizes::route('/'),
            'create' => Pages\CreateWheelPrize::route('/create'),
            'edit' => Pages\EditWheelPrize::route('/{record}/edit'),
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
