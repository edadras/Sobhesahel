<?php

use App\Http\Controllers\Api\V1\AuthorController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CommentController;
use App\Http\Controllers\Api\V1\ContentController;
use App\Http\Controllers\Api\V1\HomeController;
use App\Http\Controllers\Api\V1\MediaController;
use App\Http\Controllers\Api\V1\MenuController;
use App\Http\Controllers\Api\V1\MiscController;
use App\Http\Controllers\Api\V1\PublicationController;
use App\Http\Controllers\Api\V1\SearchController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public News API — /api/v1/*  (see docs/NEWS_API.md)
|--------------------------------------------------------------------------
|
| Sourced from the real site models/controllers. Public (no token). A bearer
| token, when present, is only used to personalize the `bookmarked` flag —
| everything works for guests. Responses are {data:...} or, for paginated
| lists, {data:[...], meta:{current_page,last_page,per_page,total}}.
|
*/

Route::prefix('v1')->group(function () {
    // Navigation & home.
    Route::get('menu', [MenuController::class, 'index']);
    Route::get('home', [HomeController::class, 'index']);

    // Multimedia listings.
    Route::get('videos', [MediaController::class, 'videos']);
    Route::get('podcasts', [MediaController::class, 'podcasts']);
    Route::get('galleries', [MediaController::class, 'galleries']);

    // Newspaper archive.
    Route::get('publications', [PublicationController::class, 'index']);

    // Prices & live streams.
    Route::get('prices', [MiscController::class, 'prices']);
    Route::get('live', [MiscController::class, 'live']);

    // Search.
    Route::get('search', [SearchController::class, 'index']);

    // Author profile.
    Route::get('authors/{user_type}/{id}', [AuthorController::class, 'show'])
        ->whereNumber('id');

    // Services (categories) & tags.
    Route::get('category/{slug}', [CategoryController::class, 'index']);
    Route::get('tag/{name}', [CategoryController::class, 'tag']);

    // Comments (registered before the generic content/{type}/{code} routes).
    Route::get('content/{type}/{code}/comments', [CommentController::class, 'index']);
    Route::post('content/{type}/{code}/comment', [CommentController::class, 'store']);

    // Content lists & detail.
    Route::get('content/{type}', [ContentController::class, 'index']);
    Route::get('content/{type}/{code}', [ContentController::class, 'show']);
});
