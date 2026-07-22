<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuestionCategoryResource\Pages;
use App\Models\QuestionCategory;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class QuestionCategoryResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = QuestionCategory::class;

    protected static ?string $navigationIcon = 'fas-bars';

    protected static ?string $label = 'دسته‌بندی پرسش‌ها';

    protected static ?string $navigationGroup = 'پرسش و پاسخ';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?int $navigationSort = 2;

    public static function getPluralModelLabel(): string
    {
        return 'دسته‌بندی پرسش‌ها';
    }

    public static function getPluralLabel(): ?string
    {
        return 'دسته‌بندی پرسش‌ها';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->label('عنوان'),
                Forms\Components\TextInput::make('slug')
                    ->label('SLUG')
                    ->helperText('در صورت خالی بودن از روی عنوان ساخته می‌شود'),
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

                Tables\Columns\TextColumn::make('slug')
                    ->label('نامک')
                    ->searchable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('فعال')
                    ->boolean(),

                Tables\Columns\TextColumn::make('sort')
                    ->label('ترتیب')
                    ->sortable(),

                Tables\Columns\TextColumn::make('questions_count')
                    ->label('تعداد پرسش‌ها')
                    ->counts('questions'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاریخ ایجاد')
                    ->jalaliDateTime('H:i Y/m/d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('فعال'),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuestionCategories::route('/'),
            'create' => Pages\CreateQuestionCategory::route('/create'),
            'edit' => Pages\EditQuestionCategory::route('/{record}/edit'),
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
