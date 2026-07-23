<?php

namespace App\Http\Controllers\Api\Member;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Member\Concerns\BuildsMemberPayloads;
use App\Http\Controllers\Api\Member\Concerns\MemberApiResponses;
use App\Models\Member;
use App\Services\Points\ClubService;
use App\Services\Points\PointsService;
use Illuminate\Http\Request;

/**
 * باشگاه اعضا: زنجیره ورود، ماموریت‌ها و چرخ شانس — بازاستفاده از ClubService.
 */
class ClubController extends Controller
{
    use BuildsMemberPayloads;
    use MemberApiResponses;

    public function __construct(
        protected PointsService $points,
        protected ClubService $club,
    ) {
    }

    /**
     * GET /club — {streak, missions, wheel:{free_available, cost}}
     */
    public function index(Request $request)
    {
        /** @var Member $member */
        $member = $request->user();
        $memberId = (int) $member->id;

        return $this->data([
            'streak' => $this->streakPayload($memberId, $this->points),
            'missions' => $this->missionPayloads($memberId, $this->club, $this->points),
            'wheel' => [
                'free_available' => $this->club->freeSpinAvailable($memberId),
                'cost' => $this->club->wheelCost(),
            ],
        ]);
    }

    /**
     * POST /club/wheel/spin — {prize:{title,type,value}, points_balance} یا 422.
     */
    public function spin(Request $request)
    {
        /** @var Member $member */
        $member = $request->user();
        $memberId = (int) $member->id;

        if (! $this->points->hasTable('wheel_prizes') || ! $this->points->hasTable('wheel_spins')) {
            return $this->fail('چرخ شانس در حال حاضر در دسترس نیست.', 422);
        }

        $freeAvailable = $this->club->freeSpinAvailable($memberId);
        $cost = $this->club->wheelCost();

        // اگر چرخش رایگان نداریم و موجودی برای هزینه کافی نیست، پیش از تلاش 422.
        if (! $freeAvailable && $cost > 0 && $this->points->balance($memberId) < $cost) {
            return $this->fail('امتیاز کافی برای چرخش چرخ شانس ندارید.', 422, [
                'points' => ['امتیاز کافی ندارید.'],
            ]);
        }

        $spin = $this->club->spinWheel($memberId, useFreeSpin: true);

        if ($spin === null) {
            return $this->fail('چرخش چرخ شانس ممکن نشد؛ لطفاً دوباره تلاش کنید.', 422);
        }

        $prize = $spin->prize;

        return $this->data([
            'prize' => [
                'title' => (string) ($prize->title ?? 'پوچ'),
                'type' => (string) ($prize->type ?? 'nothing'),
                'value' => $prize !== null ? (string) ($prize->value ?? '') : '',
            ],
            'points_balance' => $this->points->balance($memberId),
        ]);
    }
}
