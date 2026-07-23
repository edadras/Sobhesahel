<?php

namespace App\Filament\Pages;

use App\Models\Category;
use App\Models\Gallery;
use App\Models\News;
use App\Models\NewsRevision;
use App\Models\Note;
use App\Models\Podcast;
use App\Models\User;
use App\Models\Video;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Morilog\Jalali\Jalalian;

/**
 * گزارش فعالیت تحریریه — روزنامه‌نگاران / حقوق و دستمزد (roadmap section 16).
 *
 * Per-user counts of published content by type, total visits, per-service
 * (category) breakdown, edit counts from news_revisions and the average
 * editor score from editor_ratings.
 */
class EditorialReport extends Page
{
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'آمار';

    protected static ?string $navigationLabel = 'گزارش فعالیت تحریریه';

    protected static ?string $title = 'گزارش فعالیت تحریریه';

    protected static ?string $slug = 'editorial-report';

    protected static string $view = 'filament.pages.editorial-report';

    public const TYPE_LABELS = [
        'news' => 'خبر',
        'note' => 'یادداشت',
        'video' => 'ویدئو',
        'podcast' => 'پادکست',
        'photo' => 'گزارش تصویری',
    ];

    public ?array $filters = [];

    /**
     * Per-request cache of the computed report.
     */
    protected ?array $reportCache = null;

