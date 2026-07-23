<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;

/**
 * صفحات «به‌زودی» پنل اعضا — placeholders for the roadmap pages built in
 * later phases (points, club, ...), so every sidebar item resolves and
 * nothing 404s while the phases land one by one.
 *
 * NOTE: «کتاب‌ها» is intentionally absent everywhere (تصمیم ۵-۲).
 */
class MemberPageController extends Controller
{
    /**
     * page-key => [عنوان, توضیح کوتاه]
     *
     * @var array<string, array{0: string, 1: string}>
     */
    public const PAGES = [
        'points' => ['امتیازها و سطوح', 'راه‌های کسب امتیاز، تاریخچه تراکنش‌ها و سطح‌بندی اعضا'],
        'club' => ['باشگاه مشتریان', 'ماموریت‌های روزانه، زنجیره ورود و چرخ شانس'],
        'badges' => ['نشان‌ها', 'نشان‌ها و دستاوردهای شما در باشگاه اعضا'],
        'shop' => ['فروشگاه دیجیتال', 'محصولات دیجیتال صبح ساحل با پرداخت آنلاین یا امتیاز'],
        'subscription' => ['اشتراک من', 'خرید، تمدید و ارتقای اشتراک ویژه از داخل پنل'],
        'library' => ['مطالعات من', 'اخبار ذخیره‌شده، دنبال‌کردن نویسندگان و ادامه مطالعه'],
        'archive' => ['نسخه دیجیتال روزنامه', 'ورق‌زدن آنلاین و دریافت PDF شماره‌های روزنامه'],
        'tourism' => ['گردشگری هرمزگان', 'راهنمای سفر هرمزگان با قابلیت نشان‌کردن مقاصد'],
        'notifications' => ['اعلان‌ها', 'اعلان‌های حساب، اشتراک و باشگاه اعضا'],
    ];

    public function show(string $page)
    {
        abort_unless(array_key_exists($page, self::PAGES), 404);

        [$title, $description] = self::PAGES[$page];

        return view('member.coming-soon', [
            'active' => $page,
            'pageTitle' => $title,
            'pageDescription' => $description,
        ]);
    }
}
