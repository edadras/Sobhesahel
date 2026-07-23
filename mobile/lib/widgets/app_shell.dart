import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

import '../l10n/app_localizations.dart';
import '../theme/app_colors.dart';
import '../theme/glass.dart';

/// The primary-tab shell: a [GlassBottomNav] over an indexed stack of the five
/// news-first branches (Home, Services, Multimedia, Newspaper, Account). Driven
/// by go_router's [StatefulNavigationShell]. The member area lives under the
/// Account tab.
class AppShell extends StatelessWidget {
  const AppShell({super.key, required this.navigationShell});

  final StatefulNavigationShell navigationShell;

  @override
  Widget build(BuildContext context) {
    final AppLocalizations t = AppLocalizations.of(context);
    final List<GlassNavItem> items = <GlassNavItem>[
      GlassNavItem(
        icon: Icons.home_outlined,
        activeIcon: Icons.home_rounded,
        label: t.newsNavHome,
      ),
      GlassNavItem(
        icon: Icons.grid_view_outlined,
        activeIcon: Icons.grid_view_rounded,
        label: t.newsNavServices,
      ),
      GlassNavItem(
        icon: Icons.play_circle_outline_rounded,
        activeIcon: Icons.play_circle_fill_rounded,
        label: t.newsNavMultimedia,
      ),
      GlassNavItem(
        icon: Icons.menu_book_outlined,
        activeIcon: Icons.menu_book_rounded,
        label: t.newsNavNewspaper,
      ),
      GlassNavItem(
        icon: Icons.person_outline_rounded,
        activeIcon: Icons.person_rounded,
        label: t.newsNavAccount,
      ),
    ];

    return Scaffold(
      backgroundColor: Colors.transparent,
      extendBody: true,
      body: navigationShell,
      bottomNavigationBar: GlassBottomNav(
        items: items,
        currentIndex: navigationShell.currentIndex,
        onTap: (int index) => navigationShell.goBranch(
          index,
          initialLocation: index == navigationShell.currentIndex,
        ),
      ),
    );
  }
}

/// Shown while the auth controller bootstraps from stored credentials.
class SplashScreen extends StatelessWidget {
  const SplashScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final AppPalette c = context.colors;
    final AppLocalizations t = AppLocalizations.of(context);
    return DecoratedBox(
      decoration: BoxDecoration(gradient: c.scaffoldGradient),
      child: Center(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: <Widget>[
            Container(
              width: 96,
              height: 96,
              decoration: BoxDecoration(
                gradient: c.brandGradient,
                shape: BoxShape.circle,
                boxShadow: <BoxShadow>[
                  BoxShadow(
                    color: c.brand.withValues(alpha: 0.4),
                    blurRadius: 30,
                    offset: const Offset(0, 14),
                  ),
                ],
              ),
              child: const Icon(Icons.waves_rounded,
                  color: Colors.white, size: 44),
            ),
            const SizedBox(height: 22),
            Text(t.appName,
                style: Theme.of(context).textTheme.headlineMedium),
            const SizedBox(height: 6),
            Text(t.appTagline,
                style: Theme.of(context).textTheme.bodyMedium),
            const SizedBox(height: 26),
            const SizedBox(
              width: 26,
              height: 26,
              child: CircularProgressIndicator(strokeWidth: 2.6),
            ),
          ],
        ),
      ),
    );
  }
}
