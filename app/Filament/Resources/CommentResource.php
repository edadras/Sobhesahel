<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CommentResource\Pages;
use App\Filament\Resources\CommentResource\RelationManagers;
use App\Models\Comment;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CommentResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Comment::class;

    protected static ?string $navigationIcon = 'fas-comments';

    protected static ?string $label = 'دیدگاه';

    protected static ?string $navigationGroup = 'محتوا';

    protected static bool $hasTitleCaseModelLabel = false;

    public static function getNavigationBadge(): ?string
    {
        return Comment::where('status','pending')->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }

    public static function getPluralModelLabel(): string
    {
        return 'دیدگاه ها';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('اطلاعات دیدگاه')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('نام کاربر')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('email')
                            ->label('ایمیل کاربر')
                            ->email()
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('comment')
                            ->label('متن دیدگاه')
                            ->required()
                            ->rows(4),

                        Forms\Components\Select::make('status')
                            ->label('وضعیت دیدگاه')
                            ->options([
                                'pending' => 'در انتظار بررسی',
                                'verified' => 'تأیید شده',
                                'rejected' => 'رد شده',
                            ])
                            ->required()
                            ->default('pending')
                            ->native(false),
                    ]),

                Forms\Components\Section::make('پست مرتبط')
                    ->schema([
                        Forms\Components\Select::make('news_id')
                            ->label('انتخاب خبر')
                            ->relationship('news', 'title')
                            ->searchable()
                            ->disabled()
                            ->hidden(fn ($record) => !is_null($record->note_id)), // Hide if it's a note

                        Forms\Components\Select::make('note_id')
                            ->label('انتخاب یادداشت')
                            ->relationship('note', 'title')
                            ->searchable()
                            ->disabled()
                            ->hidden(fn ($record) => !is_null($record->news_id)), // Hide if it's news
                    ]),

                Forms\Components\Section::make('اطلاعات سیستمی')
                    ->schema([
                        Forms\Components\TextInput::make('user_id')
                            ->label('شناسه کاربر')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(),

                        Forms\Components\TextInput::make('reply_to')
                            ->label('شناسه پاسخ')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(),

                        Forms\Components\TextInput::make('lang_id')
                            ->label('شناسه زبان')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(),

                        Forms\Components\DateTimePicker::make('created_at')
                            ->label('تاریخ ایجاد')
                            ->jalali()
                            ->disabled(),

                        Forms\Components\DateTimePicker::make('updated_at')
                            ->label('تاریخ بروزرسانی')
                            ->jalali()
                            ->disabled(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('news_id')
                    ->label('پست مرتبط')
                    ->formatStateUsing(fn ($record) =>
                    $record->news_id
                        ? ($record->news?->title ?? 'بدون عنوان')
                        : ($record->note?->title ?? 'بدون عنوان')
                    )
                    ->limit(10)
                    ->sortable()
                    ->toggleable()
                    ->url(fn ($record) =>
                    $record->news_id
                        ? NewsResource::getUrl('edit',[$record->news_id])
                        : ($record->note_id ? NoteResource::getUrl('edit',[$record->note_id]) : null)
                        , true)
                    ->color(fn ($record) => $record->news_id ? 'blue' : ($record->note_id ? 'green' : 'gray')),
                Tables\Columns\TextColumn::make('name')->label('نام کاربر'),
                Tables\Columns\TextColumn::make('email')->label('ایمیل کاربر'),
                Tables\Columns\TextColumn::make('comment')
                    ->label('متن دیدگاه')
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->comment)
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->label('وضعیت')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'pending' => 'در انتظار بررسی',
                        'verified' => 'تأیید شده',
                        'rejected' => 'رد شده',
                        default => 'نامشخص',
                    })
                    ->color(fn ($state) => match ($state) {
                        'pending' => 'warning',
                        'verified' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
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
                Tables\Filters\SelectFilter::make('status')
                    ->label('وضعیت')
                    ->options([
                        'pending' => 'در انتظار بررسی',
                        'verified' => 'تأیید شده',
                        'rejected' => 'رد شده',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])  ->defaultSort('id','DESC');
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
            'index' => Pages\ListComments::route('/'),
            'create' => Pages\CreateComment::route('/create'),
            'edit' => Pages\EditComment::route('/{record}/edit'),
        ];
    }

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'update',
            'delete',
        ];
    }
}
