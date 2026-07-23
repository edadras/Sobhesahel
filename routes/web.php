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
//Route::get('en','Website\IndexController@enIndex')->name('website.ltr.home');

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

//Route::get('en/{type}/{code}/{slug}','Website\PostController@en_single')->name('website.ltr.single')->where('type','news|note|podcast|video|photo');
//Route::post('en/{type}/{code}/{slug}','Website\NewsController@comment')->name('website.ltr.single')->where('type','news|note|podcast|video|photo');

//DONE
Route::get('{type}/{code}','App\Http\Controllers\Website\PostController@redirect')->where('type','news|note|podcast|video|photo')->name('website.rtl.short_single');
//Route::get('en/{type}/{code}','Website\PostController@redirect')->where('type','news|note|podcast|video|photo')->name('website.ltr.short_single');;

//DONE
Route::get('{type}','App\Http\Controllers\Website\PostController@index')->name('website.rtl.index')->where('type','news|note|photo|video|podcast');
//Route::get('en/{type}','Website\PostController@en_index')->name('website.ltr.index')->where('type','news|note|photo|video|podcast');


Route::get('pdf','App\Http\Controllers\Website\ArchiveController@index')->name('website.rtl.archive');
//Route::get('en/pdf','Website\ArchiveController@en_index')->name('website.ltr.archive');
Route::get('pdf/{archive}/{number}','App\Http\Controllers\Website\ArchiveController@pdf')->name('website.rtl.archive.pdf');
Route::get('pdf/api','App\Http\Controllers\Website\ArchiveController@get_data');

//DONE
Route::get('category/{slug}','App\Http\Controllers\Website\CategoryController@index')->name('website.rtl.category');
//Route::get('en/category/{slug}','Website\CategoryController@en_index')->name('website.ltr.category');

//DONE
Route::get('tag/{name}','App\Http\Controllers\Website\TagController@index')->name('website.rtl.tag');
//Route::get('en/tag/{name}','Website\TagController@en_index')->name('website.ltr.tag');

//DONE
Route::get('contact-us','App\Http\Controllers\Website\ContactController@index')->name('website.rtl.contact');
//Route::get('en/contact-us','Website\ContactController@en_index')->name('website.ltr.contact');
Route::post('contact-us','App\Http\Controllers\Website\ContactController@message');

//DONE
Route::get('about-us','App\Http\Controllers\Website\ContactController@about_index')->name('website.rtl.about');
//Route::get('en/about-us','Website\ContactController@en_about_index')->name('website.ltr.about');


Route::get('author/{user_type}/{id}','App\Http\Controllers\Website\AuthorController@index')->name('website.rtl.author');
//Route::get('en/author/{id}','Website\AuthorController@en_index')->name('website.ltr.author');

Route::get('search','App\Http\Controllers\Website\SearchController@index')->name('website.rtl.search');
//Route::get('en/search','App\Http\Controllers\Website\SearchController@en_index')->name('website.ltr.search');
Route::get('search/api','App\Http\Controllers\Website\SearchController@api');
