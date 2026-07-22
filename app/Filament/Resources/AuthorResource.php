<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AuthorResource\Pages;
use App\Filament\Resources\AuthorResource\RelationManagers;
use App\Infolists\Components\UserSummaryInfolist;
use App\Models\Author;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Faker\Provider\Text;
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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AuthorResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Author::class;

    protected static ?string $navigationIcon = 'fas-user';

    protected static ?string $label = 'نویسنده';

    protected static ?string $navigationGroup = 'کاربران';

    protected static bool $hasTitleCaseModelLabel = false;

    public static function getPluralModelLabel(): string
    {
        return 'نویسندگان';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')->label('نام نویسنده')->required(),
                Forms\Components\TextInput::make('nik_name')->label('لقب')->helperText('مانند : سردبیر روزنامه'),
                Forms\Components\Select::make('type')
                    ->label('نوع فعالیت')
                    ->options(Author::TYPES)
                    ->native(false)
                    ->nullable(),
                Forms\Components\Textarea::make('bio')->label('بیوگرافی'),
                Forms\Components\FileUpload::make('avatar')->avatar()->label('آواتار'),
                Forms\Components\RichEditor::make('resume')
                    ->label('رزومه')
                    ->columnSpanFull(),
                Forms\Components\Repeater::make('work_history')
                    ->label('سوابق کاری')
                    ->schema([
                        Forms\Components\TextInput::make('title')->label('عنوان شغلی')->required(),
                        Forms\Components\TextInput::make('organization')->label('سازمان / رسانه'),
                        Forms\Components\TextInput::make('from')->label('از سال'),
                        Forms\Components\TextInput::make('to')->label('تا سال')->helperText('در صورت ادامه داشتن، خالی بگذارید'),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->defaultItems(0)
                    ->addActionLabel('افزودن سابقه کاری')
                    ->columnSpanFull(),
                Forms\Components\Repeater::make('social_links')
                    ->label('شبکه‌های اجتماعی')
                    ->schema([
                        Forms\Components\Select::make('network')
                            ->label('شبکه')
                            ->options(Author::SOCIAL_NETWORKS)
                            ->native(false)
                            ->required(),
                        Forms\Components\TextInput::make('url')->label('آدرس (لینک)')->url()->required(),
                    ])
                    ->columns(2)
                    ->defaultItems(0)
                    ->addActionLabel('افزودن شبکه اجتماعی')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('email')
                    ->label('ایمیل')
                    ->email()
                    ->helperText('فقط برای نمایش در پنل مدیریت'),
                Forms\Components\TextInput::make('phone')
                    ->label('شماره تماس')
                    ->tel()
                    ->helperText('فقط برای نمایش در پنل مدیریت'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('avatar')->circular()->label('آواتار'),
                Tables\Columns\TextColumn::make('name')->label('نام')->searchable(),
                Tables\Columns\TextColumn::make('nik_name')->label('لقب')->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('نوع فعالیت')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Author::TYPES[$state] ?? $state),
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
                Tables\Actions\ViewAction::make()
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                UserSummaryInfolist::make('')->columnSpanFull()->getStateUsing(fn($record) => $record),
                Section::make('اطلاعات نویسنده')
                    ->schema([
                        Grid::make(3)->schema([
                            ImageEntry::make('avatar')->label('')->circular()->hidden(fn($state) => $state == null)->width(50)->height(50),
                            TextEntry::make('name')->label('نام'),
                            TextEntry::make('nik_name')->label('لقب'),
                        ]),
                        Grid::make(3)->schema([
                            TextEntry::make('type')
                                ->label('نوع فعالیت')
                                ->badge()
                                ->formatStateUsing(fn ($state) => Author::TYPES[$state] ?? $state)
                                ->hidden(fn ($state) => $state == null),
                            TextEntry::make('email')->label('ایمیل')->hidden(fn ($state) => $state == null),
                            TextEntry::make('phone')->label('شماره تماس')->hidden(fn ($state) => $state == null),
                        ])
                    ]),
            ]);
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAuthors::route('/'),
            'create' => Pages\CreateAuthor::route('/create'),
            'edit' => Pages\EditAuthor::route('/{record}/edit'),
            'view' => Pages\ViewAuthor::route('/{record}'),
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
