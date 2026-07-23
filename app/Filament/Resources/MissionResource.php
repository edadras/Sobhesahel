<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MissionResource\Pages;
use App\Models\Mission;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * مدیریت ماموریت‌های باشگاه اعضا (روزانه/هفتگی/یک‌باره).
 */
class MissionResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Mission::class;

    protected static ?string $navigationIcon = 'fas-bullseye';

    protected static ?string $label = 'ماموریت';

    protected static ?string $navigationGroup = 'باشگاه اعضا';

    protected static ?int $navigationSort = 2;

    protected static bool $hasTitleCaseModelLabel = false;

    /**
     * رویدادهایی که می‌توانند پیشرفت ماموریت را جلو ببرند (هم‌نام کد قوانین امتیاز).
     */
    public static function eventCodeOptions(): array
    {
        return [
            'daily_login' => 'ورود روزانه',
            'comment_approved' => 'نظر تأییدشده',
            'poll_vote' => 'شرکت در نظرسنجی',
            'rating' => 'امتیازدهی به محتوا',
            'purchase' => 'خرید از فروشگاه',
            'subscription_purchase' => 'خرید یا تمدید اشتراک',
            'wheel' => 'چرخاندن چرخ شانس',
            'archive_unlock' => 'دریافت شماره آرشیو',
        ];
    }

    public static function getPluralModelLabel(): string
    {
        return 'ماموریت‌ها';
    }

    public static function getPluralLabel(): ?string
    {
        return 'ماموریت‌ها';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->label('کد یکتا')
                    ->helperText('شناسه انگلیسی یکتا؛ مثلاً daily_comment یا weekly_poll')
                    ->required()
                    ->maxLength(100)
                    ->unique(ignoreRecord: true),

                Forms\Components\TextInput::make('title')
                    ->label('عنوان')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Textarea::make('description')
                    ->label('توضیح')
                    ->rows(2)
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('points')
                    ->label('امتیاز جایزه')
                    ->helperText('امتیازی که پس از تکمیل ماموریت به عضو داده می‌شود')
                    ->numeric()
                    ->required()
                    ->default(0)
                    ->minValue(0),

                Forms\Components\Select::make('period')
                    ->label('دوره')
                    ->options(Mission::PERIODS)
                    ->default('daily')
                    ->required(),

                Forms\Components\TextInput::make('goal_count')
                    ->label('تعداد لازم برای تکمیل')
                    ->helperText('مثلاً «۳ نظر در روز» یعنی رویداد نظر تأییدشده و تعداد ۳')
                    ->numeric()
                    ->minValue(1)
                    ->default(1)
                    ->required(),

                Forms\Components\Select::make('event_code')
                    ->label('رویداد پیشرفت')
                    ->helperText('کدام اقدام عضو پیشرفت این ماموریت را جلو می‌برد')
                    ->options(static::eventCodeOptions())
                    ->required(),

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

                Tables\Columns\TextColumn::make('period')
                    ->label('دوره')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => Mission::PERIODS[$state] ?? $state),

                Tables\Columns\TextColumn::make('event_code')
                    ->label('رویداد')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn (string $state) => static::eventCodeOptions()[$state] ?? $state),

                Tables\Columns\TextColumn::make('goal_count')
                    ->label('هدف'),

                Tables\Columns\TextColumn::make('points')
                    ->label('امتیاز')
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('completions_count')
                    ->label('تکمیل‌شده')
                    ->counts('completions'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('فعال')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('period')
                    ->label('دوره')
                    ->options(Mission::PERIODS),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('فعال'),
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
            ->reorderable('sort')
            ->defaultSort('sort');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMissions::route('/'),
            'create' => Pages\CreateMission::route('/create'),
            'edit' => Pages\EditMission::route('/{record}/edit'),
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
