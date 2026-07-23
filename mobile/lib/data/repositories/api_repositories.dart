// Dio-backed implementations of every domain repository. Used when
// AppConfig.useMock is false. Method bodies map directly to the endpoints in
// docs/MEMBER_API.md.

import '../api_client.dart';
import '../models/models.dart';
import '../token_storage.dart';
import 'repositories.dart';

Map<String, dynamic> _asMap(dynamic v) =>
    (v as Map?)?.cast<String, dynamic>() ?? const <String, dynamic>{};

List<Map<String, dynamic>> _asList(dynamic v) => (v is List)
    ? v.whereType<Map>().map((e) => e.cast<String, dynamic>()).toList()
    : const <Map<String, dynamic>>[];

Paged<T> _paged<T>(
  Map<String, dynamic> env,
  T Function(Map<String, dynamic>) parse,
) {
  final Map<String, dynamic> meta = _asMap(env['meta']);
  return Paged<T>(
    items: _asList(env['data']).map(parse).toList(),
    currentPage: (meta['current_page'] as num?)?.toInt() ?? 1,
    lastPage: (meta['last_page'] as num?)?.toInt() ?? 1,
  );
}

class ApiAuthRepository implements AuthRepository {
  ApiAuthRepository(this._api, this._tokens);
  final ApiClient _api;
  final TokenStorage _tokens;

  @override
  Future<int> requestOtp(String mobile) async {
    final dynamic data =
        await _api.postData('/auth/otp/request', body: {'mobile': mobile});
    return (_asMap(data)['expires_in'] as num?)?.toInt() ?? 300;
  }

  @override
  Future<AuthResult> verifyOtp({
    required String mobile,
    required String code,
  }) async {
    final dynamic data = await _api
        .postData('/auth/otp/verify', body: {'mobile': mobile, 'code': code});
    final AuthResult result = AuthResult.fromJson(_asMap(data));
    await _tokens.write(result.token);
    return result;
  }

  @override
  Future<AuthResult> passwordLogin({
    required String email,
    required String password,
  }) async {
    final dynamic data = await _api.postData('/auth/password/login',
        body: {'email': email, 'password': password});
    final AuthResult result = AuthResult.fromJson(_asMap(data));
    await _tokens.write(result.token);
    return result;
  }

  @override
  Future<void> logout() async {
    try {
      await _api.postData('/auth/logout');
    } finally {
      await _tokens.clear();
    }
  }

  @override
  Future<Member> me() async {
    final dynamic data = await _api.getData('/auth/me');
    return Member.fromJson(_asMap(data));
  }
}

class ApiDashboardRepository implements DashboardRepository {
  ApiDashboardRepository(this._api);
  final ApiClient _api;

  @override
  Future<DashboardData> load() async {
    final dynamic data = await _api.getData('/dashboard');
    return DashboardData.fromJson(_asMap(data));
  }
}

class ApiPointsRepository implements PointsRepository {
  ApiPointsRepository(this._api);
  final ApiClient _api;

  @override
  Future<PointsOverview> overview() async {
    final dynamic data = await _api.getData('/points');
    return PointsOverview.fromJson(_asMap(data));
  }

  @override
  Future<Paged<PointTransaction>> transactions({int page = 1}) async {
    final Map<String, dynamic> env =
        await _api.getEnvelope('/points/transactions', query: {'page': page});
    return _paged(env, PointTransaction.fromJson);
  }
}

class ApiClubRepository implements ClubRepository {
  ApiClubRepository(this._api);
  final ApiClient _api;

  @override
  Future<ClubData> load() async {
    final dynamic data = await _api.getData('/club');
    return ClubData.fromJson(_asMap(data));
  }

  @override
  Future<WheelPrize> spinWheel() async {
    final dynamic data = await _api.postData('/club/wheel/spin');
    return WheelPrize.fromJson(_asMap(data));
  }
}

class ApiBadgesRepository implements BadgesRepository {
  ApiBadgesRepository(this._api);
  final ApiClient _api;

  @override
  Future<List<Badge>> list() async {
    final dynamic data = await _api.getData('/badges');
    return _asList(data).map(Badge.fromJson).toList();
  }
}

class ApiShopRepository implements ShopRepository {
  ApiShopRepository(this._api);
  final ApiClient _api;

  @override
  Future<List<Product>> products({String sort = 'newest'}) async {
    final Map<String, dynamic> env =
        await _api.getEnvelope('/shop/products', query: {'sort': sort});
    return _asList(env['data']).map(Product.fromJson).toList();
  }

  @override
  Future<Product> product(String slug) async {
    final dynamic data = await _api.getData('/shop/products/$slug');
    return Product.fromJson(_asMap(data));
  }

  @override
  Future<Order> createOrder({
    required int productId,
    int quantity = 1,
    required PayWith payWith,
  }) async {
    final dynamic data = await _api.postData('/shop/orders', body: {
      'product_id': productId,
      'quantity': quantity,
      'pay_with': payWith.apiValue,
    });
    return Order.fromJson(_asMap(data));
  }

  @override
  Future<List<Order>> orders() async {
    final dynamic data = await _api.getData('/shop/orders');
    return _asList(data).map(Order.fromJson).toList();
  }
}

class ApiSubscriptionRepository implements SubscriptionRepository {
  ApiSubscriptionRepository(this._api);
  final ApiClient _api;

  @override
  Future<SubscriptionOverview> overview() async {
    final dynamic data = await _api.getData('/subscription');
    return SubscriptionOverview.fromJson(_asMap(data));
  }

  @override
  Future<Map<String, dynamic>> purchase({
    required int planId,
    required PayWith payWith,
  }) async {
    final dynamic data = await _api.postData('/subscription/purchase',
        body: {'plan_id': planId, 'pay_with': payWith.apiValue});
    return _asMap(data);
  }

