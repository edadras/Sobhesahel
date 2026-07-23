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

/// The app router. Uses a [StatefulShellRoute.indexedStack] for the five
/// primary bottom-nav tabs, with the remaining member-area screens as
/// top-level routes pushed over the shell. Redirects gate authentication.
final routerProvider = Provider<GoRouter>((ref) {
  // Bridge Riverpod auth changes into a Listenable go_router can refresh on.
  final ValueNotifier<int> refresh = ValueNotifier<int>(0);
  ref.listen(authControllerProvider, (_, __) => refresh.value++);
  ref.onDispose(refresh.dispose);

  return GoRouter(
    navigatorKey: _rootKey,
    initialLocation: '/dashboard',
    refreshListenable: refresh,
    redirect: (BuildContext context, GoRouterState state) {
      final AuthState auth = ref.read(authControllerProvider);
      final String loc = state.matchedLocation;
      final bool onSplash = loc == '/splash';
      final bool onLogin = loc == '/login';

      if (auth.status == AuthStatus.unknown) {
        return onSplash ? null : '/splash';
      }
      if (!auth.isAuthenticated) {
        return onLogin ? null : '/login';
      }
      // Authenticated: keep them out of splash/login.
      if (onSplash || onLogin) return '/dashboard';
      return null;
    },
    routes: <RouteBase>[
      GoRoute(
        path: '/splash',
        builder: (_, __) => const SplashScreen(),
      ),
      GoRoute(
        path: '/login',
        builder: (_, __) => const LoginScreen(),
      ),

      // Primary tabs.
      StatefulShellRoute.indexedStack(
        parentNavigatorKey: _rootKey,
        builder: (_, __, StatefulNavigationShell shell) =>
            AppShell(navigationShell: shell),
        branches: <StatefulShellBranch>[
          StatefulShellBranch(
            navigatorKey: _shellKey,
            routes: <RouteBase>[
              GoRoute(
                path: '/dashboard',
                builder: (_, __) => const DashboardScreen(),
              ),
            ],
          ),
          StatefulShellBranch(
            routes: <RouteBase>[
              GoRoute(
                path: '/club',
                builder: (_, __) => const ClubScreen(),
              ),
            ],
          ),
          StatefulShellBranch(
            routes: <RouteBase>[
              GoRoute(
                path: '/shop',
                builder: (_, __) => const ShopScreen(),
              ),
            ],
          ),
          StatefulShellBranch(
            routes: <RouteBase>[
              GoRoute(
                path: '/library',
                builder: (_, __) => const LibraryScreen(),
              ),
            ],
          ),
          StatefulShellBranch(
            routes: <RouteBase>[
              GoRoute(
                path: '/settings',
                builder: (_, __) => const SettingsScreen(),
              ),
            ],
          ),
        ],
      ),

      // Secondary screens pushed full-screen over the shell.
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
        path: '/subscription',
        parentNavigatorKey: _rootKey,
        builder: (_, __) => const SubscriptionScreen(),
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
        path: '/shop/:slug',
        parentNavigatorKey: _rootKey,
        builder: (_, GoRouterState state) =>
            ShopDetailScreen(slug: state.pathParameters['slug'] ?? ''),
      ),
    ],
  );
});
