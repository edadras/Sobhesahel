<?php

namespace App\Services\Points;

use App\Models\Badge;
use App\Models\BadgeMember;
use App\Models\LoginStreak;
use App\Models\Mission;
use App\Models\MissionCompletion;
use App\Models\PointRule;
use App\Models\PointTransaction;
use App\Models\WheelPrize;
use App\Models\WheelSpin;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * منطق باشگاه اعضا: زنجیره ورود روزانه، ماموریت‌ها، چرخ شانس و نشان‌ها.
 *
 * همه متدها در برابر نبود جدول‌ها (پیش از migration) محافظت شده‌اند.
 */
class ClubService
{
    public function __construct(protected PointsService $points)
    {
    }

    /**
     * ثبت ورود روزانه عضو: به‌روزرسانی زنجیره (به‌وقت Asia/Tehran)، امتیاز
     * daily_login و جایزه streak_bonus در نقاط عطف پیکربندی‌شده.
     *
     * بازگشت: مدل LoginStreak یا null (جدول آماده نیست). ورود دوم در همان روز
     * زنجیره و امتیاز را تکرار نمی‌کند.
     */
    public function recordDailyLogin(int $memberId): ?LoginStreak
    {
        if ($memberId <= 0 || ! $this->points->hasTable('login_streaks')) {
            return null;
        }

        try {
            $tz = (string) config('points.timezone', 'Asia/Tehran');
            $today = Carbon::now($tz)->startOfDay();

            /** @var LoginStreak $streak */
            $streak = LoginStreak::query()->firstOrCreate(
                ['member_id' => $memberId],
                ['current_days' => 0, 'longest_days' => 0],
            );

            $lastDate = $streak->last_login_date?->format('Y-m-d');

            if ($lastDate === $today->toDateString()) {
                return $streak; // امروز قبلاً ثبت شده
            }

            $streak->current_days = ($lastDate === $today->copy()->subDay()->toDateString())
                ? $streak->current_days + 1
                : 1;
            $streak->longest_days = max($streak->longest_days, $streak->current_days);
            $streak->last_login_date = $today->toDateString();
            $streak->save();

            $this->points->award($memberId, 'daily_login', $streak);

            $milestones = (array) config('points.streak_milestones', [7, 30]);

            if (in_array($streak->current_days, array_map('intval', $milestones), true)) {
                $this->points->award(
                    $memberId,
                    'streak_bonus',
                    $streak,
                    "جایزه زنجیره ورود {$streak->current_days} روزه",
                );
            }

            $this->progressMission($memberId, 'daily_login');
            $this->checkBadges($memberId);

            return $streak;
        } catch (\Throwable $e) {
            Log::warning('ClubService::recordDailyLogin failed', ['member' => $memberId, 'error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * جلو بردن پیشرفت ماموریت‌های فعالِ هم‌رویداد در دوره جاری؛ در صورت رسیدن
     * به هدف، completed_at ثبت و امتیاز ماموریت اهدا می‌شود.
     *
     * بازگشت: لیست ماموریت‌هایی که همین الان تکمیل شدند.
     *
     * @return array<int, Mission>
     */
    public function progressMission(int $memberId, string $eventCode): array
    {
        if (
            $memberId <= 0
            || ! $this->points->hasTable('missions')
            || ! $this->points->hasTable('mission_completions')
        ) {
            return [];
        }

        $completedNow = [];

        try {
            $missions = Mission::query()->active()->where('event_code', $eventCode)->get();

            foreach ($missions as $mission) {
                $periodKey = $this->periodKey($mission->period);

                /** @var MissionCompletion $completion */
                $completion = MissionCompletion::query()->firstOrCreate(
                    [
                        'member_id' => $memberId,
                        'mission_id' => $mission->id,
                        'period_key' => $periodKey,
                    ],
                    ['progress' => 0],
                );

                if ($completion->completed_at !== null) {
                    continue; // در این دوره قبلاً تکمیل شده
                }

                $completion->progress = $completion->progress + 1;

                if ($completion->progress >= max(1, (int) $mission->goal_count)) {
                    $completion->completed_at = now();
                    $completion->save();

                    if ((int) $mission->points > 0) {
                        $this->points->awardCustom(
                            $memberId,
                            'mission',
                            (int) $mission->points,
                            $mission,
                            "تکمیل ماموریت: {$mission->title}",
                        );
                    }

                    $completedNow[] = $mission;
                } else {
                    $completion->save();
                }
            }

            if ($completedNow !== []) {
                $this->checkBadges($memberId);
            }
        } catch (\Throwable $e) {
            Log::warning('ClubService::progressMission failed', ['member' => $memberId, 'event' => $eventCode, 'error' => $e->getMessage()]);
        }

        return $completedNow;
    }

    /**
     * چرخاندن چرخ شانس.
     *
     * - هر روز (Asia/Tehran) به تعداد points.wheel_free_spins_per_day چرخش رایگان.
     * - چرخش اضافه به هزینه قانون فعال wheel_cost (قدرمطلق امتیاز) یا در نبود
     *   آن config('points.wheel_cost')، از موجودی عضو کسر می‌شود.
     * - انتخاب جایزه به‌صورت تصادفی وزنی بین جوایز فعالِ دارای موجودی.
     * - جایزه از نوع points با قانون wheel به دفتر کل اضافه می‌شود.
     *
     * بازگشت: WheelSpin (با رابطه prize بارگذاری‌شده) یا null در صورت
     * ناکافی بودن موجودی / آماده نبودن جدول‌ها.
     */
    public function spinWheel(int $memberId, bool $useFreeSpin = true): ?WheelSpin
    {
        if (
            $memberId <= 0
            || ! $this->points->hasTable('wheel_prizes')
            || ! $this->points->hasTable('wheel_spins')
        ) {
            return null;
        }

        try {
            $isFree = $useFreeSpin && $this->freeSpinAvailable($memberId);
            $cost = 0;

            if (! $isFree) {
                $cost = $this->wheelCost();

                if ($cost > 0 && $this->points->spend($memberId, $cost, 'wheel_cost', null, 'هزینه چرخش چرخ شانس') === null) {
                    return null; // موجودی کافی نیست
                }
            }

            $spin = DB::transaction(function () use ($memberId, $cost) {
                $prizes = WheelPrize::query()
                    ->active()
                    ->where('weight', '>', 0)
                    ->where(fn ($q) => $q->whereNull('stock')->orWhere('stock', '>', 0))
                    ->lockForUpdate()
                    ->get();

                $prize = $this->pickWeighted($prizes->all());

                if ($prize && $prize->stock !== null) {
                    $prize->decrement('stock');
                }

                $spin = new WheelSpin([
                    'member_id' => $memberId,
                    'wheel_prize_id' => $prize?->id,
                    'cost_points' => $cost,
                    'created_at' => now(),
                ]);
                $spin->save();

                return $spin;
            });

            $prize = $spin->prize;

            if ($prize && $prize->type === 'points' && $prize->pointsValue() > 0) {
                $this->points->awardCustom(
                    $memberId,
                    'wheel',
                    $prize->pointsValue(),
                    $prize,
                    "جایزه چرخ شانس: {$prize->title}",
                );
            }

            $this->progressMission($memberId, 'wheel');
            $this->checkBadges($memberId);

            return $spin;
        } catch (\Throwable $e) {
            Log::warning('ClubService::spinWheel failed', ['member' => $memberId, 'error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * آیا عضو امروز هنوز چرخش رایگان دارد؟
     */
    public function freeSpinAvailable(int $memberId): bool
    {
        if ($memberId <= 0 || ! $this->points->hasTable('wheel_spins')) {
            return false;
        }

        try {
            [$dayStart, $dayEnd] = $this->points->todayRange();

            $freeUsed = WheelSpin::query()
                ->where('member_id', $memberId)
                ->where('cost_points', 0)
                ->whereBetween('created_at', [$dayStart, $dayEnd])
                ->count();

            return $freeUsed < max(0, (int) config('points.wheel_free_spins_per_day', 1));
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * هزینه چرخش اضافه: قانون فعال wheel_cost (قدرمطلق) و در نبود آن config.
     */
    public function wheelCost(): int
    {
        try {
            if ($this->points->hasTable('point_rules')) {
                $rule = PointRule::query()->active()->where('code', 'wheel_cost')->first();

                if ($rule && (int) $rule->points !== 0) {
                    return abs((int) $rule->points);
                }
            }
        } catch (\Throwable) {
            // از مقدار config استفاده می‌شود
        }

        return max(0, (int) config('points.wheel_cost', 100));
    }

    /**
     * ارزیابی شرط نشان‌های فعال و اهدای نشان‌های جدید.
     *
     * @return array<int, Badge> نشان‌هایی که همین الان اهدا شدند
     */
    public function checkBadges(int $memberId): array
    {
        if (
            $memberId <= 0
            || ! $this->points->hasTable('badges')
            || ! $this->points->hasTable('badge_member')
        ) {
            return [];
        }

        $awarded = [];

        try {
            $ownedIds = BadgeMember::query()
                ->where('member_id', $memberId)
                ->pluck('badge_id')
                ->all();

            $candidates = Badge::query()
                ->active()
                ->where('condition_type', '!=', 'manual')
                ->when($ownedIds !== [], fn ($q) => $q->whereNotIn('id', $ownedIds))
                ->get();

            if ($candidates->isEmpty()) {
                return [];
            }

            foreach ($candidates as $badge) {
                if ($this->metric($memberId, $badge->condition_type) >= max(1, (int) $badge->condition_value)) {
                    BadgeMember::query()->firstOrCreate(
                        ['member_id' => $memberId, 'badge_id' => $badge->id],
                        ['awarded_at' => now()],
                    );

                    $awarded[] = $badge;
                }
            }
        } catch (\Throwable $e) {
            Log::warning('ClubService::checkBadges failed', ['member' => $memberId, 'error' => $e->getMessage()]);
        }

        return $awarded;
    }

    /**
     * مقدار فعلی سنجه شرط نشان برای عضو.
     */
    protected function metric(int $memberId, string $conditionType): int
    {
        return match ($conditionType) {
            'points_total' => $this->points->totalEarned($memberId),
            'streak_days' => $this->points->hasTable('login_streaks')
                ? (int) (LoginStreak::query()->where('member_id', $memberId)->value('longest_days') ?? 0)
                : 0,
            'missions_completed' => $this->points->hasTable('mission_completions')
                ? (int) MissionCompletion::query()
                    ->where('member_id', $memberId)
                    ->whereNotNull('completed_at')
                    ->count()
                : 0,
            'transactions_count' => $this->points->ready()
                ? (int) PointTransaction::query()->where('member_id', $memberId)->count()
                : 0,
            default => 0,
        };
    }

    /**
     * کلید دوره جاری ماموریت به‌وقت Asia/Tehran.
     */
    public function periodKey(string $period): string
    {
        $tz = (string) config('points.timezone', 'Asia/Tehran');
        $now = Carbon::now($tz);

        return match ($period) {
            'daily' => $now->toDateString(), // 2026-07-24
            'weekly' => $now->format('o-\WW'), // 2026-W30 (هفته ISO)
            default => 'once',
        };
    }

    /**
     * انتخاب تصادفی وزنی از میان جوایز.
     *
     * @param array<int, WheelPrize> $prizes
     */
    protected function pickWeighted(array $prizes): ?WheelPrize
    {
        if ($prizes === []) {
            return null;
        }

        $total = 0;

        foreach ($prizes as $prize) {
            $total += max(0, (int) $prize->weight);
        }

        if ($total <= 0) {
            return null;
        }

        $roll = random_int(1, $total);

        foreach ($prizes as $prize) {
            $roll -= max(0, (int) $prize->weight);

            if ($roll <= 0) {
                return $prize;
            }
        }

        return end($prizes) ?: null;
    }
}
