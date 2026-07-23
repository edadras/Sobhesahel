<?php

namespace App\Services\Points;

use App\Models\PointRule;
use App\Models\PointTransaction;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * موتور امتیاز باشگاه اعضا — دفتر کل (ledger) با موجودی جاری.
 *
 * همه متدها با Schema::hasTable محافظت شده‌اند تا فراخوانی پیش از اجرای
 * migration ها (یا در محیط ناقص) هرگز خطای مهلک تولید نکند.
 */
class PointsService
{
    /**
     * کش بررسی وجود جدول‌ها در طول همین درخواست.
     *
     * @var array<string, bool>
     */
    protected static array $tableChecks = [];

    /**
     * کسب امتیاز بر اساس یک قانون فعال (point_rules).
     *
     * سقف روزانه (daily_cap = حداکثر دفعات در روز به‌وقت Asia/Tehran) per
     * member+rule اعمال می‌شود. در صورت نبود قانون فعال، صفر بودن امتیاز یا
     * پر بودن سقف، null برمی‌گردد.
     */
    public function award(int $memberId, string $ruleCode, ?Model $reference = null, ?string $descriptionOverride = null): ?PointTransaction
    {
        if ($memberId <= 0 || ! $this->ready()) {
            return null;
        }

        try {
            $rule = PointRule::query()->active()->where('code', $ruleCode)->first();

            if (! $rule || (int) $rule->points === 0) {
                return null;
            }

            if ($rule->daily_cap !== null && $rule->daily_cap > 0) {
                [$dayStart, $dayEnd] = $this->todayRange();

                $countToday = PointTransaction::query()
                    ->where('member_id', $memberId)
                    ->where('rule_code', $ruleCode)
                    ->whereBetween('created_at', [$dayStart, $dayEnd])
                    ->count();

                if ($countToday >= $rule->daily_cap) {
                    return null;
                }
            }

            return $this->write(
                $memberId,
                (int) $rule->points,
                $ruleCode,
                $descriptionOverride ?? $rule->title,
                $reference,
            );
        } catch (\Throwable $e) {
            Log::warning('PointsService::award failed', ['member' => $memberId, 'rule' => $ruleCode, 'error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * کسب امتیاز با مقدار صریح (برای ماموریت‌ها و جوایز چرخ شانس که مقدارشان
     * از خود ماموریت/جایزه می‌آید نه از قانون).
     */
    public function awardCustom(int $memberId, string $ruleCode, int $points, ?Model $reference = null, ?string $description = null): ?PointTransaction
    {
        if ($memberId <= 0 || $points <= 0 || ! $this->ready()) {
            return null;
        }

        try {
            return $this->write($memberId, $points, $ruleCode, $description, $reference);
        } catch (\Throwable $e) {
            Log::warning('PointsService::awardCustom failed', ['member' => $memberId, 'rule' => $ruleCode, 'error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * خرج کردن امتیاز — اگر موجودی کافی نباشد null برمی‌گردد و هیچ ردیفی
     * ثبت نمی‌شود.
     */
    public function spend(int $memberId, int $points, string $ruleCode, ?Model $reference = null, ?string $description = null): ?PointTransaction
    {
        $points = abs($points);

        if ($memberId <= 0 || $points === 0 || ! $this->ready()) {
            return null;
        }

        try {
            return DB::transaction(function () use ($memberId, $points, $ruleCode, $reference, $description) {
                $balance = $this->lockedBalance($memberId);

                if ($balance < $points) {
                    return null;
                }

                return $this->insertRow($memberId, -$points, $balance - $points, $ruleCode, $description, $reference);
            });
        } catch (\Throwable $e) {
            Log::warning('PointsService::spend failed', ['member' => $memberId, 'rule' => $ruleCode, 'error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * موجودی فعلی عضو — ۶۰ ثانیه cache می‌شود و با هر تراکنش باطل می‌گردد.
     */
    public function balance(int $memberId): int
    {
        if ($memberId <= 0 || ! $this->ready()) {
            return 0;
        }

        try {
            $ttl = (int) config('points.balance_cache_ttl', 60);

            return (int) Cache::remember(
                $this->balanceCacheKey($memberId),
                $ttl,
                fn () => (int) (PointTransaction::query()
                    ->where('member_id', $memberId)
                    ->orderByDesc('id')
                    ->value('balance_after') ?? 0),
            );
        } catch (\Throwable) {
            return 0;
        }
    }

    /**
     * مجموع امتیازهای کسب‌شده (فقط ردیف‌های مثبت) — برای شرط نشان points_total.
     */
    public function totalEarned(int $memberId): int
    {
        if ($memberId <= 0 || ! $this->ready()) {
            return 0;
        }

        try {
            return (int) PointTransaction::query()
                ->where('member_id', $memberId)
                ->where('points', '>', 0)
                ->sum('points');
        } catch (\Throwable) {
            return 0;
        }
    }

    /**
     * تاریخچه صفحه‌بندی‌شده تراکنش‌های امتیاز عضو (جدیدترین اول).
     */
    public function history(int $memberId, ?int $perPage = null): LengthAwarePaginator
    {
        $perPage = $perPage ?: (int) config('points.history_per_page', 20);

        if ($memberId <= 0 || ! $this->ready()) {
            return new Paginator([], 0, $perPage);
        }

        try {
            return PointTransaction::query()
                ->where('member_id', $memberId)
                ->orderByDesc('id')
                ->paginate($perPage);
        } catch (\Throwable) {
            return new Paginator([], 0, $perPage);
        }
    }

    /**
     * آیا برای این مرجع قبلاً با این قانون امتیاز ثبت شده؟ (جلوگیری از
     * امتیاز تکراری مثلاً هنگام تأیید/لغو/تأیید مجدد یک نظر)
     */
    public function alreadyAwardedFor(string $ruleCode, Model $reference): bool
    {
        if (! $this->ready()) {
            return false;
        }

        try {
            return PointTransaction::query()
                ->where('rule_code', $ruleCode)
                ->where('reference_type', $reference->getMorphClass())
                ->where('reference_id', $reference->getKey())
                ->exists();
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * بازه امروز به‌وقت Asia/Tehran (برای سقف روزانه و چرخش رایگان).
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    public function todayRange(): array
    {
        $tz = (string) config('points.timezone', 'Asia/Tehran');
        $now = Carbon::now($tz);

        return [
            $now->copy()->startOfDay()->setTimezone(config('app.timezone', 'UTC')),
            $now->copy()->endOfDay()->setTimezone(config('app.timezone', 'UTC')),
        ];
    }

    /**
     * آیا جدول‌های موتور امتیاز migrate شده‌اند؟
     */
    public function ready(): bool
    {
        foreach (['point_rules', 'point_transactions'] as $tableName) {
            if (! $this->hasTable($tableName)) {
                return false;
            }
        }

        return true;
    }

    /**
     * بررسی وجود جدول با کش درون‌درخواستی (تا هر فراخوانی award یک کوئری
     * information_schema اضافه نزند).
     */
    public function hasTable(string $tableName): bool
    {
        if (! array_key_exists($tableName, self::$tableChecks)) {
            try {
                self::$tableChecks[$tableName] = Schema::hasTable($tableName);
            } catch (\Throwable) {
                return false;
            }
        }

        return self::$tableChecks[$tableName];
    }

    /**
     * ثبت یک ردیف کسب امتیاز داخل تراکنش دیتابیس با قفل روی آخرین موجودی.
     */
    protected function write(int $memberId, int $points, string $ruleCode, ?string $description, ?Model $reference): PointTransaction
    {
        return DB::transaction(function () use ($memberId, $points, $ruleCode, $description, $reference) {
            $balance = $this->lockedBalance($memberId);

            return $this->insertRow($memberId, $points, $balance + $points, $ruleCode, $description, $reference);
        });
    }

    /**
     * آخرین موجودی عضو با lockForUpdate (باید داخل DB::transaction صدا زده شود).
     */
    protected function lockedBalance(int $memberId): int
    {
        $last = PointTransaction::query()
            ->where('member_id', $memberId)
            ->orderByDesc('id')
            ->lockForUpdate()
            ->first();

        return (int) ($last->balance_after ?? 0);
    }

    protected function insertRow(int $memberId, int $points, int $balanceAfter, string $ruleCode, ?string $description, ?Model $reference): PointTransaction
    {
        $transaction = new PointTransaction([
            'member_id' => $memberId,
            'points' => $points,
            'balance_after' => $balanceAfter,
            'rule_code' => $ruleCode,
            'description' => $description,
            'reference_type' => $reference?->getMorphClass(),
            'reference_id' => $reference?->getKey(),
            'created_at' => now(),
        ]);

        $transaction->save();

        Cache::forget($this->balanceCacheKey($memberId));

        return $transaction;
    }

    protected function balanceCacheKey(int $memberId): string
    {
        return "member:points:balance:{$memberId}";
    }
}
