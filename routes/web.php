<?php

use App\Models\News;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| صبح ساحل تی‌وی — TV subdomain (زیر دامنه تی‌وی)
|--------------------------------------------------------------------------
| Video-first TV site served on config('app.tv_domain'), falling back to
| "tv." + host of config('app.url') (guarded helper — never breaks on odd
| app.url values). Registered BEFORE the main-domain routes so the TV host
| wins route matching; main-domain requests fail the domain check and fall
| through to the regular site routes below.
| The TV home is ALSO aliased at /tv on the main domain (route tv.alias.home)
| so it stays reachable before any DNS record for the subdomain exists.
*/
Route::domain(\App\Http\Controllers\Tv\TvController::tvDomain())->group(function () {
    Route::get('/', 'App\Http\Controllers\Tv\TvController@home')->name('tv.home');
    Route::get('videos', 'App\Http\Controllers\Tv\TvController@videos')->name('tv.videos');
    Route::get('video/{code}/{slug?}', 'App\Http\Controllers\Tv\TvController@single')->name('tv.single')->where('code', '[0-9]+');
    Route::get('live', 'App\Http\Controllers\Tv\TvController@live')->name('tv.live');
});
// Main-domain alias so the TV home works without DNS setup for the subdomain.
Route::get('tv', 'App\Http\Controllers\Tv\TvController@home')->name('tv.alias.home');

// پخش زنده — public live streams page on the main domain.
Route::get('live', 'App\Http\Controllers\Website\LiveController@index')->name('website.rtl.live');

Route::get('/','App\Http\Controllers\Website\IndexController@newIndex')->name('website.home');

//Route::get('/',function (){
//   dd(\App\Models\Category::find(1)->news()->latest()->get());
//})->name('website.home');

// روت profile حالا ورودی پنل اعضاست (تصمیم ا۶): عضو واردشده به داشبورد و
// مهمان به صفحه ورود اعضا هدایت می‌شود.
Route::get('profile',function (){
    return auth('member')->check()
        ? redirect()->route('member.dashboard')
        : redirect()->route('member.login');
})->name('profile');

Route::get('test',function (){
//    dd(\Illuminate\Support\Facades\Auth::user()->givePermissionTo('view_news'));
    $news = News::search('ساختمان صدا سیما', function ($meilisearch, $query, $options) {
        $options['attributesToSearchOn'] = ['title'];
        return $meilisearch->search($query, $options);
    })->get()->toArray();


    $one_news = News::search('ساختمان صدا سیما')->get()->toArray();


    dd(array_map(fn($item) => $item['title'],$news),array_map(fn($item) => $item['title'],$one_news));
});

// Organization public profiles — must stay registered BEFORE the catch-all {type} routes below.
Route::get('org/{slug}','App\Http\Controllers\Website\OrganizationController@index')->name('website.rtl.organization');
// Market prices page (قیمت‌ها) — must stay registered before the {type} catch-all routes below.
Route::get('prices','App\Http\Controllers\Website\PriceController@index')->name('website.rtl.prices');
// پرسش و پاسخ (Q&A) - must stay BEFORE the catch-all {type} routes below
Route::get('qa','App\Http\Controllers\Website\QaController@index')->name('website.rtl.qa');
Route::post('qa','App\Http\Controllers\Website\QaController@store')->name('website.rtl.qa.store');
Route::get('qa/{id}/{slug?}','App\Http\Controllers\Website\QaController@show')->name('website.rtl.qa.single')->where('id','[0-9]+');

// شهروند خبرنگار (Citizen journalist) - must stay BEFORE the catch-all {type} routes below
Route::get('citizen-report','App\Http\Controllers\Website\CitizenReportController@index')->name('website.rtl.citizen_report');
Route::post('citizen-report','App\Http\Controllers\Website\CitizenReportController@store')->name('website.rtl.citizen_report.store');

