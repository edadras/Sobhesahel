<?php

use App\Models\News;
use Illuminate\Support\Facades\Route;

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
