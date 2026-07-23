import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import 'features/archive/archive_screen.dart';
import 'features/auth/auth_controller.dart';
import 'features/auth/login_screen.dart';
import 'features/badges/badges_screen.dart';
import 'features/club/club_screen.dart';
import 'features/dashboard/dashboard_screen.dart';
import 'features/library/library_screen.dart';
import 'features/news/account/account_screen.dart';
import 'features/news/article/article_detail_screen.dart';
import 'features/news/author/author_screen.dart';
import 'features/news/categories/categories_screen.dart';
import 'features/news/content/content_list_screen.dart';
import 'features/news/home/home_screen.dart';
import 'features/news/multimedia/multimedia_hub_screen.dart';
import 'features/news/publications/publications_screen.dart';
import 'features/news/search/search_screen.dart';
import 'features/notifications/notifications_screen.dart';
import 'features/points/points_screen.dart';
import 'features/settings/settings_screen.dart';
import 'features/shop/shop_detail_screen.dart';
import 'features/shop/shop_screen.dart';
import 'features/subscription/subscription_screen.dart';
import 'features/tourism/tourism_screen.dart';
import 'widgets/app_shell.dart';

final _rootKey = GlobalKey<NavigatorState>(debugLabel: 'root');
final _shellKey = GlobalKey<NavigatorState>(debugLabel: 'shell');

/// Member-area paths that require authentication. Every other route (the whole
/// public news experience) is reachable as a guest.
const List<String> _memberPaths = <String>[
  '/dashboard',
  '/club',
  '/points',
  '/badges',
  '/shop',
  '/subscription',
  '/library',
  '/archive',
  '/tourism',
  '/notifications',
  '/settings',
];

bool _requiresAuth(String loc) =>
    _memberPaths.any((String p) => loc == p || loc.startsWith('$p/'));

