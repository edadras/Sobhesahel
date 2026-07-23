<?php

namespace App\Http\Controllers\Api\Member;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Member\Concerns\MemberApiResponses;
use App\Models\Archive;
use App\Models\Member;
use App\Models\PointRule;
use App\Models\PointTransaction;
use App\Services\Points\PointsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Morilog\Jalali\Jalalian;
use Symfony\Component\HttpFoundation\Response;

/**
 * آرشیو دیجیتال روزنامه — دسترسی با اشتراک فعال یا باز‌کردن با امتیاز.
 *
 * باز‌کردن با امتیاز روی همان دفتر کل امتیاز (PointTransaction با rule_code
 * = archive_unlock و reference به شماره آرشیو) ثبت می‌شود؛ نیازی به جدول جدید
 * نیست و دسترسی از روی وجود همان تراکنش تشخیص داده می‌شود.
 */
class ArchiveController extends Controller
{
    use MemberApiResponses;

    public function __construct(protected PointsService $points)
    {
    }

    /**
     * GET /archive?year= — [{id,title,cover_url,date_jalali,type,accessible,unlock_cost}]
     */
    public function index(Request $request)
    {
        if (! Schema::hasTable('archives')) {
            return $this->data([]);
        }

        /** @var Member $member */
        $member = $request->user();

        try {
            $query = Archive::query()
                ->where('is_published', true)
                ->orderByDesc('archive_date');

            $year = $request->filled('year') ? (int) $request->input('year') : null;

            $archives = $query->limit(500)->get();

            $hasActiveSub = $member->activeSubscription() !== null;
            $gate = (bool) config('payments.gate_archive', false);
            $unlockCost = $this->unlockCost();

            $items = $archives
                ->filter(function (Archive $a) use ($year) {
                    if ($year === null) {
                        return true;
                    }

                    return $this->jalaliYear($a) === $year;
                })
                ->map(fn (Archive $a) => $this->archivePayload($a, $member, $hasActiveSub, $gate, $unlockCost))
                ->values()
                ->all();

            return $this->data($items);
        } catch (\Throwable) {
            return $this->data([]);
        }
    }

    /**
     * GET /archive/{id} — {...archive, accessible, unlock_cost}
     */
    public function show(Request $request, int $id)
    {
        if (! Schema::hasTable('archives')) {
            return $this->fail('آرشیو موردنظر یافت نشد.', 404);
        }

        /** @var Member $member */
        $member = $request->user();

        $archive = Archive::query()->where('id', $id)->where('is_published', true)->first();

        if ($archive === null) {
            return $this->fail('آرشیو موردنظر یافت نشد.', 404);
        }

        return $this->data($this->archivePayload(
            $archive,
            $member,
            $member->activeSubscription() !== null,
            (bool) config('payments.gate_archive', false),
            $this->unlockCost(),
        ));
    }

    /**
     * POST /archive/{id}/unlock — {unlocked:true, points_balance} یا 422.
     */
    public function unlock(Request $request, int $id)
    {
        if (! Schema::hasTable('archives')) {
            return $this->fail('آرشیو موردنظر یافت نشد.', 404);
        }

        /** @var Member $member */
        $member = $request->user();
        $memberId = (int) $member->id;

        $archive = Archive::query()->where('id', $id)->where('is_published', true)->first();

        if ($archive === null) {
            return $this->fail('آرشیو موردنظر یافت نشد.', 404);
        }

        // Already accessible (subscription / gating off / previously unlocked).
        if ($this->isAccessible($archive, $member, $member->activeSubscription() !== null, (bool) config('payments.gate_archive', false))) {
            return $this->data([
                'unlocked' => true,
                'points_balance' => $this->points->balance($memberId),
            ]);
        }

        if (! $this->points->ready()) {
            return $this->fail('باز‌کردن با امتیاز در حال حاضر ممکن نیست.', 422);
        }

        $cost = $this->unlockCost();

        if ($cost <= 0) {
            // No cost configured — treat as free unlock but still record it.
            $cost = 0;
        }

        if ($cost > 0 && $this->points->balance($memberId) < $cost) {
            return $this->fail('امتیاز کافی برای باز‌کردن این شماره ندارید.', 422, [
                'points' => ['امتیاز کافی ندارید.'],
            ]);
        }

        $spend = $this->points->spend($memberId, max(1, $cost), 'archive_unlock', $archive, "باز‌کردن آرشیو: {$archive->title}");

        if ($spend === null) {
            return $this->fail('امتیاز کافی برای باز‌کردن این شماره ندارید.', 422);
        }

        return $this->data([
            'unlocked' => true,
            'points_balance' => $this->points->balance($memberId),
        ]);
    }

