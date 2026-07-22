<?php

namespace App\Filament\Resources;

use App\Filament\Resources\YektanetAdResource\Pages;
use App\Models\YektanetAd;
use App\Support\YektanetAdConfig;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class YektanetAdResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = YektanetAd::class;

    protected static ?string $navigationIcon = 'fas-code';

    protected static ?string $label = 'تبلیغ یکتانت';

    protected static ?string $navigationGroup = 'سایر';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?int $navigationSort = 201;

    public static function getPluralModelLabel(): string
    {
        return 'تبلیغات یکتانت';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('اطلاعات تبلیغ')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('نام')
                            ->required()
                            ->maxLength(255)
                            ->helperText('فقط برای شناسایی در پنل مدیریت است.'),

                        Forms\Components\Textarea::make('code')
                            ->label('کد تبلیغ یکتانت')
                            ->required()
                            ->rows(12)
                            ->columnSpanFull()
                            ->helperText('کد HTML/JavaScript دریافتی از پنل yektanet.com را اینجا قرار دهید.'),

                        Forms\Components\Select::make('position')
                            ->label('موقعیت نمایش')
                            ->options(YektanetAdConfig::POSITIONS)
                            ->required()
                            ->native(false)
                            ->searchable(),

                        Forms\Components\CheckboxList::make('page_scopes')
                            ->label('صفحات نمایش')
                            ->options(YektanetAdConfig::PAGE_SCOPES)
                            ->required()
                            ->columns(2)
                            ->bulkToggleable()
                            ->helperText('می‌توانید چند صفحه را انتخاب کنید. گزینه «همه صفحات» به‌تنهایی کافی است.'),

                        Forms\Components\TextInput::make('sort_order')
                            ->label('ترتیب نمایش')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->helperText('عدد کوچک‌تر زودتر نمایش داده می‌شود.'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('فعال')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('نام')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('position')
                    ->label('موقعیت')
                    ->formatStateUsing(fn (string $state): string => YektanetAdConfig::POSITIONS[$state] ?? $state)
                    ->sortable(),

                Tables\Columns\TextColumn::make('page_scopes')
                    ->label('صفحات')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => YektanetAdConfig::PAGE_SCOPES[$state] ?? $state),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('ترتیب')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('فعال')
                    ->boolean(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('آخرین ویرایش')
                    ->jalaliDateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order')
            ->filters([
                Tables\Filters\SelectFilter::make('position')
                    ->label('موقعیت')
                    ->options(YektanetAdConfig::POSITIONS),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('فعال'),
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListYektanetAds::route('/'),
            'create' => Pages\CreateYektanetAd::route('/create'),
            'view' => Pages\ViewYektanetAd::route('/{record}'),
            'edit' => Pages\EditYektanetAd::route('/{record}/edit'),
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
