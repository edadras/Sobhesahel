<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdvertiseResource\Pages;
use App\Filament\Resources\AdvertiseResource\RelationManagers;
use App\Models\Advertise;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

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
                Forms\Components\TextInput::make('url')->label('آدرس')->required()->url(),
                Forms\Components\ToggleButtons::make('status')
                    ->options([
                        'text' => 'متن',
                        'image' => 'عکس',
                    ])
                    ->icons([
                        'text' => 'heroicon-o-pencil',
                        'image' => 'heroicon-o-clock',
                    ])
                    ->colors([
                        'text' => 'warning',
                        'image' => 'danger',
                    ])
                    ->label('نوع تبلیغ')
                    ->inline()
                    ->required()
                    ->reactive()
                    ->default(fn ($get) => $get('image') ? 'image' : 'text')
                    ->formatStateUsing(fn ($state, $record) => $record?->image ? 'image' : 'text'),

                Forms\Components\TextInput::make('text')
                    ->label('متن تبلیغات')
                    ->hidden(fn ($get) => $get('status') !== 'text')
                    ->required(fn ($get) => $get('status') === 'text'),

                Forms\Components\FileUpload::make('image')
                    ->label('تصویر تبلیغات')
                    ->image()
                    ->hidden(fn ($get) => $get('status') !== 'image')
                    ->required(fn ($get) => $get('status') === 'image'),
                Forms\Components\Toggle::make('is_active')->label('آیا فعال است؟')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('نام'),
                Tables\Columns\TextColumn::make('url')->label('آدرس'),
                Tables\Columns\IconColumn::make('is_active')->label('آیا فعال است')->boolean()
            ])
            ->filters([
                //
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
