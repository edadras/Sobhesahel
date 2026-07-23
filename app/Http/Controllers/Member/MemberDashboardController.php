<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Member;
use Illuminate\Support\Facades\Auth;
use Morilog\Jalali\Jalalian;

/**
 * داشبورد پنل اعضا.
 *
 * Data-provider pattern: every card's data is assembled by a dedicated
 * try/catch provider so a missing phase-2 table (points, missions, ...) or
 * an absent monetization table can never break the page — the card simply
 * falls back to its safe default.
 */
class MemberDashboardController extends Controller
{
    public function index()
    {
        /** @var Member $member */
        $member = Auth::guard('member')->user();

        return view('member.dashboard', [
            'member' => $member,
            'active' => 'dashboard',
            'greeting' => $this->greeting($member),
            'todayJalali' => $this->todayJalali(),
            'subscription' => $this->subscriptionSummary($member),
            'points' => $this->pointsSummary($member),
        ]);
    }

    /**
     * سلام شخصی + وقتِ روز فارسی (صبح/ظهر/عصر/شب).
     */
    protected function greeting(Member $member): string
    {
        $hour = (int) now()->format('G');

        $timeOfDay = match (true) {
            $hour >= 5 && $hour < 12 => 'صبح‌ات به‌خیر ☀️',
            $hour >= 12 && $hour < 15 => 'ظهرت به‌خیر 🌤',
            $hour >= 15 && $hour < 19 => 'عصرت به‌خیر 🌇',
            default => 'شبت به‌خیر 🌙',
        };

        $name = trim((string) $member->first_name);

        return $name !== ''
            ? 'سلام ' . $name . '، ' . $timeOfDay
            : 'سلام، ' . $timeOfDay;
    }

    protected function todayJalali(): string
    {
        try {
            return Jalalian::now()->format('%d %B %Y');
        } catch (\Throwable) {
            return '';
        }
    }

    /**
     * «اشتراک من» — read-only lookup against the existing Subscription model
     * matched by the member's mobile (تداخل ت۱ — Subscription is not modified).
     */
    protected function subscriptionSummary(Member $member): array
    {
        $summary = [
            'has_active' => false,
            'plan_name' => null,
            'days_left' => 0,
            'progress' => 0,
            'renews_at_jalali' => null,
        ];

        try {
            $subscription = $member->activeSubscription();

            if ($subscription === null || $subscription->ends_at === null) {
                return $summary;
            }

            // Signed diff (works on both Carbon 2 and 3 — C3 returns float).
            $daysLeft = max(0, (int) ceil((float) now()->diffInDays($subscription->ends_at, false)));
            $durationDays = max(1, (int) ($subscription->plan?->duration_days ?? 30));

            $summary['has_active'] = true;
            $summary['plan_name'] = $subscription->plan?->name ?? 'اشتراک ویژه';
            $summary['days_left'] = $daysLeft;
            $summary['progress'] = (int) min(100, max(0, round($daysLeft / $durationDays * 100)));
            $summary['renews_at_jalali'] = Jalalian::fromCarbon($subscription->ends_at)->format('%d %B %Y');
        } catch (\Throwable) {
            // Monetization tables absent or unreadable — keep safe defaults.
        }

        return $summary;
    }

    /**
     * امتیاز و سطح — phase-2 placeholders (static zero).
     *
     * PHASE-2 WIRING POINT: when the points/club engine lands (PointsService,
     * point_transactions ledger, levels), replace the static defaults below
     * with real reads inside this same try/catch — the blade already renders
     * whatever this provider returns.
     */
    protected function pointsSummary(Member $member): array
    {
        $summary = [
            'total' => 0,
            'level_name' => null,       // e.g. «برنزی» once phase 2 lands
            'next_level_name' => null,
            'next_level_points' => 0,
            'progress' => 0,
            'saved_news' => 0,          // phase-3 (library/bookmarks)
        ];

        try {
            // Intentionally empty in phase 1 — see PHASE-2 WIRING POINT above.
        } catch (\Throwable) {
            // Never let a missing phase-2 table break the dashboard.
        }

        return $summary;
    }
}
