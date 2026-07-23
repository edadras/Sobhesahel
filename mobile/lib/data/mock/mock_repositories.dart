// In-memory mock implementations of every repository, returning the Persian
// sample data from mock_data.dart after a short simulated network delay. Used
// when AppConfig.useMock is true so the whole app is demoable offline.

import 'dart:math' as math;

import '../models/models.dart';
import '../repositories/repositories.dart';
import '../token_storage.dart';
import 'mock_data.dart';

Future<T> _delayed<T>(T value, [int ms = 450]) =>
    Future<T>.delayed(Duration(milliseconds: ms), () => value);

class MockAuthRepository implements AuthRepository {
  MockAuthRepository(this._tokens);
  final TokenStorage _tokens;

  @override
  Future<int> requestOtp(String mobile) => _delayed(300);

  @override
  Future<AuthResult> verifyOtp({
    required String mobile,
    required String code,
  }) async {
    // In mock mode any non-empty code is accepted.
    await _tokens.write('mock-token-${DateTime.now().millisecondsSinceEpoch}');
    return _delayed(
      AuthResult(token: _tokens.cachedToken!, member: MockData.member),
    );
  }

  @override
  Future<AuthResult> passwordLogin({
    required String email,
    required String password,
  }) async {
    await _tokens.write('mock-token-${DateTime.now().millisecondsSinceEpoch}');
    return _delayed(
      AuthResult(token: _tokens.cachedToken!, member: MockData.member),
    );
  }

  @override
  Future<void> logout() async {
    await _tokens.clear();
  }

  @override
  Future<Member> me() => _delayed(MockData.member);
}

class MockDashboardRepository implements DashboardRepository {
  @override
  Future<DashboardData> load() => _delayed(MockData.dashboard());
}

class MockPointsRepository implements PointsRepository {
  @override
  Future<PointsOverview> overview() => _delayed(
        PointsOverview(
          balance: 7240,
          level: MockData.goldLevel,
          earnRules: MockData.earnRules(),
          spendRules: MockData.spendRules(),
        ),
      );

  @override
  Future<Paged<PointTransaction>> transactions({int page = 1}) => _delayed(
        Paged<PointTransaction>(
          items: MockData.transactions(),
          currentPage: page,
          lastPage: 1,
        ),
      );
}

class MockClubRepository implements ClubRepository {
  final math.Random _rng = math.Random();
  final List<WheelPrize> _prizes = const <WheelPrize>[
    WheelPrize(title: '۲۰ امتیاز', type: 'points', value: '20', pointsBalance: 7260),
    WheelPrize(title: '۱ روز اشتراک ویژه', type: 'subscription', value: '1', pointsBalance: 7240),
    WheelPrize(title: 'کد تخفیف ۱۰٪', type: 'coupon', value: '10', pointsBalance: 7240),
    WheelPrize(title: '۵۰ امتیاز', type: 'points', value: '50', pointsBalance: 7290),
  ];

  @override
  Future<ClubData> load() => _delayed(
        ClubData(
          streak: MockData.streak,
          missions: MockData.missions(),
          wheel: MockData.wheel,
        ),
      );

  @override
  Future<WheelPrize> spinWheel() =>
      _delayed(_prizes[_rng.nextInt(_prizes.length)], 900);
}

class MockBadgesRepository implements BadgesRepository {
  @override
  Future<List<Badge>> list() => _delayed(MockData.badges());
}

class MockShopRepository implements ShopRepository {
  @override
  Future<List<Product>> products({String sort = 'newest'}) =>
      _delayed(MockData.products());

  @override
  Future<Product> product(String slug) => _delayed(
        MockData.products().firstWhere(
          (p) => p.slug == slug,
          orElse: () => MockData.products().first,
        ),
      );

  @override
  Future<Order> createOrder({
    required int productId,
    int quantity = 1,
    required PayWith payWith,
  }) =>
      _delayed(MockData.orders().first, 800);

  @override
  Future<List<Order>> orders() => _delayed(MockData.orders());
}

class MockSubscriptionRepository implements SubscriptionRepository {
  @override
  Future<SubscriptionOverview> overview() => _delayed(
        SubscriptionOverview(
          current: MockData.currentSub,
          plans: MockData.plans(),
        ),
      );

