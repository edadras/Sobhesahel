<?php

namespace App\Filament\Resources\AuthorResource\RelationManagers;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Morilog\Jalali\Jalalian;

class VideosRelationManager extends RelationManager
{
    protected static string $relationship = 'videos';

    protected static ?string $title = 'ویدئو ها';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_large')->label('تصویر'),
                Tables\Columns\TextColumn::make('title')->label('عنوان')->searchable()
                    ->description(fn ($record): string => substr(strip_tags($record->short_description),0,128) . '..'),
                Tables\Columns\TextColumn::make('visits')->label('بازدید'),
                Tables\Columns\TextColumn::make('status')
                    ->label('وضعیت انتشار')->badge()
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
                    })
                    ->description(fn ($record): string => Jalalian::fromCarbon(Carbon::parse($record->created_at))->format('H:i Y/m/d')),
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
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\Action::make('copy_link')
                        ->label('لینک پست')
                        ->icon('heroicon-o-clipboard')
//                        ->action(fn (News $record) => Component::dispatchBrowserEvent('copyToClipboard', ['text' => $record->getUrl()]))
                        ->url(fn($record) => $record->getUrl())
                        ->color('success')
                        ->openUrlInNewTab(true),
                    Tables\Actions\Action::make('copy_link')
                        ->label('لینک کوتاه')
                        ->icon('heroicon-o-clipboard')
//                        ->action(fn (News $record) => Component::dispatchBrowserEvent('copyToClipboard', ['text' => $record->getUrl()]))
                        ->url(fn($record) => $record->getShortUrl())
                        ->openUrlInNewTab(true),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]) ->defaultSort('id','DESC');
    }
}