  @override
  Future<void> confirmPayment({
    required String paymentToken,
    required String refCode,
  }) async {
    await _api.postData('/subscription/payment/confirm',
        body: {'payment_token': paymentToken, 'ref_code': refCode});
  }
}

class ApiArchiveRepository implements ArchiveRepository {
  ApiArchiveRepository(this._api);
  final ApiClient _api;

  @override
  Future<List<ArchiveIssue>> issues({int? year}) async {
    final dynamic data = await _api
        .getData('/archive', query: year == null ? null : {'year': year});
    return _asList(data).map(ArchiveIssue.fromJson).toList();
  }

  @override
  Future<ArchiveIssue> issue(int id) async {
    final dynamic data = await _api.getData('/archive/$id');
    return ArchiveIssue.fromJson(_asMap(data));
  }

  @override
  Future<int> unlock(int id) async {
    final dynamic data = await _api.postData('/archive/$id/unlock');
    return (_asMap(data)['points_balance'] as num?)?.toInt() ?? 0;
  }
}

class ApiLibraryRepository implements LibraryRepository {
  ApiLibraryRepository(this._api);
  final ApiClient _api;

  @override
  Future<Paged<Bookmark>> bookmarks({String? type}) async {
    final Map<String, dynamic> env = await _api.getEnvelope(
        '/library/bookmarks',
        query: type == null ? null : {'type': type});
    return _paged(env, Bookmark.fromJson);
  }

  @override
  Future<bool> toggleBookmark({required String type, required int id}) async {
    final dynamic data = await _api
        .postData('/library/bookmarks/toggle', body: {'type': type, 'id': id});
    return (_asMap(data)['bookmarked'] as bool?) ?? false;
  }

  @override
  Future<List<Author>> authors() async {
    final dynamic data = await _api.getData('/library/authors');
    return _asList(data).map(Author.fromJson).toList();
  }

  @override
  Future<bool> toggleAuthor(int id) async {
    final dynamic data = await _api.postData('/library/authors/$id/toggle');
    return (_asMap(data)['following'] as bool?) ?? false;
  }

  @override
  Future<List<ReadingItem>> reading() async {
    final dynamic data = await _api.getData('/library/reading');
    return _asList(data).map(ReadingItem.fromJson).toList();
  }

  @override
  Future<void> updateReading({
    required int newsId,
    required int progress,
  }) async {
    await _api.postData('/library/reading',
        body: {'news_id': newsId, 'progress_percent': progress});
  }
}

class ApiTourismRepository implements TourismRepository {
  ApiTourismRepository(this._api);
  final ApiClient _api;

  @override
  Future<Paged<TourismItem>> feed({int page = 1}) async {
    final Map<String, dynamic> env =
        await _api.getEnvelope('/tourism', query: {'page': page});
    return _paged(env, TourismItem.fromJson);
  }
}

class ApiNotificationsRepository implements NotificationsRepository {
  ApiNotificationsRepository(this._api);
  final ApiClient _api;
  int _unread = 0;

  @override
  int get unreadCount => _unread;

  @override
  Future<Paged<AppNotification>> list({String filter = 'all'}) async {
    final Map<String, dynamic> env =
        await _api.getEnvelope('/notifications', query: {'filter': filter});
    _unread = (env['unread_count'] as num?)?.toInt() ?? _unread;
    return _paged(env, AppNotification.fromJson);
  }

  @override
  Future<void> markRead(int id) async {
    await _api.postData('/notifications/$id/read');
    if (_unread > 0) _unread--;
  }

  @override
  Future<void> markAllRead() async {
    await _api.postData('/notifications/read-all');
    _unread = 0;
  }

  @override
  Future<NotificationPreferences> preferences() async {
    final dynamic data = await _api.getData('/notifications/preferences');
    return NotificationPreferences.fromJson(_asMap(data));
  }

  @override
  Future<NotificationPreferences> updatePreferences(
      NotificationPreferences prefs) async {
    final dynamic data = await _api.putData('/notifications/preferences',
        body: prefs.toJson());
    return NotificationPreferences.fromJson(_asMap(data));
  }
}

class ApiSettingsRepository implements SettingsRepository {
  ApiSettingsRepository(this._api);
  final ApiClient _api;

  @override
  Future<Member> updateProfile({
    String? firstName,
    String? lastName,
    String? city,
    String? birthDate,
    String? bio,
    String? email,
  }) async {
    final dynamic data = await _api.putData('/settings/profile', body: {
      if (firstName != null) 'first_name': firstName,
      if (lastName != null) 'last_name': lastName,
      if (city != null) 'city': city,
      if (birthDate != null) 'birth_date': birthDate,
      if (bio != null) 'bio': bio,
      if (email != null) 'email': email,
    });
    return Member.fromJson(_asMap(data));
  }

  @override
  Future<String> updateAvatar(String filePath) async {
    // Multipart upload; feature agents can wire an image picker to filePath.
    final dynamic data = await _api.postData('/settings/avatar', body: {
      'avatar': filePath,
    });
    return (_asMap(data)['avatar_url'] as String?) ?? '';
  }

  @override
  Future<void> updatePassword({
    String? currentPassword,
    required String password,
    required String passwordConfirmation,
  }) async {
    await _api.putData('/settings/password', body: {
      if (currentPassword != null) 'current_password': currentPassword,
      'password': password,
      'password_confirmation': passwordConfirmation,
    });
  }

  @override
  Future<void> updateLocale(String locale) async {
    await _api.putData('/settings/locale', body: {'locale': locale});
  }
}
