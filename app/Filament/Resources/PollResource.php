<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PollResource\Pages;
use App\Filament\Resources\PollResource\RelationManagers;
use App\Models\Poll;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PollResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Poll::class;

    protected static ?string $navigationIcon = 'fas-square-poll-vertical';

    protected static ?string $label = 'نظرسنجی';

    protected static ?string $navigationGroup = 'سایر';

    protected static bool $hasTitleCaseModelLabel = false;

    public static function getPluralModelLabel(): string
    {
        return 'نظرسنجی';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(12)->schema([
                    Forms\Components\Grid::make()->schema([
                        Forms\Components\TextInput::make('question')->label('متن نظرسنجی')->columnSpanFull(),
                        Forms\Components\Repeater::make('options')->label('گزینه ها')
                            ->relationship('options')
                            ->schema([
                                Forms\Components\TextInput::make('option_text')->label('متن')
                            ])->reorderable()
                            ->orderColumn('sort_order')->columnSpanFull()
                    ])->columnSpan(8),
                    Forms\Components\Grid::make()->schema([
                        Forms\Components\Section::make('تنظیمات')->schema([
                            Forms\Components\Toggle::make('is_active')->label('آیا فعال است ؟'),
                            Forms\Components\Toggle::make('login_required')->label('نیازمند ورود'),
                            Forms\Components\TextInput::make('poll_category')
                                ->label('دسته‌بندی نظرسنجی')
                                ->datalist(fn () => array_values(Poll::categories()))
                                ->helperText('از دسته‌های موجود انتخاب کنید یا دسته جدید بنویسید'),
                        ]),
                        Forms\Components\Section::make('زمان‌بندی انتشار')->schema([
                            Forms\Components\DateTimePicker::make('starts_at')
                                ->jalali()
                                ->label('زمان شروع')
                                ->helperText('خالی = بدون محدودیت'),
                            Forms\Components\DateTimePicker::make('ends_at')
                                ->jalali()
                                ->label('زمان پایان')
                                ->after('starts_at')
                                ->helperText('خالی = بدون محدودیت'),
                        ])->description('نظرسنجی فقط در این بازه قابل رأی دادن است'),
                    ])->columnSpan(4),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('question')->limit(50)->label('متن نظرسنجی'),
                Tables\Columns\TextColumn::make('poll_category')
                    ->label('دسته‌بندی')
                    ->badge()
                    ->placeholder('بدون دسته'),
                Tables\Columns\IconColumn::make('is_active')->boolean()->label('آیا فعال است'),
                Tables\Columns\TextColumn::make('ends_at')
                    ->label('زمان پایان')
                    ->jalaliDateTime('H:i Y/m/d')
                    ->placeholder('بدون محدودیت')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاریخ ایجاد')
                    ->jalaliDateTime('H:i Y/m/d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('تاریخ بروزرسانی')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->jalaliDateTime('H:i Y/m/d'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('poll_category')
                    ->label('دسته‌بندی')
                    ->options(fn () => Poll::categories()),
            ])
            ->actions([
                Tables\Actions\Action::make('results')
                    ->label('نتایج')
                    ->icon('heroicon-o-chart-bar')
                    ->color('info')
                    ->modalHeading('نتایج نظرسنجی')
                    ->modalContent(fn (Poll $record) => view('filament.poll-results', ['poll' => $record]))
                    ->modalSubmitAction(false)
                    ->modalCancelAction(fn ($action) => $action->label('بستن')),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListPolls::route('/'),
            'create' => Pages\CreatePoll::route('/create'),
            'view' => Pages\ViewPoll::route('/{record}'),
            'edit' => Pages\EditPoll::route('/{record}/edit'),
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
