// Riverpod wiring for the data layer. Every repository provider returns the
// mock implementation when AppConfig.useMock is true, otherwise the Dio-backed
// one. Feature agents just `ref.watch(<domain>RepositoryProvider)`.

import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../config.dart';
import '../core/providers.dart';
import 'api_client.dart';
import 'mock/mock_news_repository.dart';
import 'mock/mock_repositories.dart';
import 'models/news_models.dart';
import 'news_api_client.dart';
import 'repositories/api_news_repository.dart';
import 'repositories/api_repositories.dart';
import 'repositories/news_repository.dart';
import 'repositories/repositories.dart';
import 'token_storage.dart';

final tokenStorageProvider = Provider<TokenStorage>((ref) => TokenStorage());

final apiClientProvider = Provider<ApiClient>((ref) {
  final ApiClient client =
      ApiClient(tokenStorage: ref.watch(tokenStorageProvider));
  // Keep Accept-Language in sync with the active locale.
  client.localeCode = ref.watch(localeProvider).languageCode;
  ref.listen(localeProvider, (_, next) => client.localeCode = next.languageCode);
  return client;
});

/// Public News-API client (base `/api/v1`). Shares the same token storage so a
/// logged-in member gets `bookmarked` flags, but works fully anonymously.
final newsApiClientProvider = Provider<NewsApiClient>((ref) {
  final NewsApiClient client =
      NewsApiClient(tokenStorage: ref.watch(tokenStorageProvider));
  client.localeCode = ref.watch(localeProvider).languageCode;
  ref.listen(localeProvider, (_, next) => client.localeCode = next.languageCode);
  return client;
});

const bool _mock = AppConfig.useMock;

final authRepositoryProvider = Provider<AuthRepository>((ref) {
  final TokenStorage tokens = ref.watch(tokenStorageProvider);
  return _mock
      ? MockAuthRepository(tokens)
      : ApiAuthRepository(ref.watch(apiClientProvider), tokens);
});

final dashboardRepositoryProvider = Provider<DashboardRepository>((ref) {
  return _mock
      ? MockDashboardRepository()
      : ApiDashboardRepository(ref.watch(apiClientProvider));
});

final pointsRepositoryProvider = Provider<PointsRepository>((ref) {
  return _mock
      ? MockPointsRepository()
      : ApiPointsRepository(ref.watch(apiClientProvider));
});

final clubRepositoryProvider = Provider<ClubRepository>((ref) {
  return _mock
      ? MockClubRepository()
      : ApiClubRepository(ref.watch(apiClientProvider));
});

final badgesRepositoryProvider = Provider<BadgesRepository>((ref) {
  return _mock
      ? MockBadgesRepository()
      : ApiBadgesRepository(ref.watch(apiClientProvider));
});

final shopRepositoryProvider = Provider<ShopRepository>((ref) {
  return _mock
      ? MockShopRepository()
      : ApiShopRepository(ref.watch(apiClientProvider));
});

final subscriptionRepositoryProvider = Provider<SubscriptionRepository>((ref) {
  return _mock
      ? MockSubscriptionRepository()
      : ApiSubscriptionRepository(ref.watch(apiClientProvider));
});

final archiveRepositoryProvider = Provider<ArchiveRepository>((ref) {
  return _mock
      ? MockArchiveRepository()
      : ApiArchiveRepository(ref.watch(apiClientProvider));
});

final libraryRepositoryProvider = Provider<LibraryRepository>((ref) {
  return _mock
      ? MockLibraryRepository()
      : ApiLibraryRepository(ref.watch(apiClientProvider));
});

final tourismRepositoryProvider = Provider<TourismRepository>((ref) {
  return _mock
      ? MockTourismRepository()
      : ApiTourismRepository(ref.watch(apiClientProvider));
});

final notificationsRepositoryProvider =
    Provider<NotificationsRepository>((ref) {
  return _mock
      ? MockNotificationsRepository()
      : ApiNotificationsRepository(ref.watch(apiClientProvider));
});

final settingsRepositoryProvider = Provider<SettingsRepository>((ref) {
  return _mock
      ? MockSettingsRepository()
      : ApiSettingsRepository(ref.watch(apiClientProvider));
});

// ---------------------------------------------------------------------------
// News layer
// ---------------------------------------------------------------------------

final newsRepositoryProvider = Provider<NewsRepository>((ref) {
  return _mock
      ? MockNewsRepository()
      : ApiNewsRepository(ref.watch(newsApiClientProvider));
});

// ---------------------------------------------------------------------------
// Convenience async providers used by the fully-implemented screens.
// ---------------------------------------------------------------------------

final dashboardProvider = FutureProvider.autoDispose((ref) {
  return ref.watch(dashboardRepositoryProvider).load();
});

/// The aggregated home payload.
final homeProvider = FutureProvider.autoDispose<HomePayload>((ref) {
  return ref.watch(newsRepositoryProvider).home();
});

/// The services/category navigation tree.
final menuProvider = FutureProvider.autoDispose<List<MenuItem>>((ref) {
  return ref.watch(newsRepositoryProvider).menu();
});

/// The public newspaper archive.
final publicationsProvider =
    FutureProvider.autoDispose<List<Publication>>((ref) {
  return ref.watch(newsRepositoryProvider).publications();
});

/// Arguments for an article detail lookup.
class ArticleRef {
  const ArticleRef({required this.type, required this.code});
  final String type;
  final String code;

  @override
  bool operator ==(Object other) =>
      other is ArticleRef && other.type == type && other.code == code;

  @override
  int get hashCode => Object.hash(type, code);
}

final articleProvider =
    FutureProvider.autoDispose.family<ArticleDetail, ArticleRef>((ref, r) {
  return ref
      .watch(newsRepositoryProvider)
      .articleDetail(type: r.type, code: r.code);
});
