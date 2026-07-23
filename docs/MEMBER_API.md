# قرارداد Member API — اپ موبایل صبح ساحل

API توکنی (Laravel Sanctum، از قبل نصب) برای اپ فلاتر بخش کاربران. همه مسیرها با پیشوند `/api/member` و پاسخ JSON.

- **احراز هویت:** هدر `Authorization: Bearer {token}`. توکن از ورود OTP یا رمز صادر می‌شود (Sanctum personal access token روی مدل `Member`).
- **زبان:** هدر `Accept-Language: fa|en` — پیام‌ها و برچسب‌ها بر اساس آن (پیش‌فرض fa).
- **قالب پاسخ موفق:** `{ "data": ... }` — خطا: `{ "message": "...", "errors": {...}? }` با کد HTTP مناسب (401 عدم احراز، 422 اعتبارسنجی، 429 محدودیت نرخ).
- **اعداد/تاریخ:** مقادیر خام (اعداد لاتین، تاریخ ISO-8601 + یک فیلد `*_jalali` رشته‌ای برای نمایش). فرمت‌بندی فارسی سمت اپ.

## احراز هویت
| متد | مسیر | بدنه | پاسخ |
|---|---|---|---|
| POST | `/auth/otp/request` | `{mobile}` | `{data:{sent:true, expires_in:300}}` — ارسال کد پیامکی (نرخ: ۱ در ۲ دقیقه) |
| POST | `/auth/otp/verify` | `{mobile, code}` | `{data:{token, member, is_new_member}}` — ورود/ثبت‌نام خودکار |
| POST | `/auth/password/login` | `{email, password}` | `{data:{token, member}}` |
| POST | `/auth/logout` | — | `{data:{ok:true}}` (باطل‌کردن توکن جاری) |
| GET | `/auth/me` | — | `{data: member}` |

**آبجکت `member`:** `{id, first_name, last_name, full_name, mobile, email, avatar_url, city, birth_date, bio, locale, is_active, created_at}`

## داشبورد
| GET | `/dashboard` | `{data:{ member, points:{balance,level:{key,title,progress_percent,next_threshold}}, streak:{current_days,longest_days,next_milestone}, subscription:{active,plan_title,ends_at,ends_at_jalali,days_left}?, missions_today:[...], recent_transactions:[...3], badges_count, unread_notifications }}` |

## امتیازها و سطوح
| GET | `/points` | `{data:{balance, level, earn_rules:[{code,title,points}], spend_rules:[{code,title,points}] }}` |
| GET | `/points/transactions?page=` | صفحه‌بندی‌شده: `{data:[{id,points,balance_after,rule_code,description,created_at,created_at_jalali}], meta:{...}}` |
| GET | `/points/transactions/export` | فایل CSV (BOM) — دانلود تاریخچه خودِ عضو |

## باشگاه
| GET | `/club` | `{data:{ streak, missions:[{code,title,points,progress,goal,completed}], wheel:{free_available,cost} }}` |
| POST | `/club/wheel/spin` | `{data:{prize:{title,type,value}, points_balance}}` یا 422 با پیام کمبود امتیاز |
| GET | `/badges` | `{data:[{code,title,description,icon,earned,awarded_at_jalali?,condition_text}]}` |

## اشتراک
| GET | `/subscription` | `{data:{ current:{plan_title,ends_at_jalali,days_left,status}?, plans:[{id,name,duration_days,price,points_price?,features:[...],badge?}] }}` |
| POST | `/subscription/purchase` | `{plan_id, pay_with: "cash"|"points"}` → cash: `{data:{payment:{token,instructions,card_info}}}` · points: `{data:{activated:true, ends_at_jalali}}` یا 422 |
| POST | `/subscription/payment/confirm` | `{payment_token, ref_code}` → `{data:{status:"manual_review"}}` |

## فروشگاه
| GET | `/shop/products?sort=` | `{data:[{id,title,slug,image_url,description,price,points_price?,type,in_stock}], meta}` |
| GET | `/shop/products/{slug}` | `{data: product}` |
| POST | `/shop/orders` | `{product_id, quantity?, pay_with}` → مانند خرید اشتراک (cash/points) |
| GET | `/shop/orders` | سفارش‌های عضو: `{data:[{id,product_title,amount,paid_with,status,created_at_jalali,download_url?}]}` |
| GET | `/shop/orders/{id}/download` | استریم فایل دیجیتال (فقط پرداخت‌شده و متعلق به عضو) |

## آرشیو دیجیتال روزنامه
| GET | `/archive?year=` | `{data:[{id,title,cover_url,date_jalali,type,accessible,unlock_cost}]}` |
| GET | `/archive/{id}` | `{data:{...archive, accessible, unlock_cost}}` |
| POST | `/archive/{id}/unlock` | با امتیاز باز می‌کند: `{data:{unlocked:true, points_balance}}` یا 422 |
| GET | `/archive/{id}/pdf` | استریم PDF (فقط اگر accessible: اشتراک فعال یا unlock‌شده) |

## کتابخانه (بدون بخش کتاب‌ها طبق تصمیم کارفرما)
| GET | `/library/bookmarks?type=` | `{data:[{id, bookmarkable:{type,id,title,image_url,url,date_jalali}}], meta}` |
| POST | `/library/bookmarks/toggle` | `{type, id}` → `{data:{bookmarked:bool}}` |
| GET | `/library/authors` | نویسندگان دنبال‌شده: `{data:[{id,name,type,avatar_url,url}]}` |
| POST | `/library/authors/{id}/toggle` | `{data:{following:bool}}` |
| GET | `/library/reading` | ادامه مطالعه: `{data:[{news:{...}, progress_percent, updated_at_jalali}]}` |
| POST | `/library/reading` | `{news_id, progress_percent}` → ثبت پیشرفت |

## گردشگری
| GET | `/tourism` | فید محتوای سرویس/برچسب گردشگری: `{data:[{id,title,image_url,excerpt,url,bookmarked}], meta}` |

## اعلان‌ها
| GET | `/notifications?filter=all\|unread` | `{data:[{id,title,body,icon,url,read,created_at_jalali}], meta, unread_count}` |
| POST | `/notifications/{id}/read` | `{data:{ok:true}}` |
| POST | `/notifications/read-all` | `{data:{ok:true}}` |
| GET/PUT | `/notifications/preferences` | `{data:{points:bool, subscription:bool, announcements:bool}}` |

## تنظیمات
| PUT | `/settings/profile` | `{first_name,last_name,city,birth_date,bio,email}` → `{data: member}` |
| POST | `/settings/avatar` | multipart `avatar` → `{data:{avatar_url}}` |
| PUT | `/settings/password` | `{current_password?, password, password_confirmation}` |
| PUT | `/settings/locale` | `{locale: fa|en}` |

## توضیحات پیاده‌سازی
- توکن‌ها روی مدل `Member` با `HasApiTokens` (Sanctum). گارد API از `auth:sanctum` با provider members استفاده می‌کند یا میدل‌ور اختصاصی که `Member` را از توکن حل می‌کند.
- منطق OTP، امتیاز، اشتراک، آرشیو و... از سرویس‌های موجود (`MemberOtpService`, `PointsService`, `ClubService`, `PaymentManager`, `Subscription`, `Archive`) بازاستفاده می‌شود — بدون منطق موازی.
- امکاناتی که بک‌اندشان هنوز کامل نیست (فروشگاه/کتابخانه/آرشیو عضو) با پاسخ‌های گارد‌شده و خالیِ امن ارائه می‌شوند تا اپ هرگز کرش نکند و بعداً پر شوند.
