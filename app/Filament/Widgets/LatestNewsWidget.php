<?php

namespace App\Filament\Widgets;

use App\Models\News;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Carbon\Carbon;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Model;
use Morilog\Jalali\Jalalian;

class LatestNewsWidget extends BaseWidget
{
    use HasWidgetShield;

    protected static ?string $heading = 'آخرین اخبار';

    protected static ?int $sort = 10;

    protected static bool $isLazy = false;

    protected static ?string $pollingInterval = '5s';

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                News::query()
            )
            ->columns([
                Tables\Columns\ImageColumn::make('image_original')->label('تصویر'),
                Tables\Columns\TextColumn::make('title')->label('عنوان')
                    ->description(fn (News $record): string => substr(strip_tags($record->short_description),0,128) . '..'),
                Tables\Columns\TextColumn::make('visits')->label('بازدید'),
                Tables\Columns\TextColumn::make('status')
                    ->label('منتشر شده')->badge()
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
                    ->description(fn (News $record): string => Jalalian::fromCarbon(Carbon::parse($record->created_at))->format('H:i Y/m/d')),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاریخ ایجاد')
                    ->jalaliDateTime('H:i Y/m/d'),

            ])->actions([
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
            ->defaultSort('id','DESC');
    }
}