  @override
  Future<Map<String, dynamic>> purchase({
    required int planId,
    required PayWith payWith,
  }) async {
    if (payWith == PayWith.points) {
      return _delayed(<String, dynamic>{
        'activated': true,
        'ends_at_jalali': '۴ مرداد ۱۴۰۵',
      }, 800);
    }
    return _delayed(<String, dynamic>{
      'payment': <String, dynamic>{
        'token': 'mock-payment-token',
        'instructions': 'مبلغ را به شماره کارت زیر واریز و کد پیگیری را وارد کنید.',
        'card_info': '۶۲۱۹-۸۶۱۹-۰۰۰۰-۱۲۳۴ — بانک سامان',
      },
    }, 800);
  }

  @override
  Future<void> confirmPayment({
    required String paymentToken,
    required String refCode,
  }) =>
      _delayed<void>(null, 700);
}

class MockArchiveRepository implements ArchiveRepository {
  @override
  Future<List<ArchiveIssue>> issues({int? year}) =>
      _delayed(MockData.archive());

  @override
  Future<ArchiveIssue> issue(int id) => _delayed(
        MockData.archive().firstWhere(
          (a) => a.id == id,
          orElse: () => MockData.archive().first,
        ),
      );

  @override
  Future<int> unlock(int id) => _delayed(7190, 700);
}

class MockLibraryRepository implements LibraryRepository {
  @override
  Future<Paged<Bookmark>> bookmarks({String? type}) => _delayed(
        Paged<Bookmark>(items: MockData.bookmarks()),
      );

  @override
  Future<bool> toggleBookmark({required String type, required int id}) =>
      _delayed(false, 300);

  @override
  Future<List<Author>> authors() => _delayed(MockData.authors());

  @override
  Future<bool> toggleAuthor(int id) => _delayed(false, 300);

  @override
  Future<List<ReadingItem>> reading() => _delayed(MockData.reading());

  @override
  Future<void> updateReading({required int newsId, required int progress}) =>
      _delayed<void>(null, 200);
}

class MockTourismRepository implements TourismRepository {
  @override
  Future<Paged<TourismItem>> feed({int page = 1}) => _delayed(
        Paged<TourismItem>(items: MockData.tourism(), currentPage: page),
      );
}

class MockNotificationsRepository implements NotificationsRepository {
  List<AppNotification> _items = MockData.notifications();

  @override
  int get unreadCount => _items.where((n) => !n.read).length;

  @override
  Future<Paged<AppNotification>> list({String filter = 'all'}) {
    final List<AppNotification> filtered = filter == 'unread'
        ? _items.where((n) => !n.read).toList()
        : _items;
    return _delayed(Paged<AppNotification>(items: filtered));
  }

  @override
  Future<void> markRead(int id) async {
    _items = _items
        .map((n) => n.id == id
            ? AppNotification(
                id: n.id,
                title: n.title,
                body: n.body,
                icon: n.icon,
                url: n.url,
                read: true,
                createdAtJalali: n.createdAtJalali,
              )
            : n)
        .toList();
  }

  @override
  Future<void> markAllRead() async {
    _items = _items
        .map((n) => AppNotification(
              id: n.id,
              title: n.title,
              body: n.body,
              icon: n.icon,
              url: n.url,
              read: true,
              createdAtJalali: n.createdAtJalali,
            ))
        .toList();
  }

  @override
  Future<NotificationPreferences> preferences() =>
      _delayed(MockData.notifPrefs);

  @override
  Future<NotificationPreferences> updatePreferences(
          NotificationPreferences prefs) =>
      _delayed(prefs, 300);
}

class MockSettingsRepository implements SettingsRepository {
  @override
  Future<Member> updateProfile({
    String? firstName,
    String? lastName,
    String? city,
    String? birthDate,
    String? bio,
    String? email,
  }) =>
      _delayed(
        MockData.member.copyWith(
          firstName: firstName,
          lastName: lastName,
          city: city,
          birthDate: birthDate,
          bio: bio,
          email: email,
          fullName: (firstName != null || lastName != null)
              ? '${firstName ?? MockData.member.firstName} ${lastName ?? MockData.member.lastName}'
              : null,
        ),
        600,
      );

  @override
  Future<String> updateAvatar(String filePath) => _delayed('', 600);

  @override
  Future<void> updatePassword({
    String? currentPassword,
    required String password,
    required String passwordConfirmation,
  }) =>
      _delayed<void>(null, 600);

  @override
  Future<void> updateLocale(String locale) => _delayed<void>(null, 200);
}
