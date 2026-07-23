<?php

namespace App\Http\Controllers\Api\Member\Concerns;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Morilog\Jalali\Jalalian;

/**
 * کمک‌متدهای مشترک کنترلرهای Member API: قالب پاسخ {data}/{data,meta} و تاریخ
 * شمسی. همه تبدیل‌های تاریخ در try/catch هستند تا یک تاریخ نامعتبر پاسخ را
 * از کار نیندازد.
 */
trait MemberApiResponses
{
    /**
     * قالب پاسخ موفق استاندارد: {data: ...}
     */
    protected function data(mixed $data, int $status = 200): \Illuminate\Http\JsonResponse
    {
        return response()->json(['data' => $data], $status);
    }

    /**
     * خطای استاندارد: {message, errors?}
     */
    protected function fail(string $message, int $status = 422, array $errors = []): \Illuminate\Http\JsonResponse
    {
        $payload = ['message' => $message];

        if ($errors !== []) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $status);
    }

    /**
     * قالب پاسخ صفحه‌بندی‌شده مطابق Paged.fromJson فلاتر:
     * {data:[...], meta:{current_page,last_page,per_page,total}}
     *
     * @param  array<int, mixed>  $items
     */
    protected function paginated(array $items, LengthAwarePaginator $paginator, array $extra = []): \Illuminate\Http\JsonResponse
    {
        return response()->json(array_merge([
            'data' => $items,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ], $extra));
    }

    /**
     * پاسخ صفحه‌بندی‌شده خالیِ امن (برای دامنه‌هایی که هنوز بک‌اند ندارند):
     * {data:[], meta:{...}}
     */
    protected function emptyPaged(array $extra = []): \Illuminate\Http\JsonResponse
    {
        return response()->json(array_merge([
            'data' => [],
            'meta' => [
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => 20,
                'total' => 0,
            ],
        ], $extra));
    }

    /**
     * تاریخ شمسی رشته‌ای برای نمایش (اعداد فارسی سمت اپ فرمت می‌شوند).
     * قالب پیش‌فرض: «۱۵ خرداد ۱۴۰۳».
     */
    protected function jalali(mixed $date, string $format = '%d %B %Y'): ?string
    {
        if (blank($date)) {
            return null;
        }

        try {
            $carbon = $date instanceof \DateTimeInterface
                ? \Illuminate\Support\Carbon::instance($date)
                : \Illuminate\Support\Carbon::parse($date);

            return Jalalian::fromCarbon($carbon)->format($format);
        } catch (\Throwable) {
            return null;
        }
    }
}
