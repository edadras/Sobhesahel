<?php

namespace App\Filament\Resources\QuestionResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class AnswersRelationManager extends RelationManager
{
    protected static string $relationship = 'answers';

    protected static ?string $title = 'پاسخ‌ها';

    protected static ?string $modelLabel = 'پاسخ';

    protected static ?string $pluralModelLabel = 'پاسخ‌ها';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Textarea::make('body')
                    ->label('متن پاسخ')
                    ->rows(5)
                    ->columnSpanFull()
                    ->required(),
                Forms\Components\TextInput::make('answerer_name')
                    ->label('نام پاسخ‌دهنده')
                    ->default(fn () => auth()->user()?->name),
                Forms\Components\Toggle::make('is_expert')
                    ->label('پاسخ کارشناسی')
                    ->helperText('این پاسخ با نشان «پاسخ کارشناسی» در سایت نمایش داده می‌شود'),
                Forms\Components\Toggle::make('is_published')
                    ->label('منتشر شود')
                    ->default(true),
                Forms\Components\TextInput::make('sort')
                    ->label('ترتیب')
                    ->numeric()
                    ->default(0),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('body')
            ->columns([
                Tables\Columns\TextColumn::make('body')
                    ->label('متن پاسخ')
                    ->limit(70)
                    ->searchable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('answerer_name')
                    ->label('پاسخ‌دهنده')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('is_expert')
                    ->label('نوع پاسخ')->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'پاسخ کارشناسی' : 'عادی')
                    ->color(fn ($state) => $state ? 'success' : 'gray'),

                Tables\Columns\IconColumn::make('is_published')
                    ->label('منتشر شده')
                    ->boolean(),

                Tables\Columns\TextColumn::make('sort')
                    ->label('ترتیب')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاریخ ایجاد')
                    ->jalaliDateTime('H:i Y/m/d')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_expert')->label('پاسخ کارشناسی'),
                Tables\Filters\TernaryFilter::make('is_published')->label('منتشر شده'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('افزودن پاسخ')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = auth()->id();

                        return $data;
                    }),
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
            ->defaultSort('sort');
    }
}
