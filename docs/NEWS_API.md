# قرارداد News API عمومی — اپ خبری صبح ساحل

اپ موبایل صبح ساحل یک **اپ روزنامه خبری** است: بخش اصلی، محتوای عمومی خبری است و **پنل کاربری (باشگاه اعضا) فقط یکی از بخش‌های آن** است. این سند قرارداد API محتوای عمومی (بدون نیاز به ورود) است. پنل کاربری در `docs/MEMBER_API.md` تعریف شده.

- پیشوند: `/api/v1` — پاسخ JSON، `{data, meta?}`.
- زبان: هدر `Accept-Language: fa|en` (پیش‌فرض fa، محتوای lang_id مطابق).
- عمومی (بدون توکن). اگر عضو وارد باشد، توکن Bearer اختیاری برای وضعیت نشان‌کردن (`bookmarked`).

## انواع محتوا و سرویس‌ها (منوها)
- انواع: `news` (اخبار)، `note` (یادداشت‌ها)، `video` (ویدئو)، `podcast` (پادکست)، `photo` (عکس/گالری).
- سرویس‌ها (Category درختی): سیاسی، اقتصاد، اجتماعی، فرهنگی و هنری، ورزشی، شهرستان‌ها، خلیج فارس و… — پویا از دیتابیس.

| متد | مسیر | توضیح |
|---|---|---|
| GET | `/menu` | ساختار منو/سرویس‌ها برای ناوبری اپ: `{data:[{id,title,slug,type,children:[...]}]}` (از Menu/Category) |
| GET | `/home` | صفحه اصلی: `{data:{ breaking:[...], slider:[...featured], latest:[...news], boxes:[{title,slug,items:[...]}], videos:[...], podcasts:[...], galleries:[...], latest_issue:{...archive}, polls:[...], prices:[...], live:[...] }}` — هر بخش گارد‌شده و اختیاری |
| GET | `/content/{type}?page=&category=&tag=&sort=newest\|oldest` | فهرست محتوا بر اساس نوع/سرویس/برچسب: `{data:[card], meta:{pagination}}` |
| GET | `/content/{type}/{code}` | جزئیات یک مطلب (خبر/یادداشت/ویدئو/پادکست/عکس) — پایین ↓ |
| GET | `/category/{slug}?page=` | مطالب یک سرویس |
| GET | `/tag/{name}?page=` | مطالب یک برچسب |
| GET | `/search?q=&type=&category=&from=&to=&author=&sort=` | جستجوی پیشرفته: `{data:{posts:[card], authors:[author_card]}, meta}` (مطابق قابلیت جستجوی سایت) |
| GET | `/authors/{user_type}/{id}` | پروفایل نویسنده + مطالبش |
| GET | `/publications?year=` | آرشیو روزنامه (Archive): `{data:[{id,title,cover_url,date_jalali,type,pdf_url?}], meta}` |
| GET | `/videos?page=` · `/podcasts?page=` · `/galleries?page=` | فهرست‌های چندرسانه‌ای |
| GET | `/prices` | قیمت‌ها (ارز/طلا) گروه‌بندی‌شده |
| GET | `/live` | پخش‌های زنده فعال |
| POST | `/content/{type}/{code}/comment` | ثبت نظر (مهمان یا عضو) |
| GET | `/content/{type}/{code}/comments` | نظرات تأییدشده |

### آبجکت `card` (کارت لیست)
`{id, code, type, title, lead, image_url, category:{title,slug}, author, published_at, published_at_jalali, url, visits, bookmarked}`

### جزئیات مطلب `/content/{type}/{code}`
`{data:{ id, code, type, rotitr, title, lead, body_html, subtitles:[...], image_url, images:[...], media_url?, category, tags:[...], author:{...}, published_at_jalali, visits, related:[card], gallery:[image], video_embed?, audio_url?, show_comments, comments_count, bookmarked, seo:{title,description,image} }}`

## ساختار ناوبری اپ (مهم)
اپ **خبری-محور** است. ناوبری اصلی (bottom nav) پیشنهادی:
1. **خانه** — `/home` (اسلایدر، خبر فوری، آخرین اخبار، باکس سرویس‌ها، چندرسانه‌ای، روزنامه)
2. **سرویس‌ها/دسته‌ها** — از `/menu` (فهرست سرویس‌ها → لیست مطالب)
3. **چندرسانه‌ای** — ویدئو/پادکست/عکس
4. **روزنامه** — آرشیو PDF (`/publications`)
5. **حساب من / پنل کاربری** — ورود به بخش اعضا (`docs/MEMBER_API.md`): داشبورد، باشگاه، امتیاز، اشتراک، فروشگاه، کتابخانه، آرشیو دیجیتال، اعلان‌ها، تنظیمات.

جستجو از هدر در همه صفحات در دسترس است. صفحه جزئیات مطلب از هر لیست باز می‌شود. بخش اعضا (که قبلاً ساخته شده) بدون تغییر، زیر تب «حساب من» قرار می‌گیرد.

## پیاده‌سازی
- منطق از کنترلرها/سرویس‌های موجود سایت (`IndexController`, `PostController`, `CategoryController`, `SearchController/SiteSearchService`, `ArchiveController`, `Video/Podcast/Gallery`, `MarketPrice`, `LiveStream`) بازاستفاده می‌شود.
- بخش‌هایی که هنوز API ندارند با پاسخ گارد‌شده/mock در اپ پوشش داده می‌شوند تا اپ مستقل اجرا شود؛ اتصال واقعی با `useMock=false` و پیاده‌سازی این endpointها.
