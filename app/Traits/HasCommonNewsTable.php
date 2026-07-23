<?php

namespace App\Traits;

use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Morilog\Jalali\Jalalian;
use Carbon\Carbon;
use App\Models\FeaturedNews;
use Filament\Forms;

trait HasCommonNewsTable
{
    public static function getCommonNewsColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('id')
                ->label('کد خبر')
                ->sortable()
                ->searchable()
                ->toggleable(isToggledHiddenByDefault: true),

            Tables\Columns\ImageColumn::make('image_original')
                ->label('تصویر'),

            Tables\Columns\TextColumn::make('title')
                ->label('عنوان')
                ->searchable()
                ->limit(70)
                ->description(fn($record): string =>
                    substr(strip_tags($record->short_description), 0, 100) . '..'
                ),

            Tables\Columns\TextColumn::make('visits')
                ->label('بازدید'),

            Tables\Columns\TextColumn::make('status')
                ->label('وضعیت انتشار')
                ->badge()
                ->formatStateUsing(fn($state) => match ($state) {
                    'draft' => 'پیش نویس',
                    'pending_review' => 'در انتظار تأیید',
                    'scheduled' => 'زمان بندی شده',
                    'published' => 'منتشر شده',
                    'suspended' => 'معلق',
                    default => 'نامشخص'
                })
                ->color(fn($state) => match ($state) {
                    'draft' => 'warning',
                    'pending_review' => 'info',
                    'scheduled' => 'danger',
                    'published' => 'success',
                    'suspended' => 'gray',
                    default => 'gray'
                })
                ->description(function ($record) {
                    $publishDate = $record->publish_at
                        ? Jalalian::fromCarbon(Carbon::parse($record->publish_at))->format('H:i Y/m/d')
                        : 'بدون تاریخ';

                    $featuredSections = method_exists($record, 'featuredNews')
                        ? $record->featuredNews()
                            ->active()
                            ->get()
                            ->map(fn($featured) => '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium ' .
                                ($featured->section === 'top'
                                    ? 'bg-blue-100 text-blue-800'
                                    : 'bg-green-100 text-green-800') .
                                '">' .
                                (FeaturedNews::BOXES[$featured->section]['title'] ?? $featured->section) .
                                '</span>')
                            ->join(' ')
                        : '';

                    return $publishDate . ($featuredSections ? '<br>' . $featuredSections : '');
                })
                ->html(),

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
        ];
    }

    public static function getCommonNewsFilters(): array
    {
        return [
            Tables\Filters\SelectFilter::make('categories')
                ->relationship('categories', 'title')
                ->label('دسته‌بندی')
                ->searchable(),

            Tables\Filters\SelectFilter::make('author_id')
                ->relationship('author', 'name')
                ->label('نویسنده')
                ->searchable(),

            Tables\Filters\SelectFilter::make('user_id')
                ->relationship('user', 'name')
                ->label('کاربر')
                ->searchable(),

            Tables\Filters\Filter::make('id')
                ->form([
                    Forms\Components\TextInput::make('id')->label('کد خبر'),
                ])
                ->query(fn(Builder $query, array $data) =>
                $query->when($data['id'], fn($query, $id) => $query->where('id', $id))
                ),

            Tables\Filters\Filter::make('publish_at')
                ->form([
                    Forms\Components\DatePicker::make('from')->jalali()->label('از تاریخ'),
                    Forms\Components\DatePicker::make('to')->jalali()->label('تا تاریخ'),
                ])
                ->query(fn(Builder $query, array $data) =>
                $query
                    ->when($data['from'] ?? null, fn($query, $from) => $query->whereDate('publish_at', '>=', $from))
                    ->when($data['to'] ?? null, fn($query, $to) => $query->whereDate('publish_at', '<=', $to))
                ),
        ];
    }

    public static function getCommonNewsActions(): array
    {
        return [
            Tables\Actions\EditAction::make(),
            Tables\Actions\ActionGroup::make([
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('copy_link')
                    ->label('لینک پست')
//                    ->icon('o-clipboard')
                    ->url(fn($record) => $record->getUrl())
                    ->color('success')
                    ->openUrlInNewTab(true),
                Tables\Actions\Action::make('copy_short_link')
                    ->label('لینک کوتاه')
//                    ->icon('o-clipboard')
                    ->url(fn($record) => $record->getShortUrl())
                    ->openUrlInNewTab(true),
            ]),
        ];
    }

    public static function getCommonNewsBulkActions(): array
    {
        return [
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
            ]),
        ];
    }
}
