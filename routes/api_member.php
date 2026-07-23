<?php

// Member area API (پنل کاربری اپ موبایل، Sanctum tokens) — /api/member/*
// See docs/MEMBER_API.md. Loaded from routes/api.php (inherits the /api prefix
// and the `api` middleware group). Public auth routes carry no auth middleware;
// everything else sits behind the member bearer-token middleware.

use App\Http\Controllers\Api\Member\ArchiveController;
use App\Http\Controllers\Api\Member\AuthController;
use App\Http\Controllers\Api\Member\BadgeController;
use App\Http\Controllers\Api\Member\ClubController;
use App\Http\Controllers\Api\Member\DashboardController;
use App\Http\Controllers\Api\Member\LibraryController;
use App\Http\Controllers\Api\Member\NotificationController;
use App\Http\Controllers\Api\Member\PointsController;
use App\Http\Controllers\Api\Member\SettingsController;
use App\Http\Controllers\Api\Member\ShopController;
use App\Http\Controllers\Api\Member\SubscriptionController;
use App\Http\Controllers\Api\Member\TourismController;
use App\Http\Middleware\AuthenticateMember;
use Illuminate\Support\Facades\Route;

Route::prefix('member')->group(function () {

    /* ----------------------------------------------------------------- *
     *  Public auth (no token required)
     * ----------------------------------------------------------------- */
    Route::post('auth/otp/request', [AuthController::class, 'requestOtp']);
    Route::post('auth/otp/verify', [AuthController::class, 'verifyOtp']);
    Route::post('auth/password/login', [AuthController::class, 'passwordLogin']);

    /* ----------------------------------------------------------------- *
     *  Authenticated (member bearer token)
     * ----------------------------------------------------------------- */
    Route::middleware(AuthenticateMember::class)->group(function () {

        // Auth
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('auth/me', [AuthController::class, 'me']);

        // Dashboard
        Route::get('dashboard', [DashboardController::class, 'index']);

        // Points & levels
        Route::get('points', [PointsController::class, 'overview']);
        Route::get('points/transactions/export', [PointsController::class, 'export']);
        Route::get('points/transactions', [PointsController::class, 'transactions']);

        // Club
        Route::get('club', [ClubController::class, 'index']);
        Route::post('club/wheel/spin', [ClubController::class, 'spin']);

        // Badges
        Route::get('badges', [BadgeController::class, 'index']);

        // Subscription
        Route::get('subscription', [SubscriptionController::class, 'index']);
        Route::post('subscription/purchase', [SubscriptionController::class, 'purchase']);
        Route::post('subscription/payment/confirm', [SubscriptionController::class, 'confirmPayment']);

        // Shop (guarded-empty until the shop backend lands)
        Route::get('shop/products', [ShopController::class, 'products']);
        Route::get('shop/products/{slug}', [ShopController::class, 'product']);
        Route::post('shop/orders', [ShopController::class, 'createOrder']);
        Route::get('shop/orders', [ShopController::class, 'orders']);
        Route::get('shop/orders/{id}/download', [ShopController::class, 'download']);

        // Archive
        Route::get('archive', [ArchiveController::class, 'index']);
        Route::get('archive/{id}', [ArchiveController::class, 'show'])->whereNumber('id');
        Route::post('archive/{id}/unlock', [ArchiveController::class, 'unlock'])->whereNumber('id');
        Route::get('archive/{id}/pdf', [ArchiveController::class, 'pdf'])->whereNumber('id');

        // Library (guarded-empty until the member library backend lands)
        Route::get('library/bookmarks', [LibraryController::class, 'bookmarks']);
        Route::post('library/bookmarks/toggle', [LibraryController::class, 'toggleBookmark']);
        Route::get('library/authors', [LibraryController::class, 'authors']);
        Route::post('library/authors/{id}/toggle', [LibraryController::class, 'toggleAuthor'])->whereNumber('id');
        Route::get('library/reading', [LibraryController::class, 'reading']);
        Route::post('library/reading', [LibraryController::class, 'updateReading']);

        // Tourism (real News category feed)
        Route::get('tourism', [TourismController::class, 'feed']);

        // Notifications (guarded-empty until the member notifications backend lands)
        Route::get('notifications', [NotificationController::class, 'index']);
        Route::post('notifications/read-all', [NotificationController::class, 'markAllRead']);
        Route::post('notifications/{id}/read', [NotificationController::class, 'markRead'])->whereNumber('id');
        Route::get('notifications/preferences', [NotificationController::class, 'preferences']);
        Route::put('notifications/preferences', [NotificationController::class, 'updatePreferences']);

        // Settings
        Route::put('settings/profile', [SettingsController::class, 'updateProfile']);
        Route::post('settings/avatar', [SettingsController::class, 'updateAvatar']);
        Route::put('settings/password', [SettingsController::class, 'updatePassword']);
        Route::put('settings/locale', [SettingsController::class, 'updateLocale']);
    });
});
