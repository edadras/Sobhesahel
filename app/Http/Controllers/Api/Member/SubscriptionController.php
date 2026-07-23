<?php

namespace App\Http\Controllers\Api\Member;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Member\Concerns\BuildsMemberPayloads;
use App\Http\Controllers\Api\Member\Concerns\MemberApiResponses;
use App\Http\Resources\Api\Member\SubscriptionPlanResource;
use App\Models\Member;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Services\Payment\PaymentManager;
use App\Services\Points\PointsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Morilog\Jalali\Jalalian;

/**
 * اشتراک ویژه: پلن‌ها، اشتراک فعلی، خرید (نقدی/امتیازی) و تأیید پرداخت.
 *
 * پرداخت از PaymentManager (درایور آفلاین فعلی)، اشتراک از مدل Subscription و
 * خرج امتیاز از PointsService بازاستفاده می‌شود.
 */
class SubscriptionController extends Controller
{
    use BuildsMemberPayloads;
    use MemberApiResponses;

    public function __construct(
        protected PaymentManager $payments,
        protected PointsService $points,
    ) {
    }

    /**
     * GET /subscription — {current?, plans:[...]}
     */
    public function index(Request $request)
    {
        /** @var Member $member */
        $member = $request->user();

        return $this->data([
            'current' => $this->activeSubscriptionPayload($member),
            'plans' => $this->plans($request),
        ]);
    }

    /**
     * POST /subscription/purchase — {plan_id, pay_with}
     */
    public function purchase(Request $request)
    {
        /** @var Member $member */
        $member = $request->user();

        if (! Schema::hasTable('subscription_plans') || ! Schema::hasTable('subscriptions')) {
            return $this->fail('خرید اشتراک در حال حاضر در دسترس نیست.', 422);
        }

        $planId = (int) $request->input('plan_id');
        $payWith = (string) $request->input('pay_with', 'cash');

        $plan = SubscriptionPlan::query()->where('id', $planId)->where('is_active', true)->first();

        if ($plan === null) {
            return $this->fail('پلن انتخاب‌شده معتبر نیست.', 422, ['plan_id' => ['پلن معتبر نیست.']]);
        }

        if ($payWith === 'points') {
            return $this->purchaseWithPoints($member, $plan);
        }

        return $this->purchaseWithCash($member, $plan);
    }

