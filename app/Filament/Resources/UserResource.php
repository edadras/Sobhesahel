<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Infolists\Components\UserSummaryInfolist;
use App\Models\User;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'fas-id-card';

    protected static ?string $label = 'اوپراتور';

    protected static ?string $navigationGroup = 'کاربران';

    protected static bool $hasTitleCaseModelLabel = false;

    public static function getPluralModelLabel(): string
    {
        return 'اوپراتور ها';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->label('نام و نام خانوادگی'),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->label('ایمیل')
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('mobile')
                    ->numeric()
                    ->label('موبایل')
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('password')
                    ->label('رمز عبور')
                    ->password()
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\CreateRecord)
                    ->maxLength(200),
                Forms\Components\Select::make('roles')
                    ->label('نقش کاربری')
                    ->relationship('roles', 'name')
                    ->required()
                    ->preload()
                    ->searchable(),
                Forms\Components\FileUpload::make('avatar_url')->avatar()->label('آواتار')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('avatar_url')
                    ->searchable()
                    ->circular()
                    ->label('آواتار'),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->label('نام'),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->label('ایمیل'),
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

                Tables\Columns\TextColumn::make('roles.name')
                    ->label('نقش کاربری')
                    ->sortable()
                    ->toggleable()
                    ->separator(', ') // Display multiple roles separated by a comma
                    ->searchable()
                    ->badge(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn ($record) => !$record->hasRole('Dev')),
                Tables\Actions\ViewAction::make()
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->query(User::where('email','!=','admin@develogist.com'));
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\NewsRelationManager::class,
            RelationManagers\NotesRelationManager::class,
            RelationManagers\VideosRelationManager::class,
            RelationManagers\GalleriesRelationManager::class
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                UserSummaryInfolist::make('')->columnSpanFull()->getStateUsing(fn($record) => $record),
                Section::make('اطلاعات اوپراتور')
                    ->schema([
                        Grid::make(4)->schema([
                            ImageEntry::make('avatar_url')->label('')->circular()->hidden(fn($state) => $state == null)->width(50)->height(50),
                            TextEntry::make('name')->label('نام'),
                            TextEntry::make('roles.name')->label('نقش کاربری'),
                            TextEntry::make('email')->label('ایمیل'),
                        ])
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
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
