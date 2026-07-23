<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubscriptionPlanResource\Pages;
use App\Models\Payment;
use App\Models\SubscriptionPlan;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * پلن‌های اشتراک ویژه (درآمدزایی).
 */
class SubscriptionPlanResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = SubscriptionPlan::class;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $label = 'پلن اشتراک';

    protected static ?string $navigationGroup = 'درآمدزایی';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?int $navigationSort = 2;

    public static function getPluralModelLabel(): string
    {
        return 'پلن‌های اشتراک';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('نام پلن')
                        ->placeholder('مثلا: اشتراک سه‌ماهه')
                        ->required(),
                    Forms\Components\TextInput::make('duration_days')
                        ->label('مدت (روز)')
                        ->numeric()
                        ->minValue(1)
                        ->required(),
                    Forms\Components\TextInput::make('price')
                        ->label('قیمت (تومان)')
                        ->numeric()
                        ->minValue(0)
                        ->required(),
                    Forms\Components\TagsInput::make('features')
                        ->label('ویژگی‌ها')
                        ->placeholder('هر ویژگی را نوشته و Enter بزنید')
                        ->columnSpanFull(),
                    Forms\Components\Toggle::make('is_active')
                        ->label('فعال')
                        ->default(true),
                    Forms\Components\TextInput::make('sort')
                        ->label('ترتیب نمایش')
                        ->numeric()
                        ->default(0),
                ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('نام پلن')
                    ->searchable(),

                Tables\Columns\TextColumn::make('duration_days')
                    ->label('مدت (روز)')
                    ->formatStateUsing(fn ($state) => Payment::faNumber((string) $state))
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('قیمت')
                    ->formatStateUsing(fn ($state) => Payment::faNumber(number_format((int) $state)) . ' تومان')
                    ->sortable(),

                Tables\Columns\TextColumn::make('subscriptions_count')
                    ->label('تعداد اشتراک')
                    ->counts('subscriptions'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('فعال')
                    ->boolean(),

                Tables\Columns\TextColumn::make('sort')
                    ->label('ترتیب')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
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
            ->defaultSort('sort');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubscriptionPlans::route('/'),
            'create' => Pages\CreateSubscriptionPlan::route('/create'),
            'edit' => Pages\EditSubscriptionPlan::route('/{record}/edit'),
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
