<?php

namespace App\Listeners;

use App\Models\Comment;
use App\Models\ContentRating;
use App\Models\PollVote;
use App\Services\Points\ClubService;
use App\Services\Points\PointsService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * اتصال رویدادمحور امتیاز باشگاه (نقشه راه ا۲) — بدون دست‌کاری فایل‌های
 * ماژول‌های نظر/نظرسنجی/امتیازدهی؛ فقط closure روی رویدادهای مدل.
 *
 * فقط زمانی امتیاز می‌دهد که رکورد به یک عضو متصل باشد (ستون member_id).
 * تا پیش از فاز ۳ (افزودن ستون member_id به آن جدول‌ها) همه closure ها
 * بی‌اثر (no-op) هستند و هیچ خطایی تولید نمی‌کنند.
 */
class AwardMemberPoints
{
    /**
     * کش درون‌درخواستی وجود ستون member_id در هر جدول.
     *
     * @var array<string, bool>
     */
    protected static array $columnChecks = [];

    /**
     * ثبت closure ها روی رویدادهای مدل — از boot یک ServiceProvider صدا زده می‌شود.
     */
    public static function register(): void
    {
        // نظر تأییدشده (comment_approved)
        Comment::created(function (Comment $comment) {
            self::safely(function () use ($comment) {
                if (($comment->status ?? null) === 'verified') {
                    self::awardOnceFor($comment, 'comment_approved');
                }
            });
        });

        Comment::updated(function (Comment $comment) {
            self::safely(function () use ($comment) {
                if (
                    ($comment->status ?? null) === 'verified'
                    && $comment->wasChanged('status')
                ) {
                    self::awardOnceFor($comment, 'comment_approved');
                }
            });
        });

        // شرکت در نظرسنجی (poll_vote)
        PollVote::created(function (PollVote $vote) {
            self::safely(fn () => self::awardOnceFor($vote, 'poll_vote'));
        });

        // امتیازدهی به محتوا (rating)
        ContentRating::created(function (ContentRating $rating) {
            self::safely(fn () => self::awardOnceFor($rating, 'rating'));
        });
    }

    /**
     * اهدای امتیاز برای یک رکورد، فقط یک بار به‌ازای هر مرجع، فقط وقتی
     * رکورد به عضو متصل است (ستون member_id موجود و پر باشد).
     */
    protected static function awardOnceFor(Model $record, string $ruleCode): void
    {
        if (! self::tableHasMemberColumn($record->getTable())) {
            return; // فاز ۳ هنوز ستون member_id را اضافه نکرده — no-op
        }

        $memberId = (int) ($record->getAttribute('member_id') ?? 0);

        if ($memberId <= 0) {
            return; // رکورد مهمان — رفتار مهمان‌ها تغییر نمی‌کند
        }

        /** @var PointsService $points */
        $points = app(PointsService::class);

        if (! $points->ready() || $points->alreadyAwardedFor($ruleCode, $record)) {
            return;
        }

        $points->award($memberId, $ruleCode, $record);

        /** @var ClubService $club */
        $club = app(ClubService::class);
        $club->progressMission($memberId, $ruleCode);
        $club->checkBadges($memberId);
    }

    protected static function tableHasMemberColumn(string $tableName): bool
    {
        if (! array_key_exists($tableName, self::$columnChecks)) {
            try {
                self::$columnChecks[$tableName] = Schema::hasColumn($tableName, 'member_id');
            } catch (\Throwable) {
                return false;
            }
        }

        return self::$columnChecks[$tableName];
    }

    /**
     * هیچ خطای باشگاه نباید ثبت نظر/رأی/امتیاز کاربر را خراب کند.
     */
    protected static function safely(callable $callback): void
    {
        try {
            $callback();
        } catch (\Throwable $e) {
            Log::warning('AwardMemberPoints listener failed', ['error' => $e->getMessage()]);
        }
    }
}
