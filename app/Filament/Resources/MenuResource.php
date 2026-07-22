<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MenuResource\Pages;
use App\Models\Menu;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MenuResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Menu::class;

    protected static ?string $navigationIcon = 'fas-bars';

    protected static ?string $label = 'منو';

    protected static ?string $navigationGroup = 'محتوا';

    protected static bool $hasTitleCaseModelLabel = false;

    public static function getPluralModelLabel(): string
    {
        return 'منوها';
    }

    public static function getPluralLabel(): ?string
    {
        return 'منوها';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->label('عنوان'),

                Forms\Components\Select::make('location')
                    ->label('جایگاه منو')
                    ->options(Menu::LOCATIONS)
                    ->default('header')
                    ->required()
                    ->reactive(),

                Forms\Components\Select::make('type')
                    ->label('نوع آیتم')
                    ->options(Menu::TYPES)
                    ->default('link')
                    ->required()
                    ->reactive(),

                Forms\Components\Select::make('parent_id')
                    ->label('آیتم مادر')
                    ->options(fn (Forms\Get $get, ?Menu $record) => self::getParentOptions($get('location'), $record?->id))
                    ->searchable()
                    ->nullable(),

                Forms\Components\TextInput::make('url')
                    ->label('آدرس (URL)')
                    ->helperText('آدرس کامل یا مسیر نسبی مانند /news')
                    ->hidden(fn (Forms\Get $get) => $get('type') === 'separator')
                    ->required(fn (Forms\Get $get) => $get('type') !== 'separator'),

                Forms\Components\Select::make('target')
                    ->label('نحوه باز شدن لینک')
                    ->options(Menu::TARGETS)
                    ->default('_self')
                    ->hidden(fn (Forms\Get $get) => $get('type') === 'separator'),

                Forms\Components\FileUpload::make('image')
                    ->label('تصویر اختصاصی')
                    ->image()
                    ->nullable(),

                Forms\Components\TextInput::make('order')
                    ->label('ترتیب')
                    ->numeric()
                    ->default(0),

                Forms\Components\Toggle::make('is_active')
                    ->label('آیا فعال است؟')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('عنوان')
                    ->formatStateUsing(fn (string $state, Menu $record) => str_repeat('— ', self::getDepth($record)) . $state)
                    ->searchable(),

                Tables\Columns\TextColumn::make('location')
                    ->label('جایگاه')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => Menu::LOCATIONS[$state] ?? $state),

                Tables\Columns\TextColumn::make('type')
                    ->label('نوع')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => Menu::TYPES[$state] ?? $state),

                Tables\Columns\TextColumn::make('parent.title')
                    ->label('آیتم مادر')
                    ->sortable(),

                Tables\Columns\TextColumn::make('url')
                    ->label('آدرس')
                    ->limit(40),

                Tables\Columns\TextColumn::make('order')
                    ->label('ترتیب')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('فعال')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('location')
                    ->label('جایگاه')
                    ->options(Menu::LOCATIONS),
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
            ->reorderable('order')
            ->defaultSort('order');
    }

    /**
     * Build the parent select options as an indented tree, limited to one location.
     */
    public static function getParentOptions(?string $location, ?int $exceptId = null): array
    {
        $query = Menu::query()->whereNull('parent_id')->orderBy('order');

        if ($location) {
            $query->where('location', $location);
        }

        $options = [];

        foreach ($query->with('childrenRecursive')->get() as $root) {
            self::appendOption($options, $root, 0, $exceptId);
        }

        return $options;
    }

    protected static function appendOption(array &$options, Menu $item, int $depth, ?int $exceptId): void
    {
        if ($exceptId !== null && $item->id === $exceptId) {
            return;
        }

        $options[$item->id] = str_repeat('— ', $depth) . $item->title;

        foreach ($item->childrenRecursive as $child) {
            self::appendOption($options, $child, $depth + 1, $exceptId);
        }
    }

    public static function getDepth(Menu $record): int
    {
        $depth = 0;
        $current = $record;

        while ($current->parent_id !== null && $depth < 10) {
            $current = $current->parent;

            if (! $current) {
                break;
            }

            $depth++;
        }

        return $depth;
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
            'index' => Pages\ListMenus::route('/'),
            'create' => Pages\CreateMenu::route('/create'),
            'edit' => Pages\EditMenu::route('/{record}/edit'),
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