/// The app router. News-first: a [StatefulShellRoute.indexedStack] drives the
/// five primary tabs (Home, Services, Multimedia, Newspaper, Account). The
/// member area lives under the Account tab as pushed routes, gated by auth.
/// The public news experience is always accessible without login.
final routerProvider = Provider<GoRouter>((ref) {
  // Bridge Riverpod auth changes into a Listenable go_router can refresh on.
  final ValueNotifier<int> refresh = ValueNotifier<int>(0);
  ref.listen(authControllerProvider, (_, __) => refresh.value++);
  ref.onDispose(refresh.dispose);

  return GoRouter(
    navigatorKey: _rootKey,
    initialLocation: '/home',
    refreshListenable: refresh,
    redirect: (BuildContext context, GoRouterState state) {
      final AuthState auth = ref.read(authControllerProvider);
      final String loc = state.matchedLocation;
      final bool onSplash = loc == '/splash';
      final bool onLogin = loc == '/login';

      // Still bootstrapping the session → hold on the splash.
      if (auth.status == AuthStatus.unknown) {
        return onSplash ? null : '/splash';
      }

      // Session resolved: never sit on the splash.
      if (onSplash) return '/home';

      // Member-area routes require a logged-in member.
      if (_requiresAuth(loc) && !auth.isAuthenticated) {
        return '/login';
      }

      // A logged-in member has no reason to see the login screen.
      if (onLogin && auth.isAuthenticated) return '/account';

      return null;
    },
    routes: <RouteBase>[
      GoRoute(
        path: '/splash',
        builder: (_, __) => const SplashScreen(),
      ),
      GoRoute(
        path: '/login',
        parentNavigatorKey: _rootKey,
        builder: (_, __) => const LoginScreen(),
      ),

      // Primary news-first tabs.
      StatefulShellRoute.indexedStack(
        parentNavigatorKey: _rootKey,
        builder: (_, __, StatefulNavigationShell shell) =>
            AppShell(navigationShell: shell),
        branches: <StatefulShellBranch>[
          StatefulShellBranch(
            navigatorKey: _shellKey,
            routes: <RouteBase>[
              GoRoute(
                path: '/home',
                builder: (_, __) => const HomeScreen(),
              ),
            ],
          ),
          StatefulShellBranch(
            routes: <RouteBase>[
              GoRoute(
                path: '/categories',
                builder: (_, __) => const CategoriesScreen(),
              ),
            ],
          ),
          StatefulShellBranch(
            routes: <RouteBase>[
              GoRoute(
                path: '/multimedia',
                builder: (_, __) => const MultimediaHubScreen(),
              ),
            ],
          ),
          StatefulShellBranch(
            routes: <RouteBase>[
              GoRoute(
                path: '/publications',
                builder: (_, __) => const PublicationsScreen(),
              ),
            ],
          ),
          StatefulShellBranch(
            routes: <RouteBase>[
              GoRoute(
                path: '/account',
                builder: (_, __) => const AccountScreen(),
              ),
            ],
          ),
        ],
      ),

      // --- Public news secondary screens (pushed over the shell) ---
      GoRoute(
        path: '/article/:type/:code',
        parentNavigatorKey: _rootKey,
        builder: (_, GoRouterState state) => ArticleDetailScreen(
          type: state.pathParameters['type'] ?? 'news',
          code: state.pathParameters['code'] ?? '',
        ),
      ),
      GoRoute(
        path: '/content/:type',
        parentNavigatorKey: _rootKey,
        builder: (_, GoRouterState state) => ContentListScreen(
          args: ContentListArgs(
            type: state.pathParameters['type'] ?? 'news',
            category: state.uri.queryParameters['category'],
            title: state.uri.queryParameters['title'] ??
                _defaultTypeTitle(state.pathParameters['type']),
          ),
        ),
      ),
      GoRoute(
        path: '/category/:slug',
        parentNavigatorKey: _rootKey,
        builder: (_, GoRouterState state) => ContentListScreen(
          args: ContentListArgs(
            categorySlug: state.pathParameters['slug'],
            title: state.uri.queryParameters['title'] ?? 'سرویس',
          ),
        ),
      ),
      GoRoute(
        path: '/tag/:name',
        parentNavigatorKey: _rootKey,
        builder: (_, GoRouterState state) => ContentListScreen(
          args: ContentListArgs(
            tag: state.pathParameters['name'],
            title: '#${state.pathParameters['name'] ?? ''}',
          ),
        ),
      ),
      GoRoute(
        path: '/search',
        parentNavigatorKey: _rootKey,
        builder: (_, __) => const SearchScreen(),
      ),
      GoRoute(
        path: '/author/:userType/:id',
        parentNavigatorKey: _rootKey,
        builder: (_, GoRouterState state) => AuthorScreen(
          userType: state.pathParameters['userType'] ?? 'author',
          id: int.tryParse(state.pathParameters['id'] ?? '') ?? 0,
        ),
      ),

      // --- Member-area screens (under the Account tab), pushed over shell ---
      GoRoute(
        path: '/dashboard',
        parentNavigatorKey: _rootKey,
        builder: (_, __) => const DashboardScreen(),
      ),
      GoRoute(
        path: '/club',
        parentNavigatorKey: _rootKey,
        builder: (_, __) => const ClubScreen(),
      ),
      GoRoute(
        path: '/points',
        parentNavigatorKey: _rootKey,
        builder: (_, __) => const PointsScreen(),
      ),
      GoRoute(
        path: '/badges',
        parentNavigatorKey: _rootKey,
        builder: (_, __) => const BadgesScreen(),
      ),
      GoRoute(
        path: '/shop',
        parentNavigatorKey: _rootKey,
        builder: (_, __) => const ShopScreen(),
      ),
      GoRoute(
        path: '/shop/:slug',
        parentNavigatorKey: _rootKey,
        builder: (_, GoRouterState state) =>
            ShopDetailScreen(slug: state.pathParameters['slug'] ?? ''),
      ),
      GoRoute(
        path: '/subscription',
        parentNavigatorKey: _rootKey,
        builder: (_, __) => const SubscriptionScreen(),
      ),
      GoRoute(
        path: '/library',
        parentNavigatorKey: _rootKey,
        builder: (_, __) => const LibraryScreen(),
      ),
      GoRoute(
        path: '/archive',
        parentNavigatorKey: _rootKey,
        builder: (_, __) => const ArchiveScreen(),
      ),
      GoRoute(
        path: '/tourism',
        parentNavigatorKey: _rootKey,
        builder: (_, __) => const TourismScreen(),
      ),
      GoRoute(
        path: '/notifications',
        parentNavigatorKey: _rootKey,
        builder: (_, __) => const NotificationsScreen(),
      ),
      GoRoute(
        path: '/settings',
        parentNavigatorKey: _rootKey,
        builder: (_, __) => const SettingsScreen(),
      ),
    ],
  );
});

String _defaultTypeTitle(String? type) {
  switch (type) {
    case 'note':
      return 'یادداشت‌ها';
    case 'video':
      return 'ویدئوها';
    case 'podcast':
      return 'پادکست‌ها';
    case 'photo':
      return 'گالری تصاویر';
    default:
      return 'اخبار';
  }
}