// درآمدزایی (monetization): رپورتاژ آگهی + اشتراک ویژه/دیجیتال — these must
// stay registered BEFORE the catch-all {type} routes below.
Route::get('reportage','App\Http\Controllers\Website\ReportageController@index')->name('website.rtl.reportage');
Route::post('reportage','App\Http\Controllers\Website\ReportageController@store')->middleware('throttle:5,10')->name('website.rtl.reportage.store');
Route::get('reportage/pay/{token}','App\Http\Controllers\Website\ReportageController@pay')->name('website.rtl.reportage.pay');
Route::post('reportage/pay/{token}','App\Http\Controllers\Website\ReportageController@submitPayment')->middleware('throttle:10,10')->name('website.rtl.reportage.pay.submit');
Route::get('subscribe','App\Http\Controllers\Website\SubscribeController@index')->name('website.rtl.subscribe');
Route::post('subscribe','App\Http\Controllers\Website\SubscribeController@store')->middleware('throttle:5,10')->name('website.rtl.subscribe.store');
Route::get('subscribe/pay/{token}','App\Http\Controllers\Website\SubscribeController@pay')->name('website.rtl.subscribe.pay');
Route::post('subscribe/pay/{token}','App\Http\Controllers\Website\SubscribeController@submitPayment')->middleware('throttle:10,10')->name('website.rtl.subscribe.pay.submit');
// Access-code unlock for the gated PDF archive (اشتراک دیجیتال نشریات).
Route::post('pdf/access','App\Http\Controllers\Website\SubscribeController@archiveAccess')->middleware('throttle:10,10')->name('website.rtl.archive.access');

/*
|--------------------------------------------------------------------------
| پنل اعضا (member/...) — باشگاه اعضای صبح ساحل (فاز ۱ ناحیه کاربران)
|--------------------------------------------------------------------------
| Membership area on the dedicated `member` guard (separate from the admin
| `web` guard — members can never reach /admin). Login/registration is
| OTP-first with an optional email+password tab; OTP verification
| auto-registers unknown mobiles. Pages that belong to later phases render
| a shared «به‌زودی» view so no sidebar link 404s.
| This block must stay registered BEFORE the catch-all {type} routes below.
*/
Route::prefix('member')->name('member.')->group(function () {
    // Guest-only (an authenticated member is bounced to the dashboard).
    Route::middleware(\App\Http\Middleware\RedirectIfMemberAuthenticated::class)->group(function () {
        Route::get('login', 'App\Http\Controllers\Member\MemberAuthController@showLogin')->name('login');
        Route::post('login/otp', 'App\Http\Controllers\Member\MemberAuthController@sendOtp')->middleware('throttle:10,10')->name('otp.send');
        Route::post('login/otp/verify', 'App\Http\Controllers\Member\MemberAuthController@verifyOtp')->middleware('throttle:15,10')->name('otp.verify');
        Route::get('login/otp/reset', 'App\Http\Controllers\Member\MemberAuthController@resetOtp')->name('otp.reset');
        Route::post('login/password', 'App\Http\Controllers\Member\MemberAuthController@loginWithPassword')->middleware('throttle:10,10')->name('login.password');
    });

    // Authenticated member area.
    Route::middleware(\App\Http\Middleware\MemberAuthenticate::class)->group(function () {
        Route::post('logout', 'App\Http\Controllers\Member\MemberAuthController@logout')->name('logout');

        Route::get('dashboard', 'App\Http\Controllers\Member\MemberDashboardController@index')->name('dashboard');

        Route::get('settings', 'App\Http\Controllers\Member\MemberSettingsController@index')->name('settings');
        Route::post('settings/profile', 'App\Http\Controllers\Member\MemberSettingsController@updateProfile')->name('settings.profile');
        Route::post('settings/password', 'App\Http\Controllers\Member\MemberSettingsController@updatePassword')->name('settings.password');
        Route::post('settings/locale', 'App\Http\Controllers\Member\MemberSettingsController@updateLocale')->name('settings.locale');

        // «به‌زودی» placeholders — every roadmap page resolves (no 404s);
        // later phases replace these routes with the real controllers.
        foreach (array_keys(\App\Http\Controllers\Member\MemberPageController::PAGES) as $memberPage) {
            Route::get($memberPage, 'App\Http\Controllers\Member\MemberPageController@show')
                ->defaults('page', $memberPage)
                ->name($memberPage);
        }
    });
});

//DONE
Route::get('{type}/{code}/{slug}','App\Http\Controllers\Website\PostController@new_single')->name('website.rtl.single')->where('type','news|note|podcast|video|photo');

Route::get('ads/click/{id}','App\Http\Controllers\Website\AdvertiseController@index')->name('advertise_click');
Route::get('ads/impression/{id}','App\Http\Controllers\Website\AdvertiseController@impression')->name('advertise_impression');
Route::get('ads/vast/{id}','App\Http\Controllers\Website\AdvertiseController@vast')->name('advertise_vast');

