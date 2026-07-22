<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuestionResource\Pages;
use App\Filament\Resources\QuestionResource\RelationManagers;
use App\Models\Question;
use App\Models\QuestionCategory;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class QuestionResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Question::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $label = 'پرسش';

    protected static ?string $navigationGroup = 'پرسش و پاسخ';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?int $navigationSort = 1;

    public static function getPluralModelLabel(): string
    {
        return 'پرسش‌ها';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(12)->schema([
                    Forms\Components\Grid::make()->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('عنوان پرسش')
                            ->columnSpanFull()
                            ->required(),
                        Forms\Components\Textarea::make('body')
                            ->label('متن پرسش')
                            ->rows(6)
                            ->columnSpanFull()
                            ->required(),
                        Forms\Components\TextInput::make('name')
                            ->label('نام پرسشگر')
                            ->required(),
                        Forms\Components\TextInput::make('email')
                            ->label('ایمیل (اختیاری)')
                            ->email(),
                        Forms\Components\TextInput::make('phone')
                            ->label('شماره تماس (اختیاری)'),
                        Forms\Components\FileUpload::make('attachment')
                            ->label('فایل ضمیمه')
                            ->disk('public')
                            ->directory('qa/attachments')
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                            ->maxSize(2048)
                            ->downloadable()
                            ->openable()
                            ->helperText('حداکثر ۲ مگابایت - PDF یا تصویر (JPG/PNG)'),
                    ])->columnSpan(8),
                    Forms\Components\Grid::make()->schema([
                        Forms\Components\Section::make('تنظیمات انتشار')->schema([
                            Forms\Components\Select::make('category_id')
                                ->options(QuestionCategory::pluck('title', 'id')->toArray())
                                ->label('دسته‌بندی')
                                ->searchable()
                                ->preload(),
                            Forms\Components\ToggleButtons::make('status')
                                ->options(Question::statusOptions())
                                ->icons([
                                    'pending' => 'heroicon-o-clock',
                                    'published' => 'heroicon-o-check-circle',
                                    'rejected' => 'heroicon-o-x-circle',
                                ])
                                ->colors([
                                    'pending' => 'warning',
                                    'published' => 'success',
                                    'rejected' => 'danger',
                                ])
                                ->label('وضعیت')
                                ->inline()
                                ->default('pending')
                                ->required(),
                            Forms\Components\Toggle::make('is_featured')
                                ->label('پرسش ویژه'),
                        ]),
                    ])->columnSpan(4),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('عنوان')
                    ->searchable()
                    ->limit(60)
                    ->description(fn ($record): string => $record->name ?? ''),

                Tables\Columns\TextColumn::make('category.title')
                    ->label('دسته‌بندی')
                    ->badge()
                    ->placeholder('بدون دسته‌بندی'),

                Tables\Columns\TextColumn::make('status')
                    ->label('وضعیت')->badge()
                    ->formatStateUsing(fn ($state) => Question::statusOptions()[$state] ?? 'نامشخص')
                    ->color(fn ($state) => match ($state) {
                        'pending' => 'warning',
                        'published' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('answers_count')
                    ->label('تعداد پاسخ')
                    ->counts('answers'),

                Tables\Columns\IconColumn::make('is_featured')
                    ->label('ویژه')
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_admin_created')
                    ->label('ثبت توسط مدیر')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('attachment')
                    ->label('ضمیمه')
                    ->icon(fn ($state) => filled($state) ? 'heroicon-o-paper-clip' : null),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاریخ ایجاد')
                    ->jalaliDateTime('H:i Y/m/d')
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('تاریخ بروزرسانی')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->jalaliDateTime('H:i Y/m/d'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('دسته‌بندی')
                    ->options(QuestionCategory::pluck('title', 'id')->toArray()),

                Tables\Filters\SelectFilter::make('status')
                    ->label('وضعیت')
                    ->options(Question::statusOptions()),

                Tables\Filters\TernaryFilter::make('expert_answered')
                    ->label('دارای پاسخ کارشناسی')
                    ->queries(
                        true: fn (Builder $query) => $query->whereHas('answers', fn (Builder $q) => $q->where('is_expert', true)),
                        false: fn (Builder $query) => $query->whereDoesntHave('answers', fn (Builder $q) => $q->where('is_expert', true)),
                    ),

                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('پرسش ویژه'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\Action::make('attachment_download')
                        ->label('مشاهده ضمیمه')
                        ->icon('heroicon-o-paper-clip')
                        ->color('success')
                        ->visible(fn ($record) => filled($record->attachment))
                        ->url(fn ($record) => Storage::disk('public')->url($record->attachment))
                        ->openUrlInNewTab(true),
                    Tables\Actions\Action::make('view_on_site')
                        ->label('مشاهده در سایت')
                        ->icon('heroicon-o-eye')
                        ->visible(fn ($record) => $record->status === Question::STATUS_PUBLISHED)
                        ->url(fn ($record) => $record->getUrl())
                        ->openUrlInNewTab(true),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('id', 'DESC');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\AnswersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuestions::route('/'),
            'create' => Pages\CreateQuestion::route('/create'),
            'edit' => Pages\EditQuestion::route('/{record}/edit'),
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
