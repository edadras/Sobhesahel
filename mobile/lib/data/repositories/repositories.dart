// Domain repository interfaces for the member area. Each has an API-backed
// implementation (api_repositories.dart) and a Persian mock implementation
// (../mock/mock_repositories.dart). Providers in ../providers.dart pick which
// one to use based on AppConfig.useMock.

import '../models/models.dart';

/// Payment method accepted by purchase endpoints.
enum PayWith { cash, points }

extension PayWithApi on PayWith {
  String get apiValue => this == PayWith.cash ? 'cash' : 'points';
}

abstract class AuthRepository {
  /// POST /auth/otp/request → sends an SMS code. Returns seconds until expiry.
  Future<int> requestOtp(String mobile);

  /// POST /auth/otp/verify → returns token + member (auto-registers).
  Future<AuthResult> verifyOtp({required String mobile, required String code});

  /// POST /auth/password/login.
  Future<AuthResult> passwordLogin({
    required String email,
    required String password,
  });

  /// POST /auth/logout — invalidates the current token.
  Future<void> logout();

  /// GET /auth/me.
  Future<Member> me();
}

abstract class DashboardRepository {
  /// GET /dashboard.
  Future<DashboardData> load();
}

abstract class PointsRepository {
  /// GET /points.
  Future<PointsOverview> overview();

  /// GET /points/transactions?page=.
  Future<Paged<PointTransaction>> transactions({int page = 1});
}

abstract class ClubRepository {
  /// GET /club.
  Future<ClubData> load();

  /// POST /club/wheel/spin.
  Future<WheelPrize> spinWheel();
}

abstract class BadgesRepository {
  /// GET /badges.
  Future<List<Badge>> list();
}

abstract class ShopRepository {
  /// GET /shop/products?sort=.
  Future<List<Product>> products({String sort = 'newest'});

  /// GET /shop/products/{slug}.
  Future<Product> product(String slug);

  /// POST /shop/orders.
  Future<Order> createOrder({
    required int productId,
    int quantity = 1,
    required PayWith payWith,
  });

  /// GET /shop/orders.
  Future<List<Order>> orders();
}

abstract class SubscriptionRepository {
  /// GET /subscription.
  Future<SubscriptionOverview> overview();

  /// POST /subscription/purchase.
  Future<Map<String, dynamic>> purchase({
    required int planId,
    required PayWith payWith,
  });

  /// POST /subscription/payment/confirm.
  Future<void> confirmPayment({
    required String paymentToken,
    required String refCode,
  });
}

abstract class ArchiveRepository {
  /// GET /archive?year=.
  Future<List<ArchiveIssue>> issues({int? year});

  /// GET /archive/{id}.
  Future<ArchiveIssue> issue(int id);

  /// POST /archive/{id}/unlock → returns new points balance.
  Future<int> unlock(int id);
}

abstract class LibraryRepository {
  /// GET /library/bookmarks?type=.
  Future<Paged<Bookmark>> bookmarks({String? type});

  /// POST /library/bookmarks/toggle → returns whether now bookmarked.
  Future<bool> toggleBookmark({required String type, required int id});

  /// GET /library/authors.
  Future<List<Author>> authors();

  /// POST /library/authors/{id}/toggle → returns whether now following.
  Future<bool> toggleAuthor(int id);

  /// GET /library/reading.
  Future<List<ReadingItem>> reading();

  /// POST /library/reading.
  Future<void> updateReading({required int newsId, required int progress});
}

abstract class TourismRepository {
  /// GET /tourism.
  Future<Paged<TourismItem>> feed({int page = 1});
}

abstract class NotificationsRepository {
  /// GET /notifications?filter=all|unread.
  Future<Paged<AppNotification>> list({String filter = 'all'});

  int get unreadCount;

  /// POST /notifications/{id}/read.
  Future<void> markRead(int id);

  /// POST /notifications/read-all.
  Future<void> markAllRead();

  /// GET /notifications/preferences.
  Future<NotificationPreferences> preferences();

  /// PUT /notifications/preferences.
  Future<NotificationPreferences> updatePreferences(
      NotificationPreferences prefs);
}

abstract class SettingsRepository {
  /// PUT /settings/profile.
  Future<Member> updateProfile({
    String? firstName,
    String? lastName,
    String? city,
    String? birthDate,
    String? bio,
    String? email,
  });

  /// POST /settings/avatar → returns new avatar URL.
  Future<String> updateAvatar(String filePath);

  /// PUT /settings/password.
  Future<void> updatePassword({
    String? currentPassword,
    required String password,
    required String passwordConfirmation,
  });

  /// PUT /settings/locale.
  Future<void> updateLocale(String locale);
}
