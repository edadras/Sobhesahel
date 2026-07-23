<?php

namespace App\Http\Controllers\Api\Member;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Member\Concerns\BuildsMemberPayloads;
use App\Http\Controllers\Api\Member\Concerns\MemberApiResponses;
use App\Http\Resources\Api\Member\MemberResource;
use App\Http\Resources\Api\Member\PointTransactionResource;
use App\Models\Member;
use App\Models\PointTransaction;
use App\Services\Points\ClubService;
use App\Services\Points\PointsService;
use Illuminate\Http\Request;

/**
 * داشبورد اپ — payload تجمیعی مطابق DashboardData.fromJson.
 *
 * ورود روزانه عضو هنگام بارگذاری داشبورد ثبت می‌شود (ClubService، idempotent در
 * هر روز) تا زنجیره و امتیاز daily_login بدون فلو نشستی زنده بماند.
 */
class DashboardController extends Controller
{
    use BuildsMemberPayloads;
    use MemberApiResponses;

    public function __construct(
        protected PointsService $points,
        protected ClubService $club,
    ) {
    }

    public function index(Request $request)
    {
        /** @var Member $member */
        $member = $request->user();
        $memberId = (int) $member->id;

        // ثبت ورود روزانه (زنجیره + امتیاز daily_login) — کاملاً guarded.
        try {
            $this->club->recordDailyLogin($memberId);
        } catch (\Throwable) {
            // Never let streak bookkeeping break the dashboard.
        }

        $balance = $this->points->balance($memberId);
        $totalEarned = $this->points->totalEarned($memberId);

        return $this->data([
            'member' => (new MemberResource($member))->toArray($request),
            'points' => [
                'balance' => $balance,
                'level' => $this->levelPayload($totalEarned),
            ],
            'streak' => $this->streakPayload($memberId, $this->points),
            'subscription' => $this->activeSubscriptionPayload($member),
            'missions_today' => $this->missionPayloads($memberId, $this->club, $this->points, 'daily'),
            'recent_transactions' => $this->recentTransactions($memberId, $request),
            'badges_count' => $this->badgesCount($memberId),
            'unread_notifications' => $this->unreadNotifications($memberId),
            'today_points_earned' => $this->todayPointsEarned($memberId),
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function recentTransactions(int $memberId, Request $request): array
    {
        if (! $this->points->ready()) {
            return [];
        }

        try {
            return PointTransaction::query()
                ->where('member_id', $memberId)
                ->orderByDesc('id')
                ->limit(3)
                ->get()
                ->map(fn (PointTransaction $t) => (new PointTransactionResource($t))->toArray($request))
                ->all();
        } catch (\Throwable) {
            return [];
        }
    }

    protected function badgesCount(int $memberId): int
    {
        if (! $this->points->hasTable('badge_member')) {
            return 0;
        }

        try {
            return (int) \App\Models\BadgeMember::query()->where('member_id', $memberId)->count();
        } catch (\Throwable) {
            return 0;
        }
    }

    /**
     * اعلان‌های خوانده‌نشده — جدول اعلان اعضا هنوز وجود ندارد؛ گارد‌شده صفر.
     */
    protected function unreadNotifications(int $memberId): int
    {
        return 0;
    }

    protected function todayPointsEarned(int $memberId): int
    {
        if (! $this->points->ready()) {
            return 0;
        }

        try {
            [$start, $end] = $this->points->todayRange();

            return (int) PointTransaction::query()
                ->where('member_id', $memberId)
                ->where('points', '>', 0)
                ->whereBetween('created_at', [$start, $end])
                ->sum('points');
        } catch (\Throwable) {
            return 0;
        }
    }
}
