<?php

namespace App\Filament\Resources\ArchiveResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class NewsRelationManager extends RelationManager
{
    protected static string $relationship = 'news';

    protected static ?string $title = 'خبرهای این شماره';

    protected static ?string $modelLabel = 'خبر';

    protected static ?string $pluralModelLabel = 'خبرها';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('عنوان خبر')
                    ->disabled(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('کد خبر'),
                Tables\Columns\TextColumn::make('title')
                    ->label('عنوان خبر')
                    ->limit(70)
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('وضعیت انتشار')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'draft' => 'پیش نویس',
                        'scheduled' => 'زمان بندی شده',
                        'published' => 'منتشر شده',
                        default => 'نامشخص'
                    })
                    ->color(fn ($state) => match ($state) {
                        'draft' => 'warning',
                        'scheduled' => 'danger',
                        'published' => 'success',
                        default => 'gray'
                    }),
            ])
            ->reorderable('sort_order')
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label('اتصال خبر')
                    ->modalHeading('اتصال خبر به این شماره')
                    ->recordSelectSearchColumns(['title', 'id'])
                    ->recordSelect(
                        fn (Forms\Components\Select $select) => $select
                            ->label('خبر')
                            ->placeholder('جستجو بر اساس عنوان یا کد خبر')
                    )
                    ->attachAnother(false),
            ])
            ->actions([
                Tables\Actions\DetachAction::make()->label('حذف از شماره'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make()->label('حذف از شماره'),
                ]),
            ])
            ->emptyStateHeading('خبری به این شماره متصل نشده است');
    }
}
