<?php

namespace App\Http\Controllers\Api\Member;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Member\Concerns\BuildsMemberPayloads;
use App\Http\Controllers\Api\Member\Concerns\MemberApiResponses;
use App\Models\Member;
use App\Services\Points\PointsService;
use Illuminate\Http\Request;

/**
 * نشان‌ها (دستاوردها) عضو.
 */
class BadgeController extends Controller
{
    use BuildsMemberPayloads;
    use MemberApiResponses;

    public function __construct(protected PointsService $points)
    {
    }

    /**
     * GET /badges — [{code,title,description,icon,earned,awarded_at_jalali?,condition_text}]
     */
    public function index(Request $request)
    {
        /** @var Member $member */
        $member = $request->user();

        return $this->data($this->badgePayloads((int) $member->id, $this->points));
    }
}