    /**
     * POST /subscription/payment/confirm — {payment_token, ref_code}
     */
    public function confirmPayment(Request $request)
    {
        if (! Schema::hasTable('payments')) {
            return $this->fail('پرداختی برای تأیید یافت نشد.', 422);
        }

        /** @var Member $member */
        $member = $request->user();

        $token = trim((string) $request->input('payment_token'));
        $refCode = trim((string) $request->input('ref_code'));

        if ($token === '' || $refCode === '') {
            return $this->fail('توکن پرداخت و کد رهگیری را وارد کنید.', 422, [
                'ref_code' => ['کد رهگیری را وارد کنید.'],
            ]);
        }

        try {
            $payment = Payment::query()->where('token', $token)->first();

            // Ownership: the payment must belong to this member's subscription.
            if ($payment === null || ! $this->ownsPayment($member, $payment)) {
                return $this->fail('پرداخت موردنظر یافت نشد.', 404);
            }

            $result = $this->payments->verify($payment, ['ref_code' => $refCode]);

            return $this->data([
                'status' => (string) ($result['status'] ?? Payment::STATUS_MANUAL_REVIEW),
                'message' => (string) ($result['message'] ?? ''),
            ]);
        } catch (\Throwable) {
            return $this->fail('ثبت کد رهگیری با خطا مواجه شد؛ لطفاً دوباره تلاش کنید.', 422);
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function plans(Request $request): array
    {
        if (! Schema::hasTable('subscription_plans')) {
            return [];
        }

        try {
            return SubscriptionPlan::query()
                ->where('is_active', true)
                ->orderBy('sort')
                ->get()
                ->map(fn (SubscriptionPlan $p) => (new SubscriptionPlanResource($p))->toArray($request))
                ->all();
        } catch (\Throwable) {
            return [];
        }
    }

    protected function purchaseWithCash(Member $member, SubscriptionPlan $plan)
    {
        try {
            $subscription = Subscription::query()->create([
                'mobile' => $member->mobile,
                'email' => $member->email,
                'plan_id' => $plan->id,
                'status' => Subscription::STATUS_PENDING,
            ]);

            [$payment, $init] = $this->payments->start(
                $subscription,
                (int) $plan->price,
                $member->fullName(),
                $member->mobile,
            );

            return $this->data([
                'payment' => [
                    'token' => (string) $payment->token,
                    'instructions' => $this->instructionsToString($init),
                    'card_info' => $this->cardInfo($init),
                ],
            ]);
        } catch (\Throwable) {
            return $this->fail('ایجاد پرداخت با خطا مواجه شد؛ لطفاً دوباره تلاش کنید.', 422);
        }
    }

    protected function purchaseWithPoints(Member $member, SubscriptionPlan $plan)
    {
        $pointsPrice = $this->planPointsPrice($plan);

        if ($pointsPrice === null || $pointsPrice <= 0) {
            return $this->fail('این پلن با امتیاز قابل خرید نیست.', 422);
        }

        if (! $this->points->ready()) {
            return $this->fail('خرید با امتیاز در حال حاضر ممکن نیست.', 422);
        }

        if ($this->points->balance((int) $member->id) < $pointsPrice) {
            return $this->fail('امتیاز کافی برای خرید این اشتراک ندارید.', 422, [
                'points' => ['امتیاز کافی ندارید.'],
            ]);
        }

        try {
            $subscription = Subscription::query()->create([
                'mobile' => $member->mobile,
                'email' => $member->email,
                'plan_id' => $plan->id,
                'status' => Subscription::STATUS_PENDING,
            ]);

            $spend = $this->points->spend(
                (int) $member->id,
                $pointsPrice,
                'subscription_points',
                $subscription,
                "خرید اشتراک با امتیاز: {$plan->name}",
            );

            if ($spend === null) {
                // Insufficient balance under concurrency — clean up the row.
                $subscription->delete();

                return $this->fail('امتیاز کافی برای خرید این اشتراک ندارید.', 422);
            }

            $subscription->activate();

            try {
                $subscription->sendAccessCodeSms();
            } catch (\Throwable) {
                // SMS is best-effort.
            }

            return $this->data([
                'activated' => true,
                'ends_at_jalali' => $subscription->ends_at !== null
                    ? Jalalian::fromCarbon($subscription->ends_at)->format('%d %B %Y')
                    : null,
                'points_balance' => $this->points->balance((int) $member->id),
            ]);
        } catch (\Throwable) {
            return $this->fail('فعال‌سازی اشتراک با امتیاز با خطا مواجه شد.', 422);
        }
    }

    protected function planPointsPrice(SubscriptionPlan $plan): ?int
    {
        try {
            if (Schema::hasColumn($plan->getTable(), 'points_price') && $plan->points_price !== null) {
                return (int) $plan->points_price;
            }
        } catch (\Throwable) {
            // ignore
        }

        return null;
    }

    protected function ownsPayment(Member $member, Payment $payment): bool
    {
        try {
            $payable = $payment->payable;

            if ($payable instanceof Subscription) {
                return $payable->mobile === $member->mobile;
            }
        } catch (\Throwable) {
            // ignore
        }

        return false;
    }

    /**
     * تبدیل دستورالعمل‌های آفلاین (آرایه) به یک رشته چندخطی برای اپ.
     */
    protected function instructionsToString(array $init): string
    {
        $lines = [];

        foreach ((array) ($init['instructions'] ?? []) as $label => $value) {
            if (filled($value)) {
                $lines[] = is_string($label) ? "{$label}: {$value}" : (string) $value;
            }
        }

        $note = (string) ($init['note'] ?? '');

        if ($note !== '') {
            $lines[] = $note;
        }

        return implode("\n", $lines);
    }

    protected function cardInfo(array $init): ?string
    {
        $instructions = (array) ($init['instructions'] ?? []);

        foreach (['شماره کارت', 'card_number'] as $key) {
            if (filled($instructions[$key] ?? null)) {
                return (string) $instructions[$key];
            }
        }

        $card = (string) config('payments.offline.card_number', '');

        return $card !== '' ? $card : null;
    }
}
