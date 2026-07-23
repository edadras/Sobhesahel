import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

import '../l10n/app_localizations.dart';
import '../theme/app_colors.dart';
import '../theme/glass.dart';

/// The primary-tab shell: a [GlassBottomNav] over an indexed stack of the five
/// main branches (dashboard, club, shop, library, settings). Driven by
/// go_router's [StatefulNavigationShell].
class AppShell extends StatelessWidget {
  const AppShell({super.key, required this.navigationShell});

  final StatefulNavigationShell navigationShell;

  @override
  Widget build(BuildContext context) {
    final AppLocalizations t = AppLocalizations.of(context);
    final List<GlassNavItem> items = <GlassNavItem>[
      GlassNavItem(
        icon: Icons.grid_view_outlined,
        activeIcon: Icons.grid_view_rounded,
        label: t.navDashboard,
      ),
      GlassNavItem(
        icon: Icons.card_giftcard_outlined,
        activeIcon: Icons.card_giftcard_rounded,
        label: t.navClub,
      ),
      GlassNavItem(
        icon: Icons.shopping_bag_outlined,
        activeIcon: Icons.shopping_bag_rounded,
        label: t.navShop,
      ),
      GlassNavItem(
        icon: Icons.local_library_outlined,
        activeIcon: Icons.local_library_rounded,
        label: t.navLibrary,
      ),
      GlassNavItem(
        icon: Icons.settings_outlined,
        activeIcon: Icons.settings_rounded,
        label: t.navSettings,
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
