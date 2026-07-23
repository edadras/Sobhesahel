<?php

use App\Models\News;
use Illuminate\Support\Facades\Route;

Route::get('/','App\Http\Controllers\Website\IndexController@newIndex')->name('website.home');

//Route::get('/',function (){
//   dd(\App\Models\Category::find(1)->news()->latest()->get());
//})->name('website.home');

Route::get('profile',function (){
    return redirect(\route('website.home'));

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

//DONE
Route::get('{type}/{code}/{slug}','App\Http\Controllers\Website\PostController@new_single')->name('website.rtl.single')->where('type','news|note|podcast|video|photo');

Route::get('ads/click/{id}','App\Http\Controllers\Website\AdvertiseController@index')->name('advertise_click');
Route::get('ads/impression/{id}','App\Http\Controllers\Website\AdvertiseController@impression')->name('advertise_impression');
Route::get('ads/vast/{id}','App\Http\Controllers\Website\AdvertiseController@vast')->name('advertise_vast');

Route::post('{type}/{code}/{slug}','Website\NewsController@comment')->where('type','news|note|podcast|video|photo');


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
