<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CommentResource\Pages;
use App\Filament\Resources\CommentResource\RelationManagers;
use App\Models\Comment;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
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
                        Forms\Components\TextInput::make('ip')
                            ->label('آدرس IP')
                            ->disabled(),

                        Forms\Components\TextInput::make('user_agent')
                            ->label('مرورگر کاربر (User Agent)')
                            ->disabled(),

                        Forms\Components\TextInput::make('spam_reason')
                            ->label('دلیل اسپم')
                            ->disabled(),

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
                Tables\Columns\TextColumn::make('ip')
                    ->label('آدرس IP')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('spam_reason')
                    ->label('دلیل اسپم')
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->spam_reason)
                    ->toggleable(isToggledHiddenByDefault: true),

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

                Tables\Filters\Filter::make('ip')
                    ->label('نظرات همین IP')
                    ->form([
                        Forms\Components\TextInput::make('ip')
                            ->label('آدرس IP'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['ip'] ?? null, fn (Builder $query, $ip) => $query->where('ip', $ip)))
                    ->indicateUsing(fn (array $data): ?string => ($data['ip'] ?? null)
                        ? 'نظرات IP: ' . $data['ip']
                        : null),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('تأیید')
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->visible(fn (Comment $record) => $record->status !== 'verified')
                    ->action(function (Comment $record) {
                        $record->update(['status' => 'verified']);

                        Notification::make()
                            ->title('دیدگاه تأیید شد')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('reject')
                    ->label('رد')
                    ->icon('heroicon-m-x-circle')
                    ->color('danger')
                    ->visible(fn (Comment $record) => $record->status !== 'rejected')
                    ->action(function (Comment $record) {
                        $record->update(['status' => 'rejected']);

                        Notification::make()
                            ->title('دیدگاه رد شد')
                            ->danger()
                            ->send();
                    }),

                Tables\Actions\Action::make('same_ip')
                    ->label('نظرات همین IP')
                    ->icon('heroicon-m-funnel')
                    ->color('gray')
                    ->visible(fn (Comment $record) => filled($record->ip))
                    ->url(fn (Comment $record): string => static::getUrl('index', [
                        'tableFilters' => ['ip' => ['ip' => $record->ip]],
                    ])),

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('approve')
                        ->label('تأیید گروهی')
                        ->icon('heroicon-m-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(function (Collection $records) {
                            $records->each->update(['status' => 'verified']);

                            Notification::make()
                                ->title('دیدگاه‌های انتخاب شده تأیید شدند')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\BulkAction::make('reject')
                        ->label('رد گروهی')
                        ->icon('heroicon-m-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(function (Collection $records) {
                            $records->each->update(['status' => 'rejected']);

                            Notification::make()
                                ->title('دیدگاه‌های انتخاب شده رد شدند')
                                ->danger()
                                ->send();
                        }),

                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])  ->defaultSort('id','DESC');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('دیدگاه')
                    ->schema([
                        Infolists\Components\TextEntry::make('name')
                            ->label('نام کاربر'),

                        Infolists\Components\TextEntry::make('email')
                            ->label('ایمیل کاربر'),

                        Infolists\Components\TextEntry::make('comment')
                            ->label('متن دیدگاه')
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('status')
                            ->label('وضعیت')
                            ->badge()
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

                        Infolists\Components\TextEntry::make('spam_reason')
                            ->label('دلیل اسپم')
                            ->placeholder('—'),
                    ])->columns(2),

                Infolists\Components\Section::make('اطلاعات فنی')
                    ->schema([
                        Infolists\Components\TextEntry::make('ip')
                            ->label('آدرس IP')
                            ->copyable()
                            ->placeholder('ثبت نشده'),

                        Infolists\Components\TextEntry::make('user_agent')
                            ->label('مرورگر کاربر (User Agent)')
                            ->placeholder('ثبت نشده')
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('created_at')
                            ->label('تاریخ ایجاد')
                            ->jalaliDateTime('H:i Y/m/d')
                            ->placeholder('—'),
                    ])->columns(2),
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