    public function mount(): void
    {
        $monthStart = Jalalian::fromCarbon(now())->getFirstDayOfMonth()->toCarbon();

        $this->form->fill([
            'from' => $monthStart->toDateString(),
            'to' => now()->toDateString(),
            'user_id' => null,
            'category_id' => null,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('filters')
            ->schema([
                Grid::make(4)
                    ->schema([
                        DatePicker::make('from')
                            ->label('از تاریخ')
                            ->jalali(),
                        DatePicker::make('to')
                            ->label('تا تاریخ')
                            ->jalali(),
                        Select::make('user_id')
                            ->label('کاربر')
                            ->options(fn () => User::query()->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->placeholder('همه کاربران'),
                        Select::make('category_id')
                            ->label('سرویس')
                            ->options(fn () => Category::query()->orderBy('title')->pluck('title', 'id'))
                            ->searchable()
                            ->placeholder('همه سرویس‌ها')
                            ->helperText('فیلتر سرویس فقط بر اخبار اعمال می‌شود.'),
                    ]),
            ]);
    }

    public function applyFilters(): void
    {
        $this->form->getState();
        $this->reportCache = null;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label('خروجی Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(fn () => $this->exportCsv()),
        ];
    }

    /**
     * Stream the current filtered report as a UTF-8 (BOM) CSV that opens
     * correctly in Excel with Persian text. No extra packages needed.
     */
    public function exportCsv()
    {
        $report = $this->getReport();

        $fileName = 'editorial-report-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($report) {
            $out = fopen('php://output', 'w');

            // UTF-8 BOM so Excel detects the encoding for Persian text.
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, array_merge(
                ['کاربر'],
                array_values(self::TYPE_LABELS),
                ['مجموع مطالب', 'مجموع بازدید', 'تعداد ویرایش‌ها', 'میانگین امتیاز دبیران'],
            ));

            foreach ($report['rows'] as $row) {
                fputcsv($out, array_merge(
                    [$row['name']],
                    array_map(fn ($type) => $row['counts'][$type], array_keys(self::TYPE_LABELS)),
                    [
                        $row['total'],
                        $row['visits'],
                        $row['edits'],
                        $row['editor_score'] !== null ? number_format($row['editor_score'], 2) : '',
                    ],
                ));
            }

            $totals = $report['totals'];
            fputcsv($out, array_merge(
                ['جمع کل'],
                array_map(fn ($type) => $totals['counts'][$type], array_keys(self::TYPE_LABELS)),
                [$totals['total'], $totals['visits'], $totals['edits'], ''],
            ));

            if (! empty($report['services'])) {
                fputcsv($out, []);
                fputcsv($out, ['تفکیک اخبار منتشرشده بر اساس سرویس']);
                fputcsv($out, ['کاربر', 'سرویس', 'تعداد خبر', 'مجموع بازدید']);

                foreach ($report['services'] as $row) {
                    fputcsv($out, [$row['name'], $row['category'], $row['total'], $row['visits']]);
                }
            }

            fclose($out);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Resolved [from, to] Carbon range from the filter state.
     */
    protected function dateRange(): array
    {
        try {
            $from = ! empty($this->filters['from'])
                ? Carbon::parse($this->filters['from'])->startOfDay()
                : Jalalian::fromCarbon(now())->getFirstDayOfMonth()->toCarbon()->startOfDay();
        } catch (\Throwable $e) {
            $from = now()->startOfMonth();
        }

        try {
            $to = ! empty($this->filters['to'])
                ? Carbon::parse($this->filters['to'])->endOfDay()
                : now()->endOfDay();
        } catch (\Throwable $e) {
            $to = now()->endOfDay();
        }

        if ($from->greaterThan($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        return [$from, $to];
    }

    public function getReport(): array
    {
        if ($this->reportCache !== null) {
            return $this->reportCache;
        }

        [$from, $to] = $this->dateRange();

        $userId = $this->filters['user_id'] ?? null;
        $categoryId = $this->filters['category_id'] ?? null;

        $models = [
            'news' => News::class,
            'note' => Note::class,
            'video' => Video::class,
            'podcast' => Podcast::class,
            'photo' => Gallery::class,
        ];

        $emptyCounts = array_fill_keys(array_keys(self::TYPE_LABELS), 0);

        /** @var array<int, array> $rows keyed by user id */
        $rows = [];

        $ensureRow = function ($id) use (&$rows, $emptyCounts) {
            if (! isset($rows[$id])) {
                $rows[$id] = [
                    'user_id' => $id,
                    'name' => null,
                    'counts' => $emptyCounts,
                    'total' => 0,
                    'visits' => 0,
                    'edits' => 0,
                    'editor_score' => null,
                    'editor_score_count' => 0,
                ];
            }
        };

        // Published content per user per type + visit sums.
        foreach ($models as $type => $modelClass) {
            /** @var \Illuminate\Database\Eloquent\Model $model */
            $model = new $modelClass();

            if (! Schema::hasTable($model->getTable())) {
                continue;
            }

            // Service (category) filter only applies to news — other content
            // types have no service, so they are excluded when it is set.
            if ($categoryId && $type !== 'news') {
                continue;
            }

            $query = $modelClass::query()
                ->where('status', 'published')
                ->whereBetween('publish_at', [$from, $to])
                ->whereNotNull('user_id');

            if ($userId) {
                $query->where('user_id', $userId);
            }

            if ($categoryId && $type === 'news') {
                $query->whereHas('categories', fn ($q) => $q->where('categories.id', $categoryId));
            }

            $aggregates = $query
                ->groupBy('user_id')
                ->selectRaw('user_id, COUNT(*) as total_count, COALESCE(SUM(visits), 0) as total_visits')
                ->get();

            foreach ($aggregates as $aggregate) {
                $ensureRow($aggregate->user_id);
                $rows[$aggregate->user_id]['counts'][$type] += (int) $aggregate->total_count;
                $rows[$aggregate->user_id]['total'] += (int) $aggregate->total_count;
                $rows[$aggregate->user_id]['visits'] += (int) $aggregate->total_visits;
            }
        }

        // Edit counts from the news revision history (تعداد ویرایش‌ها).
        if (Schema::hasTable('news_revisions')) {
            $revisionQuery = NewsRevision::query()
                ->whereIn('action', ['updated', 'status_changed'])
                ->whereBetween('created_at', [$from, $to])
                ->whereNotNull('user_id');

            if ($userId) {
                $revisionQuery->where('user_id', $userId);
            }

            $revisionCounts = $revisionQuery
                ->groupBy('user_id')
                ->selectRaw('user_id, COUNT(*) as total_count')
                ->get();

            foreach ($revisionCounts as $aggregate) {
                $ensureRow($aggregate->user_id);
                $rows[$aggregate->user_id]['edits'] += (int) $aggregate->total_count;
            }
        }

        // Average editor score per user (میانگین امتیاز دبیران) for payroll.
        if (Schema::hasTable('editor_ratings') && Schema::hasTable('news')) {
            $scoreQuery = DB::table('editor_ratings')
                ->join('news', 'news.id', '=', 'editor_ratings.news_id')
                ->whereNull('news.deleted_at')
                ->whereBetween('news.publish_at', [$from, $to])
                ->whereNotNull('news.user_id');

            if ($userId) {
                $scoreQuery->where('news.user_id', $userId);
            }

            if ($categoryId && Schema::hasTable('category_news')) {
                $scoreQuery
                    ->join('category_news', 'category_news.news_id', '=', 'news.id')
                    ->where('category_news.category_id', $categoryId);
            }

            $scores = $scoreQuery
                ->groupBy('news.user_id')
                ->selectRaw('news.user_id as user_id, AVG(editor_ratings.score) as avg_score, COUNT(*) as score_count')
                ->get();

            foreach ($scores as $aggregate) {
                $ensureRow($aggregate->user_id);
                $rows[$aggregate->user_id]['editor_score'] = round((float) $aggregate->avg_score, 2);
                $rows[$aggregate->user_id]['editor_score_count'] = (int) $aggregate->score_count;
            }
        }

        // Resolve user names.
        $names = User::query()
            ->whereIn('id', array_keys($rows))
            ->pluck('name', 'id');

        foreach ($rows as $id => $row) {
            $rows[$id]['name'] = $names[$id] ?? ('کاربر #' . $id);
        }

        // Sort by total published content, descending.
        uasort($rows, fn ($a, $b) => [$b['total'], $b['visits']] <=> [$a['total'], $a['visits']]);

        // Totals row.
        $totals = [
            'counts' => $emptyCounts,
            'total' => 0,
            'visits' => 0,
            'edits' => 0,
        ];

        foreach ($rows as $row) {
            foreach ($row['counts'] as $type => $count) {
                $totals['counts'][$type] += $count;
            }
            $totals['total'] += $row['total'];
            $totals['visits'] += $row['visits'];
            $totals['edits'] += $row['edits'];
        }

        return $this->reportCache = [
            'from' => $from,
            'to' => $to,
            'rows' => array_values($rows),
            'totals' => $totals,
            'services' => $this->buildServiceBreakdown($from, $to, $userId, $categoryId),
        ];
    }

    /**
     * Published news per user per service (category).
     */
    protected function buildServiceBreakdown(Carbon $from, Carbon $to, $userId, $categoryId): array
    {
        if (! Schema::hasTable('news') || ! Schema::hasTable('category_news') || ! Schema::hasTable('categories')) {
            return [];
        }

        $query = DB::table('news')
            ->join('category_news', 'category_news.news_id', '=', 'news.id')
            ->join('categories', 'categories.id', '=', 'category_news.category_id')
            ->leftJoin('users', 'users.id', '=', 'news.user_id')
            ->where('news.status', 'published')
            ->whereNull('news.deleted_at')
            ->whereBetween('news.publish_at', [$from, $to])
            ->whereNotNull('news.user_id');

        if ($userId) {
            $query->where('news.user_id', $userId);
        }

        if ($categoryId) {
            $query->where('categories.id', $categoryId);
        }

        return $query
            ->groupBy('news.user_id', 'users.name', 'categories.id', 'categories.title')
            ->selectRaw('news.user_id, users.name, categories.title as category, COUNT(*) as total, COALESCE(SUM(news.visits), 0) as visits')
            ->orderBy('users.name')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [
                'user_id' => $row->user_id,
                'name' => $row->name ?? ('کاربر #' . $row->user_id),
                'category' => $row->category,
                'total' => (int) $row->total,
                'visits' => (int) $row->visits,
            ])
            ->all();
    }
}
