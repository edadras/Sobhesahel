<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HomeBoxResource\Pages;
use App\Models\Category;
use App\Models\HomeBox;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class HomeBoxResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = HomeBox::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $label = 'باکس صفحه اصلی';

    protected static ?string $navigationGroup = 'محتوا';

    protected static bool $hasTitleCaseModelLabel = false;

    public static function getPluralModelLabel(): string
    {
        return 'باکس‌های صفحه اصلی';
    }

    public static function getPluralLabel(): ?string
    {
        return 'باکس‌های صفحه اصلی';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Placeholder::make('description')
                    ->label('راهنما')
                    ->content('تا زمانی که هیچ باکس فعالی تعریف نشده باشد، صفحه اصلی با چیدمان پیش‌فرض نمایش داده می‌شود. به محض تعریف اولین باکس فعال، چیدمان صفحه اصلی به طور کامل با باکس‌های تعریف‌شده (به ترتیب مشخص‌شده) جایگزین می‌شود.')
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('title')
                    ->required()
                    ->label('عنوان باکس')
                    ->helperText('برای باکس‌های عریض، ویدئو و عکس به عنوان تیتر باکس در سایت نمایش داده می‌شود. در ردیف دسته‌بندی، عنوان هر ستون از نام خود دسته گرفته می‌شود.'),

                Forms\Components\Select::make('box_type')
                    ->label('نوع باکس')
                    ->options(HomeBox::TYPES)
                    ->default('category_row')
                    ->required()
                    ->reactive(),

                Forms\Components\Select::make('category_ids')
                    ->label('دسته‌بندی‌ها')
                    ->multiple()
                    ->options(fn () => Category::orderBy('title')->pluck('title', 'id')->toArray())
                    ->searchable()
                    ->helperText('در ردیف دسته‌بندی حداکثر ۳ دسته (هر دسته یک ستون) و در باکس عریض فقط اولین دسته استفاده می‌شود.')
                    ->visible(fn (Forms\Get $get) => in_array($get('box_type'), ['category_row', 'wide_category']))
                    ->required(fn (Forms\Get $get) => in_array($get('box_type'), ['category_row', 'wide_category'])),

                Forms\Components\Select::make('content_type')
                    ->label('نوع محتوا')
                    ->options(HomeBox::CONTENT_TYPES)
                    ->default('news')
                    ->helperText('فیلتر دسته‌بندی فقط برای نوع «خبر» و «ترکیبی» اعمال می‌شود؛ سایر انواع، آخرین مطالب همان نوع را نمایش می‌دهند.')
                    ->visible(fn (Forms\Get $get) => in_array($get('box_type'), ['category_row', 'wide_category'])),

                Forms\Components\TextInput::make('items_count')
                    ->label('تعداد مطالب')
                    ->numeric()
                    ->minValue(1)
                    ->default(6)
                    ->helperText('در ردیف دسته‌بندی، تعداد مطالب هر ستون است.'),

                Forms\Components\TextInput::make('lead_chars')
                    ->label('تعداد کاراکتر لید')
                    ->numeric()
                    ->minValue(0)
                    ->nullable()
                    ->helperText('در صورت خالی بودن، لید بدون کوتاه‌سازی نمایش داده می‌شود.'),

                Forms\Components\TextInput::make('time_range_days')
                    ->label('بازه زمانی (روز)')
                    ->numeric()
                    ->minValue(1)
                    ->nullable()
                    ->helperText('فقط مطالب منتشرشده در این تعداد روز اخیر نمایش داده می‌شوند. خالی یعنی بدون محدودیت.'),

                Forms\Components\TextInput::make('sort_order')
                    ->label('ترتیب')
                    ->numeric()
                    ->default(0),

                Forms\Components\Toggle::make('is_active')
                    ->label('آیا فعال است؟')
                    ->default(true),

                Forms\Components\KeyValue::make('settings')
                    ->label('تنظیمات تکمیلی')
                    ->keyLabel('کلید')
                    ->valueLabel('مقدار')
                    ->nullable()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('عنوان')
                    ->searchable(),

                Tables\Columns\TextColumn::make('box_type')
                    ->label('نوع باکس')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => HomeBox::TYPES[$state] ?? $state),

                Tables\Columns\TextColumn::make('content_type')
                    ->label('نوع محتوا')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => HomeBox::CONTENT_TYPES[$state] ?? $state),

                Tables\Columns\TextColumn::make('items_count')
                    ->label('تعداد مطالب'),

                Tables\Columns\TextColumn::make('time_range_days')
                    ->label('بازه زمانی (روز)')
                    ->placeholder('بدون محدودیت'),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('ترتیب')
                    ->sortable(),

                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('فعال'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('box_type')
                    ->label('نوع باکس')
                    ->options(HomeBox::TYPES),
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
            ->reorderable('sort_order')
            ->defaultSort('sort_order');
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
            'index' => Pages\ListHomeBoxes::route('/'),
            'create' => Pages\CreateHomeBox::route('/create'),
            'edit' => Pages\EditHomeBox::route('/{record}/edit'),
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