Route::post('{type}/{code}/{slug}','App\Http\Controllers\Website\NewsController@comment')->where('type','news|note|podcast|video|photo');


//DONE
Route::get('{type}/{code}','App\Http\Controllers\Website\PostController@redirect')->where('type','news|note|podcast|video|photo')->name('website.rtl.short_single');

//DONE
Route::get('{type}','App\Http\Controllers\Website\PostController@index')->name('website.rtl.index')->where('type','news|note|photo|video|podcast');


Route::get('pdf','App\Http\Controllers\Website\ArchiveController@index')->name('website.rtl.archive');
Route::get('pdf/{archive}/{number}','App\Http\Controllers\Website\ArchiveController@pdf')->name('website.rtl.archive.pdf');
Route::get('pdf/api','App\Http\Controllers\Website\ArchiveController@get_data');

//DONE
Route::get('category/{slug}','App\Http\Controllers\Website\CategoryController@index')->name('website.rtl.category');

//DONE
Route::get('tag/{name}','App\Http\Controllers\Website\TagController@index')->name('website.rtl.tag');

//DONE
Route::get('contact-us','App\Http\Controllers\Website\ContactController@index')->name('website.rtl.contact');
Route::post('contact-us','App\Http\Controllers\Website\ContactController@message');

//DONE
Route::get('about-us','App\Http\Controllers\Website\ContactController@about_index')->name('website.rtl.about');


Route::get('author/{user_type}/{id}','App\Http\Controllers\Website\AuthorController@index')->name('website.rtl.author');

Route::get('search','App\Http\Controllers\Website\SearchController@index')->name('website.rtl.search');
Route::get('search/api','App\Http\Controllers\Website\SearchController@api');

/*
|--------------------------------------------------------------------------
| English site (en/...) — چندزبانه (WP-15)
|--------------------------------------------------------------------------
|
| The whole English front-end lives in this single block. It is registered
| only when English is enabled: config('app.enable_english') (env
| APP_ENABLE_ENGLISH) OR an active "en" row in the languages table.
| Language::isEnglishEnabled() is Schema-guarded, so this never queries the
| database before migrations have run.
|
| NOTE: when the English site is toggled, run `php artisan route:clear` if
| routes are cached — cached routes are frozen at cache time.
*/
if (\App\Models\Language::isEnglishEnabled()) {
    // Home
    Route::get('en', 'App\Http\Controllers\Website\IndexController@enIndex')->name('website.ltr.home');

    // Newspaper PDF archive
    Route::get('en/pdf', 'App\Http\Controllers\Website\ArchiveController@en_index')->name('website.ltr.archive');

    // Category / tag listings
    Route::get('en/category/{slug}', 'App\Http\Controllers\Website\CategoryController@en_index')->name('website.ltr.category');
    Route::get('en/tag/{name}', 'App\Http\Controllers\Website\TagController@en_index')->name('website.ltr.tag');

    // Static-ish pages
    Route::get('en/contact-us', 'App\Http\Controllers\Website\ContactController@en_index')->name('website.ltr.contact');
    Route::post('en/contact-us', 'App\Http\Controllers\Website\ContactController@message');
    Route::get('en/about-us', 'App\Http\Controllers\Website\ContactController@en_about_index')->name('website.ltr.about');

    // Author profile
    Route::get('en/author/{id}', 'App\Http\Controllers\Website\AuthorController@en_index')->name('website.ltr.author');

    // Search
    Route::get('en/search', 'App\Http\Controllers\Website\SearchController@en_index')->name('website.ltr.search');

    // Content single / short-link / listings (keep these catch-alls last)
    Route::get('en/{type}/{code}/{slug}', 'App\Http\Controllers\Website\PostController@en_single')->name('website.ltr.single')->where('type', 'news|note|podcast|video|photo');
    Route::post('en/{type}/{code}/{slug}', 'App\Http\Controllers\Website\NewsController@comment')->where('type', 'news|note|podcast|video|photo');
    Route::get('en/{type}/{code}', 'App\Http\Controllers\Website\PostController@redirect')->name('website.ltr.short_single')->where('type', 'news|note|podcast|video|photo');
    Route::get('en/{type}', 'App\Http\Controllers\Website\PostController@en_index')->name('website.ltr.index')->where('type', 'news|note|photo|video|podcast');
}
