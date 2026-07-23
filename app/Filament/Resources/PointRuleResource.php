<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PointRuleResource\Pages;
use App\Models\PointRule;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * مدیریت قوانین امتیاز باشگاه اعضا — تصمیم نهایی ۵-۳ نقشه راه:
 * نرخ هر رویداد کاملاً از همین‌جا توسط مدیر قابل‌تعریف است.
 */
class PointRuleResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = PointRule::class;

    protected static ?string $navigationIcon = 'fas-scale-balanced';

    protected static ?string $label = 'قانون امتیاز';

    protected static ?string $navigationGroup = 'باشگاه اعضا';

    protected static ?int $navigationSort = 1;

    protected static bool $hasTitleCaseModelLabel = false;

    /**
     * مجموعه قوانین استاندارد که با دکمه «ایجاد قوانین پیش‌فرض» ساخته می‌شود.
     * مقادیر پیشنهادی‌اند و مدیر می‌تواند بعداً ویرایش/صفر کند.
     *
     * @return array<int, array{code: string, title: string, points: int, daily_cap: ?int}>
     */
    public static function defaultRules(): array
    {
        return [
            ['code' => 'daily_login', 'title' => 'ورود روزانه', 'points' => 10, 'daily_cap' => 1],
            ['code' => 'comment_approved', 'title' => 'نظر تأییدشده', 'points' => 20, 'daily_cap' => 5],
            ['code' => 'poll_vote', 'title' => 'شرکت در نظرسنجی', 'points' => 10, 'daily_cap' => 3],
            ['code' => 'rating', 'title' => 'امتیازدهی به محتوا', 'points' => 5, 'daily_cap' => 5],
            ['code' => 'purchase', 'title' => 'خرید از فروشگاه', 'points' => 50, 'daily_cap' => null],
            ['code' => 'subscription_purchase', 'title' => 'خرید یا تمدید اشتراک', 'points' => 200, 'daily_cap' => null],
            ['code' => 'streak_bonus', 'title' => 'جایزه زنجیره ورود', 'points' => 50, 'daily_cap' => null],
            ['code' => 'mission', 'title' => 'تکمیل ماموریت (مقدار از خود ماموریت)', 'points' => 0, 'daily_cap' => null],
            ['code' => 'wheel', 'title' => 'جایزه چرخ شانس (مقدار از خود جایزه)', 'points' => 0, 'daily_cap' => null],
            ['code' => 'wheel_cost', 'title' => 'هزینه چرخش اضافه چرخ شانس', 'points' => -100, 'daily_cap' => null],
            ['code' => 'archive_unlock', 'title' => 'دریافت تک‌شماره آرشیو با امتیاز', 'points' => -50, 'daily_cap' => null],
            ['code' => 'manual', 'title' => 'تغییر دستی توسط مدیر', 'points' => 0, 'daily_cap' => null],
        ];
    }

    public static function getPluralModelLabel(): string
    {
        return 'قوانین امتیاز';
    }

    public static function getPluralLabel(): ?string
    {
        return 'قوانین امتیاز';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->label('کد رویداد')
                    ->helperText('کد یکتای انگلیسی رویداد؛ مثلاً daily_login یا comment_approved. موتور امتیاز با همین کد قانون را پیدا می‌کند.')
                    ->required()
                    ->maxLength(100)
                    ->unique(ignoreRecord: true),

                Forms\Components\TextInput::make('title')
                    ->label('عنوان')
                    ->helperText('عنوان فارسی که در تاریخچه امتیاز عضو نمایش داده می‌شود')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('points')
                    ->label('امتیاز')
                    ->helperText('عدد منفی یعنی خرج امتیاز (مانند هزینه دریافت آرشیو). صفر یعنی غیرمؤثر.')
                    ->numeric()
                    ->required()
                    ->default(0),

                Forms\Components\TextInput::make('daily_cap')
                    ->label('سقف دفعات در روز')
                    ->helperText('حداکثر دفعاتی که هر عضو در یک روز از این قانون امتیاز می‌گیرد. خالی = بدون سقف.')
                    ->numeric()
                    ->minValue(1)
                    ->nullable(),

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

                Tables\Columns\TextColumn::make('code')
                    ->label('کد')
                    ->badge()
                    ->searchable(),

                Tables\Columns\TextColumn::make('points')
                    ->label('امتیاز')
                    ->badge()
                    ->color(fn ($state) => (int) $state >= 0 ? 'success' : 'danger')
                    ->sortable(),

                Tables\Columns\TextColumn::make('daily_cap')
                    ->label('سقف روزانه')
                    ->placeholder('بدون سقف'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('فعال')
                    ->boolean(),
            ])
            ->filters([
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
            'index' => Pages\ListPointRules::route('/'),
            'create' => Pages\CreatePointRule::route('/create'),
            'edit' => Pages\EditPointRule::route('/{record}/edit'),
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
