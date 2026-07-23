<?php

namespace App\Http\Controllers\Api\Member;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Member\Concerns\BuildsMemberPayloads;
use App\Http\Controllers\Api\Member\Concerns\MemberApiResponses;
use App\Http\Resources\Api\Member\PointTransactionResource;
use App\Models\Member;
use App\Models\PointTransaction;
use App\Services\Points\PointsService;
use Illuminate\Http\Request;
use Morilog\Jalali\Jalalian;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * امتیازها و سطوح + تاریخچه تراکنش‌ها (صفحه‌بندی‌شده) + خروجی CSV.
 */
class PointsController extends Controller
{
    use BuildsMemberPayloads;
    use MemberApiResponses;

    public function __construct(protected PointsService $points)
    {
    }

    /**
     * GET /points — {balance, level, earn_rules, spend_rules}
     */
    public function overview(Request $request)
    {
        /** @var Member $member */
        $member = $request->user();
        $memberId = (int) $member->id;

        $rules = $this->pointRulePayloads($this->points);

        return $this->data([
            'balance' => $this->points->balance($memberId),
            'level' => $this->levelPayload($this->points->totalEarned($memberId)),
            'earn_rules' => $rules['earn'],
            'spend_rules' => $rules['spend'],
        ]);
    }

    /**
     * GET /points/transactions?page= — صفحه‌بندی‌شده مطابق Paged.fromJson.
     */
    public function transactions(Request $request)
    {
        /** @var Member $member */
        $member = $request->user();
        $memberId = (int) $member->id;

        $paginator = $this->points->history($memberId);

        $items = collect($paginator->items())
            ->map(fn (PointTransaction $t) => (new PointTransactionResource($t))->toArray($request))
            ->all();

        return $this->paginated($items, $paginator);
    }

    /**
     * GET /points/transactions/export — CSV با BOM از تاریخچه خودِ عضو.
     */
    public function export(Request $request): StreamedResponse
    {
        /** @var Member $member */
        $member = $request->user();
        $memberId = (int) $member->id;

        $filename = 'points-history-' . $memberId . '.csv';

        return response()->streamDownload(function () use ($memberId) {
            $out = fopen('php://output', 'w');

            // BOM so Excel reads UTF-8 (Persian) correctly.
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, ['ردیف', 'امتیاز', 'موجودی پس از', 'کد قانون', 'توضیح', 'تاریخ', 'تاریخ شمسی']);

            if (! $this->points->ready()) {
                fclose($out);

                return;
            }

            try {
                PointTransaction::query()
                    ->where('member_id', $memberId)
                    ->orderByDesc('id')
                    ->chunk(200, function ($rows) use ($out) {
                        foreach ($rows as $t) {
                            $jalali = '';

                            try {
                                $jalali = $t->created_at !== null
                                    ? Jalalian::fromCarbon($t->created_at)->format('%Y/%m/%d %H:%M')
                                    : '';
                            } catch (\Throwable) {
                                $jalali = '';
                            }

                            fputcsv($out, [
                                (int) $t->id,
                                (int) $t->points,
                                (int) $t->balance_after,
                                (string) ($t->rule_code ?? ''),
                                (string) ($t->description ?? ''),
                                $t->created_at?->toIso8601String() ?? '',
                                $jalali,
                            ]);
                        }
                    });
            } catch (\Throwable) {
                // Partial CSV is preferable to a 500.
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
