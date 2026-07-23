<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MemberResource\Pages;
use App\Models\Member;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

/**
 * مدیریت اعضای سایت (باشگاه اعضای صبح ساحل — فاز ۱ ناحیه کاربران).
 *
 * Members are a SEPARATE table/guard from the editorial `users` (تصمیم ۵-۵);
 * this resource only manages the public membership records.
 */
class MemberResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Member::class;

    protected static ?string $navigationIcon = 'fas-id-card';

    protected static ?string $label = 'عضو';

    protected static ?string $navigationGroup = 'اعضا';

    protected static bool $hasTitleCaseModelLabel = false;

    public static function getPluralModelLabel(): string
    {
        return 'اعضا';
    }

    public static function getPluralLabel(): ?string
    {
        return 'اعضا';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('اطلاعات عضو')
                    ->schema([
                        Forms\Components\TextInput::make('mobile')
                            ->label('شماره موبایل')
                            ->helperText('قالب ۰۹xxxxxxxxx — شناسه ورود عضو')
                            ->required()
                            ->maxLength(20)
                            ->unique(ignoreRecord: true)
                            ->dehydrateStateUsing(fn (?string $state) => Member::normalizeMobile((string) $state))
                            ->rule('regex:/^0?9\d{9}$/')
                            ->validationMessages([
                                'required' => 'وارد کردن شماره موبایل الزامی است',
                                'unique' => 'این شماره موبایل قبلاً ثبت شده است',
                                'regex' => 'قالب شماره موبایل صحیح نیست (نمونه: 09123456789)',
                            ]),

                        Forms\Components\TextInput::make('email')
                            ->label('ایمیل')
                            ->email()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->validationMessages([
                                'unique' => 'این ایمیل قبلاً ثبت شده است',
                            ]),

                        Forms\Components\TextInput::make('first_name')
                            ->label('نام')
                            ->maxLength(100),

                        Forms\Components\TextInput::make('last_name')
                            ->label('نام خانوادگی')
                            ->maxLength(100),

                        Forms\Components\Select::make('city')
                            ->label('شهر (هرمزگان)')
                            ->options(fn () => array_combine(
                                (array) config('member.cities', []),
                                (array) config('member.cities', [])
                            ))
                            ->searchable(),

                        Forms\Components\Select::make('locale')
                            ->label('زبان')
                            ->options(fn () => \App\Models\Language::active()
                                ->pluck('native_name', 'code')
                                ->all())
                            ->default('fa'),

                        Forms\Components\Textarea::make('bio')
                            ->label('درباره من')
                            ->rows(3)
                            ->maxLength(1000)
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('فعال')
                            ->helperText('عضو غیرفعال امکان ورود به پنل اعضا را ندارد')
                            ->default(true),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('avatar')
                    ->label('آواتار')
                    ->getStateUsing(fn (Member $record) => $record->avatar
                        ? Storage::disk('public')->url($record->avatar)
                        : null)
                    ->circular()
                    ->defaultImageUrl('/asset/img/user05.png'),

                Tables\Columns\TextColumn::make('full_name')
                    ->label('نام و نام خانوادگی')
                    ->getStateUsing(fn (Member $record) => $record->fullName())
                    ->searchable(query: fn ($query, string $search) => $query
                        ->where(fn ($q) => $q
                            ->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%"))),

                Tables\Columns\TextColumn::make('mobile')
                    ->label('موبایل')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('ایمیل')
                    ->searchable()
                    ->placeholder('—')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('city')
                    ->label('شهر')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('فعال'),

                Tables\Columns\IconColumn::make('mobile_verified_at')
                    ->label('موبایل تأیید شده')
                    ->boolean()
                    ->getStateUsing(fn (Member $record) => $record->mobile_verified_at !== null),

                Tables\Columns\TextColumn::make('last_login_at')
                    ->label('آخرین ورود')
                    ->jalaliDateTime('H:i Y/m/d')
                    ->placeholder('—')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاریخ عضویت')
                    ->jalaliDate()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('وضعیت')
                    ->trueLabel('فعال')
                    ->falseLabel('غیرفعال')
                    ->placeholder('همه'),

                Tables\Filters\TernaryFilter::make('has_password')
                    ->label('رمز عبور')
                    ->trueLabel('دارد')
                    ->falseLabel('ندارد')
                    ->placeholder('همه')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('password'),
                        false: fn ($query) => $query->whereNull('password'),
                        blank: fn ($query) => $query,
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('اطلاعات عضو')
                    ->schema([
                        Infolists\Components\TextEntry::make('full_name')
                            ->label('نام و نام خانوادگی')
                            ->getStateUsing(fn (Member $record) => $record->fullName()),

                        Infolists\Components\TextEntry::make('mobile')
                            ->label('شماره موبایل')
                            ->copyable(),

                        Infolists\Components\TextEntry::make('email')
                            ->label('ایمیل')
                            ->placeholder('ثبت نشده'),

                        Infolists\Components\TextEntry::make('city')
                            ->label('شهر')
                            ->placeholder('ثبت نشده'),

                        Infolists\Components\TextEntry::make('birth_date')
                            ->label('تاریخ تولد')
                            ->jalaliDate()
                            ->placeholder('ثبت نشده'),

                        Infolists\Components\TextEntry::make('locale')
                            ->label('زبان'),

                        Infolists\Components\TextEntry::make('bio')
                            ->label('درباره من')
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ])->columns(3),

                Infolists\Components\Section::make('وضعیت حساب')
                    ->schema([
                        Infolists\Components\IconEntry::make('is_active')
                            ->label('فعال')
                            ->boolean(),

                        Infolists\Components\IconEntry::make('has_password')
                            ->label('رمز عبور دارد')
                            ->getStateUsing(fn (Member $record) => $record->hasPassword())
                            ->boolean(),

                        Infolists\Components\TextEntry::make('mobile_verified_at')
                            ->label('تأیید موبایل')
                            ->jalaliDateTime('H:i Y/m/d')
                            ->placeholder('تأیید نشده'),

                        Infolists\Components\TextEntry::make('last_login_at')
                            ->label('آخرین ورود')
                            ->jalaliDateTime('H:i Y/m/d')
                            ->placeholder('—'),

                        Infolists\Components\TextEntry::make('created_at')
                            ->label('تاریخ عضویت')
                            ->jalaliDateTime('H:i Y/m/d'),
                    ])->columns(3),
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
            'index' => Pages\ListMembers::route('/'),
            'create' => Pages\CreateMember::route('/create'),
            'view' => Pages\ViewMember::route('/{record}'),
            'edit' => Pages\EditMember::route('/{record}/edit'),
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
