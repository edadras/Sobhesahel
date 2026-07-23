<?php

namespace App\Filament\Pages;

use App\Models\Category;
use App\Models\EditorRating;
use App\Models\News;
use App\Models\User;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Morilog\Jalali\CalendarUtils;

/**
 * امتیازدهی دبیران (roadmap section 3) — editors score published news
 * items 1-10; averages feed the payroll report (گزارش فعالیت تحریریه).
 */
class EditorScoring extends Page implements HasTable
{
    use HasPageShield;
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static ?string $navigationGroup = 'آمار';

    protected static ?string $navigationLabel = 'امتیازدهی دبیران';

    protected static ?string $title = 'امتیازدهی دبیران';

    protected static ?string $slug = 'editor-scoring';

    protected static string $view = 'filament.pages.editor-scoring';

    protected static function ratingsTableExists(): bool
    {
        try {
            return Schema::hasTable('editor_ratings');
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                News::query()
                    ->where('status', 'published')
                    ->latest('publish_at')
            )
            ->columns([
                TextColumn::make('title')
                    ->label('تیتر')
                    ->searchable()
                    ->limit(60)
                    ->wrap(),
                TextColumn::make('user.name')
                    ->label('خبرنگار'),
                TextColumn::make('publish_at')
                    ->label('تاریخ انتشار')
                    ->jalaliDateTime()
                    ->sortable(),
                TextColumn::make('visits')
                    ->label('بازدید')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('my_score')
                    ->label('امتیاز من')
                    ->state(function (News $record): string {
                        if (! static::ratingsTableExists()) {
                            return '—';
                        }

                        $score = EditorRating::query()
                            ->where('news_id', $record->id)
                            ->where('user_id', auth()->id())
                            ->value('score');

                        return $score !== null
                            ? CalendarUtils::convertNumbers((string) $score) . ' از ۱۰'
                            : '—';
                    }),
                TextColumn::make('avg_score')
                    ->label('میانگین امتیاز')
                    ->state(function (News $record): string {
                        if (! static::ratingsTableExists()) {
                            return '—';
                        }

                        $average = EditorRating::query()
                            ->where('news_id', $record->id)
                            ->avg('score');

                        return $average !== null
                            ? CalendarUtils::convertNumbers(number_format((float) $average, 1))
                            : '—';
                    }),
            ])
            ->filters([
                SelectFilter::make('user_id')
                    ->label('خبرنگار')
                    ->options(fn () => User::query()->orderBy('name')->pluck('name', 'id'))
                    ->searchable(),
                SelectFilter::make('categories')
                    ->label('سرویس')
                    ->relationship('categories', 'title')
                    ->searchable()
                    ->preload(),
                Filter::make('publish_at')
                    ->label('بازه انتشار')
                    ->form([
                        DatePicker::make('from')
                            ->label('از تاریخ')
                            ->jalali(),
                        DatePicker::make('to')
                            ->label('تا تاریخ')
                            ->jalali(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'] ?? null,
                                fn (Builder $q, $date) => $q->where('publish_at', '>=', Carbon::parse($date)->startOfDay()),
                            )
                            ->when(
                                $data['to'] ?? null,
                                fn (Builder $q, $date) => $q->where('publish_at', '<=', Carbon::parse($date)->endOfDay()),
                            );
                    }),
            ])
            ->actions([
                Action::make('rate')
                    ->label('ثبت امتیاز')
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->visible(fn (): bool => static::ratingsTableExists())
                    ->modalHeading('ثبت امتیاز دبیر')
                    ->modalSubmitActionLabel('ثبت')
                    ->modalCancelActionLabel('انصراف')
                    ->fillForm(function (News $record): array {
                        $rating = EditorRating::query()
                            ->where('news_id', $record->id)
                            ->where('user_id', auth()->id())
                            ->first();

                        return [
                            'score' => $rating?->score,
                            'note' => $rating?->note,
                        ];
                    })
                    ->form([
                        TextInput::make('score')
                            ->label('امتیاز (۱ تا ۱۰)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(10)
                            ->required(),
                        Textarea::make('note')
                            ->label('یادداشت (اختیاری)')
                            ->rows(3)
                            ->maxLength(500),
                    ])
                    ->action(function (News $record, array $data): void {
                        if (! static::ratingsTableExists()) {
                            Notification::make()
                                ->title('جدول امتیازدهی دبیران هنوز ایجاد نشده است.')
                                ->danger()
                                ->send();

                            return;
                        }

                        $rating = EditorRating::query()
                            ->where('news_id', $record->id)
                            ->where('user_id', auth()->id())
                            ->first();

                        if ($rating) {
                            $rating->update([
                                'score' => (int) $data['score'],
                                'note' => $data['note'] ?? null,
                            ]);
                        } else {
                            EditorRating::create([
                                'news_id' => $record->id,
                                'user_id' => auth()->id(),
                                'score' => (int) $data['score'],
                                'note' => $data['note'] ?? null,
                                'created_at' => now(),
                            ]);
                        }

                        Notification::make()
                            ->title('امتیاز شما ثبت شد.')
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultPaginationPageOption(10)
            ->emptyStateHeading('خبری برای امتیازدهی یافت نشد');
    }
}
