<?php

namespace App\Http\Controllers\Api\Member\Concerns;

use App\Models\Badge;
use App\Models\BadgeMember;
use App\Models\LoginStreak;
use App\Models\Mission;
use App\Models\MissionCompletion;
use App\Models\PointRule;
use App\Services\Points\ClubService;
use App\Services\Points\PointsService;
use Morilog\Jalali\Jalalian;

/**
 * سازنده‌های payload مشترک باشگاه اعضا (سطح، زنجیره، ماموریت، نشان، اشتراک،
 * قوانین امتیاز) — دقیقاً مطابق DTOهای فلاتر.
 *
 * همه از سرویس‌های موجود (PointsService/ClubService/Subscription) می‌خوانند و
 * در برابر نبود جدول‌ها با try/catch محافظت شده‌اند؛ منطق موازی ساخته نمی‌شود.
 */
trait BuildsMemberPayloads
{
    /**
     * آستانه‌های سطح‌بندی بر اساس «مجموع امتیاز کسب‌شده».
     * کلیدها با tierFromKey اپ (bronze/silver/gold/platinum) یکسان‌اند.
     *
     * @return array<int, array{key:string, title:string, min:int}>
     */
    protected function levelTiers(): array
    {
        return [
            ['key' => 'bronze', 'title' => 'برنزی', 'min' => 0],
            ['key' => 'silver', 'title' => 'نقره‌ای', 'min' => (int) config('member.levels.silver', 500)],
            ['key' => 'gold', 'title' => 'طلایی', 'min' => (int) config('member.levels.gold', 2000)],
            ['key' => 'platinum', 'title' => 'پلاتینی', 'min' => (int) config('member.levels.platinum', 5000)],
        ];
    }

    /**
     * سطح عضو مطابق MemberLevel.fromJson:
     * {key, title, progress_percent, next_threshold}
     */
    protected function levelPayload(int $totalEarned): array
    {
        $tiers = $this->levelTiers();
        $current = $tiers[0];
        $next = null;

        foreach ($tiers as $i => $tier) {
            if ($totalEarned >= $tier['min']) {
                $current = $tier;
                $next = $tiers[$i + 1] ?? null;
            }
        }

        if ($next === null) {
            // Highest tier — fully progressed, no further threshold.
            return [
                'key' => $current['key'],
                'title' => $current['title'],
                'progress_percent' => 100.0,
                'next_threshold' => 0,
            ];
        }

        $span = max(1, $next['min'] - $current['min']);
        $done = max(0, $totalEarned - $current['min']);
        $percent = min(100, max(0, round($done / $span * 100, 1)));

        return [
            'key' => $current['key'],
            'title' => $current['title'],
            'progress_percent' => (float) $percent,
            'next_threshold' => (int) $next['min'],
        ];
    }

    /**
     * زنجیره ورود مطابق Streak.fromJson:
     * {current_days, longest_days, next_milestone}
     */
    protected function streakPayload(int $memberId, PointsService $points): array
    {
        $current = 0;
        $longest = 0;

        try {
            if ($points->hasTable('login_streaks')) {
                $streak = LoginStreak::query()->where('member_id', $memberId)->first();
                $current = (int) ($streak->current_days ?? 0);
                $longest = (int) ($streak->longest_days ?? 0);
            }
        } catch (\Throwable) {
            // safe defaults
        }

        $milestones = array_map('intval', (array) config('points.streak_milestones', [7, 30]));
        sort($milestones);
        $nextMilestone = 0;

        foreach ($milestones as $m) {
            if ($m > $current) {
                $nextMilestone = $m;
                break;
            }
        }

        return [
            'current_days' => $current,
            'longest_days' => $longest,
            'next_milestone' => $nextMilestone,
        ];
    }

    /**
     * ماموریت‌ها مطابق Mission.fromJson:
     * [{code, title, points, progress, goal, completed}]
     *
     * @param  string|null  $period  اگر مشخص شود فقط ماموریت‌های آن دوره
     * @return array<int, array<string, mixed>>
     */
    protected function missionPayloads(int $memberId, ClubService $club, PointsService $points, ?string $period = null): array
    {
        if (! $points->hasTable('missions')) {
            return [];
        }

        $out = [];

        try {
            $query = Mission::query()->active();

            if ($period !== null) {
                $query->where('period', $period);
            }

            $missions = $query->orderBy('sort')->get();

            $hasCompletions = $points->hasTable('mission_completions');

            foreach ($missions as $mission) {
                $progress = 0;
                $completed = false;

                if ($hasCompletions) {
                    $periodKey = $club->periodKey($mission->period);

                    $completion = MissionCompletion::query()
                        ->where('member_id', $memberId)
                        ->where('mission_id', $mission->id)
                        ->where('period_key', $periodKey)
                        ->first();

                    $progress = (int) ($completion->progress ?? 0);
                    $completed = $completion?->completed_at !== null;
                }

                $out[] = [
                    'code' => (string) $mission->code,
                    'title' => (string) $mission->title,
                    'points' => (int) $mission->points,
                    'progress' => $progress,
                    'goal' => max(1, (int) $mission->goal_count),
                    'completed' => $completed,
                ];
            }
        } catch (\Throwable) {
            return [];
        }

        return $out;
    }