    /**
     * GET /archive/{id}/pdf — استریم PDF فقط اگر accessible.
     */
    public function pdf(Request $request, int $id): Response
    {
        if (! Schema::hasTable('archives')) {
            return $this->fail('آرشیو موردنظر یافت نشد.', 404);
        }

        /** @var Member $member */
        $member = $request->user();

        $archive = Archive::query()->where('id', $id)->where('is_published', true)->first();

        if ($archive === null) {
            return $this->fail('آرشیو موردنظر یافت نشد.', 404);
        }

        if (! $this->isAccessible($archive, $member, $member->activeSubscription() !== null, (bool) config('payments.gate_archive', false))) {
            return $this->fail('برای دریافت این شماره باید اشتراک فعال داشته باشید یا آن را با امتیاز باز کنید.', 403);
        }

        $path = (string) $archive->archive_file;

        try {
            if ($path !== '' && Storage::disk('public')->exists($path)) {
                return Storage::disk('public')->response($path);
            }

            if ($path !== '' && Storage::disk('media')->exists($path)) {
                return Storage::disk('media')->response($path);
            }
        } catch (\Throwable) {
            // fall through to 404
        }

        return $this->fail('فایل این شماره در دسترس نیست.', 404);
    }

    /**
     * @return array<string, mixed>
     */
    protected function archivePayload(Archive $a, Member $member, bool $hasActiveSub, bool $gate, int $unlockCost): array
    {
        $accessible = $this->isAccessible($a, $member, $hasActiveSub, $gate);

        return [
            'id' => (int) $a->id,
            'title' => (string) $a->title,
            'cover_url' => $this->coverUrl($a),
            'date_jalali' => $this->dateJalali($a),
            'type' => (string) ($a->type ?? 'daily'),
            'accessible' => $accessible,
            'unlock_cost' => $accessible ? 0 : $unlockCost,
        ];
    }

    protected function isAccessible(Archive $a, Member $member, bool $hasActiveSub, bool $gate): bool
    {
        if (! $gate) {
            return true; // gating disabled → archive is free
        }

        if ($hasActiveSub) {
            return true;
        }

        return $this->unlockedViaPoints((int) $member->id, $a);
    }

    protected function unlockedViaPoints(int $memberId, Archive $a): bool
    {
        if (! $this->points->ready()) {
            return false;
        }

        try {
            return PointTransaction::query()
                ->where('member_id', $memberId)
                ->where('rule_code', 'archive_unlock')
                ->where('reference_type', $a->getMorphClass())
                ->where('reference_id', $a->getKey())
                ->exists();
        } catch (\Throwable) {
            return false;
        }
    }

    protected function unlockCost(): int
    {
        try {
            if ($this->points->hasTable('point_rules')) {
                $rule = PointRule::query()->active()->where('code', 'archive_unlock')->first();

                if ($rule && (int) $rule->points !== 0) {
                    return abs((int) $rule->points);
                }
            }
        } catch (\Throwable) {
            // fall back to config
        }

        return max(0, (int) config('points.archive_unlock_cost', 50));
    }

    protected function coverUrl(Archive $a): ?string
    {
        try {
            return $a->getImageUrl('medium');
        } catch (\Throwable) {
            return null;
        }
    }

    protected function dateJalali(Archive $a): string
    {
        try {
            if ($a->archive_date !== null) {
                return Jalalian::fromCarbon(\Illuminate\Support\Carbon::parse($a->archive_date))->format('%d %B %Y');
            }
        } catch (\Throwable) {
            // ignore
        }

        return '';
    }

    protected function jalaliYear(Archive $a): ?int
    {
        try {
            if ($a->archive_date !== null) {
                return (int) Jalalian::fromCarbon(\Illuminate\Support\Carbon::parse($a->archive_date))->getYear();
            }
        } catch (\Throwable) {
            // ignore
        }

        return null;
    }
}
