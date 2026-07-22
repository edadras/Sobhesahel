<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ArchiveCategoryResource\Pages;
use App\Filament\Resources\ArchiveCategoryResource\RelationManagers;
use App\Models\ArchiveCategory;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ArchiveCategoryResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = ArchiveCategory::class;

    protected static ?string $navigationIcon = 'fas-bars';

    protected static ?string $label = 'گروه آرشیو';

    protected static ?string $navigationGroup = 'محتوا';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?int $navigationSort = 20;

    public static function getPluralModelLabel(): string
    {
        return 'گروه آرشیو';
    }

    public static function getPluralLabel(): ?string
    {
        return 'گروه آرشیو';
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->label('عنوان'),
                Forms\Components\TextInput::make('en_title')
                    ->label('عنوان انگیسی'),
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->label('SLUG'),
                Forms\Components\TextInput::make('en_slug')
                    ->label('English SLUG'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('عنوان')
                    ->searchable(),

                Tables\Columns\TextColumn::make('en_title')
                    ->label('عنوان انگلیسی')
                    ->searchable(),

                Tables\Columns\TextColumn::make('slug')
                    ->label('نامک')
                    ->searchable(),

                Tables\Columns\TextColumn::make('en_slug')
                    ->label('نامک انگلیسی')
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاریخ ایجاد')
                    ->jalaliDateTime('H:i Y/m/d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('تاریخ بروزرسانی')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->jalaliDateTime('H:i Y/m/d'),
            ])
            ->filters([
                //
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
            'index' => Pages\ListArchiveCategories::route('/'),
            'create' => Pages\CreateArchiveCategory::route('/create'),
            'edit' => Pages\EditArchiveCategory::route('/{record}/edit'),
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