    /**
     * نشان‌ها مطابق Badge.fromJson:
     * [{code, title, description, icon, earned, awarded_at_jalali?, condition_text}]
     *
     * @return array<int, array<string, mixed>>
     */
    protected function badgePayloads(int $memberId, PointsService $points): array
    {
        if (! $points->hasTable('badges')) {
            return [];
        }

        $out = [];

        try {
            $badges = Badge::query()->active()->orderBy('sort')->get();

            $awarded = [];

            if ($points->hasTable('badge_member')) {
                $awarded = BadgeMember::query()
                    ->where('member_id', $memberId)
                    ->get()
                    ->keyBy('badge_id');
            }

            foreach ($badges as $badge) {
                $link = $awarded[$badge->id] ?? null;
                $earned = $link !== null;

                $out[] = [
                    'code' => (string) $badge->code,
                    'title' => (string) $badge->title,
                    'description' => (string) ($badge->description ?? ''),
                    'icon' => (string) ($badge->icon ?? ''),
                    'earned' => $earned,
                    'awarded_at_jalali' => $earned ? $this->badgeAwardedJalali($link) : null,
                    'condition_text' => $this->badgeConditionText($badge),
                ];
            }
        } catch (\Throwable) {
            return [];
        }

        return $out;
    }

    protected function badgeAwardedJalali($link): ?string
    {
        try {
            return $link?->awarded_at !== null
                ? Jalalian::fromCarbon($link->awarded_at)->format('%d %B %Y')
                : null;
        } catch (\Throwable) {
            return null;
        }
    }

    protected function badgeConditionText(Badge $badge): string
    {
        $value = (int) $badge->condition_value;

        return match ($badge->condition_type) {
            'points_total' => "کسب مجموع {$value} امتیاز",
            'streak_days' => "زنجیره ورود {$value} روزه",
            'missions_completed' => "تکمیل {$value} ماموریت",
            'transactions_count' => "{$value} تراکنش امتیاز",
            default => (string) ($badge->description ?? 'اهدای دستی'),
        };
    }

    /**
     * خلاصه اشتراک فعال مطابق ActiveSubscription.fromJson:
     * {plan_title, ends_at_jalali, days_left, status} یا null.
     */
    protected function activeSubscriptionPayload($member): ?array
    {
        try {
            $subscription = $member->activeSubscription();

            if ($subscription === null || $subscription->ends_at === null) {
                return null;
            }

            $daysLeft = max(0, (int) ceil((float) now()->diffInDays($subscription->ends_at, false)));

            return [
                'plan_title' => (string) ($subscription->plan?->name ?? 'اشتراک ویژه'),
                'plan_id' => $subscription->plan_id !== null ? (int) $subscription->plan_id : null,
                'ends_at' => $subscription->ends_at->toIso8601String(),
                'ends_at_jalali' => Jalalian::fromCarbon($subscription->ends_at)->format('%d %B %Y'),
                'days_left' => $daysLeft,
                'status' => 'active',
            ];
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * قوانین کسب/خرج امتیاز مطابق PointsRule.fromJson:
     * {code, title, points}  (points به‌صورت رشته)
     *
     * @return array{earn: array<int, array<string,string>>, spend: array<int, array<string,string>>}
     */
    protected function pointRulePayloads(PointsService $points): array
    {
        $earn = [];
        $spend = [];

        if (! $points->hasTable('point_rules')) {
            return ['earn' => $earn, 'spend' => $spend];
        }

        try {
            $rules = PointRule::query()->active()->orderBy('sort')->get();

            foreach ($rules as $rule) {
                $row = [
                    'code' => (string) $rule->code,
                    'title' => (string) $rule->title,
                    'points' => (string) (int) $rule->points,
                ];

                if ((int) $rule->points < 0) {
                    $spend[] = $row;
                } else {
                    $earn[] = $row;
                }
            }
        } catch (\Throwable) {
            return ['earn' => [], 'spend' => []];
        }

        return ['earn' => $earn, 'spend' => $spend];
    }
}
