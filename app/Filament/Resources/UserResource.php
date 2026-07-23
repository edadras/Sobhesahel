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
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\HtmlString;

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
                    ->label('گروه‌ها/نقش‌ها')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->required()
                    ->preload()
                    ->searchable()
                    ->helperText('یک کاربر می‌تواند هم‌زمان عضو چند گروه/نقش باشد.'),
                Forms\Components\FileUpload::make('avatar_url')->avatar()->label('آواتار'),
                Forms\Components\Toggle::make('is_active')
                    ->label('کاربر فعال')
                    ->default(true)
                    ->helperText('کاربر غیرفعال امکان ورود به پنل مدیریت را ندارد؛ محتوای منتشرشدهٔ او در سایت باقی می‌ماند.')
                    ->disabled(fn ($record) => $record !== null && $record->id === auth()->id()),
                Forms\Components\Select::make('locale')
                    ->label('زبان پنل')
                    ->options(User::localeOptions())
                    ->native(false)
                    ->placeholder('پیش‌فرض سیستم')
                    ->helperText('زبان نمایش پنل برای این کاربر.'),
                Forms\Components\Select::make('timezone')
                    ->label('منطقه زمانی')
                    ->options(User::timezoneOptions())
                    ->searchable()
                    ->default('Asia/Tehran')
                    ->placeholder('پیش‌فرض سیستم')
                    ->helperText('ساعت‌ها بر اساس این منطقه زمانی برای کاربر نمایش داده می‌شوند.'),
                Forms\Components\Section::make('موقعیت روی نقشه')
                    ->description('در صورت نیاز، مختصات جغرافیایی کاربر را وارد کنید تا پیش‌نمایش نقشه نمایش داده شود.')
                    ->collapsible()
                    ->collapsed(fn ($record) => $record === null || $record->latitude === null)
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('latitude')
                            ->label('عرض جغرافیایی (Latitude)')
                            ->numeric()
                            ->minValue(-90)
                            ->maxValue(90)
                            ->live(onBlur: true)
                            ->helperText('عددی بین ۹۰- تا ۹۰؛ مثال: 27.1832 برای بندرعباس.'),
                        Forms\Components\TextInput::make('longitude')
                            ->label('طول جغرافیایی (Longitude)')
                            ->numeric()
                            ->minValue(-180)
                            ->maxValue(180)
                            ->live(onBlur: true)
                            ->helperText('عددی بین ۱۸۰- تا ۱۸۰؛ مثال: 56.2666 برای بندرعباس.'),
                        Forms\Components\Placeholder::make('map_preview')
                            ->label('پیش‌نمایش نقشه')
                            ->columnSpanFull()
                            ->visible(fn (Forms\Get $get) => is_numeric($get('latitude')) && is_numeric($get('longitude')))
                            ->content(function (Forms\Get $get): ?HtmlString {
                                $lat = (float) $get('latitude');
                                $lon = (float) $get('longitude');

                                if ($lat < -90 || $lat > 90 || $lon < -180 || $lon > 180) {
                                    return null;
                                }

                                $bbox = implode(',', [$lon - 0.01, $lat - 0.005, $lon + 0.01, $lat + 0.005]);
                                $embed = 'https://www.openstreetmap.org/export/embed.html?bbox=' . rawurlencode($bbox)
                                    . '&layer=mapnik&marker=' . rawurlencode($lat . ',' . $lon);
                                $link = 'https://www.openstreetmap.org/?mlat=' . $lat . '&mlon=' . $lon
                                    . '#map=15/' . $lat . '/' . $lon;

                                return new HtmlString(
                                    '<iframe src="' . e($embed) . '" loading="lazy" referrerpolicy="no-referrer"'
                                    . ' style="width:100%;height:240px;border:0;border-radius:0.5rem;"></iframe>'
                                    . '<div style="margin-top:0.5rem;"><a href="' . e($link) . '" target="_blank" rel="noopener noreferrer"'
                                    . ' style="font-size:0.85rem;text-decoration:underline;">مشاهده در OpenStreetMap</a></div>'
                                );
                            }),
                    ]),
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
                Tables\Columns\TextColumn::make('mobile')
                    ->searchable()
                    ->label('موبایل')
                    ->toggleable(),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('فعال')
                    ->sortable()
                    ->disabled(fn ($record) => $record->id === auth()->id())
                    ->tooltip('کاربر غیرفعال امکان ورود به پنل را ندارد.'),
                Tables\Columns\TextColumn::make('last_activity_at')
                    ->label('آخرین فعالیت')
                    ->state(fn ($record) => $record->last_activity_at ? Carbon::parse($record->last_activity_at) : null)
                    ->jalaliDateTime('H:i Y/m/d')
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable()
                    ->visible(fn () => rescue(fn () => Schema::hasTable('user_activities'), false, false)),
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
                    ->label('گروه‌ها/نقش‌ها')
                    ->sortable()
                    ->toggleable()
                    ->separator(', ') // Display multiple roles separated by a comma
                    ->searchable()
                    ->badge(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('وضعیت کاربر')
                    ->trueLabel('فقط کاربران فعال')
                    ->falseLabel('فقط کاربران غیرفعال')
                    ->placeholder('همه کاربران'),
                Tables\Filters\SelectFilter::make('roles')
                    ->label('گروه/نقش')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload(),
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
            ->query(static::getTableQuery());
    }

    /**
     * Base table query. When the user_activities table (filament-user-activity
     * plugin) exists, a "last_activity_at" subselect is added so the list can
     * show each operator's most recent panel activity without N+1 queries.
     */
    protected static function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = User::query()->where('email', '!=', 'admin@develogist.com');

        if (rescue(fn () => Schema::hasTable('user_activities'), false, false)) {
            $query->addSelect([
                'last_activity_at' => DB::table('user_activities')
                    ->select('created_at')
                    ->whereColumn('user_activities.user_id', 'users.id')
                    ->orderByDesc('created_at')
                    ->limit(1),
            ]);
        }

        return $query;
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
                            TextEntry::make('roles.name')->label('گروه‌ها/نقش‌ها')->badge(),
                            TextEntry::make('email')->label('ایمیل'),
                            TextEntry::make('mobile')->label('موبایل')->placeholder('—'),
                            TextEntry::make('is_active')
                                ->label('وضعیت')
                                ->badge()
                                ->formatStateUsing(fn ($state) => $state === false ? 'غیرفعال' : 'فعال')
                                ->color(fn ($state) => $state === false ? 'danger' : 'success'),
                            TextEntry::make('locale')
                                ->label('زبان پنل')
                                ->formatStateUsing(fn ($state) => User::localeOptions()[$state] ?? $state)
                                ->placeholder('پیش‌فرض سیستم'),
                            TextEntry::make('timezone')
                                ->label('منطقه زمانی')
                                ->formatStateUsing(fn ($state) => User::timezoneOptions()[$state] ?? $state)
                                ->placeholder('پیش‌فرض سیستم'),
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
