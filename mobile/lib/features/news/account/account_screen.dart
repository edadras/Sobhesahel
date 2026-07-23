import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../data/models/models.dart';
import '../../../l10n/app_localizations.dart';
import '../../../theme/app_colors.dart';
import '../../../theme/app_theme.dart';
import '../../../theme/glass.dart';
import '../../../widgets/common.dart';
import '../../auth/auth_controller.dart';

/// The «حساب من» tab. When a member is logged in it is the hub of the member
/// area, linking to every member screen; otherwise it is a friendly login
/// gateway. Guests keep full access to the public news experience.
class AccountScreen extends ConsumerWidget {
  const AccountScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final AppLocalizations t = AppLocalizations.of(context);
    final AuthState auth = ref.watch(authControllerProvider);

    return GlassScaffold(
      appBar: GlassAppBar(
        title: t.accountTitle,
        automaticallyImplyLeading: false,
        actions: <Widget>[
          IconButton(
            tooltip: t.settingsTitle,
            onPressed: () => context.push('/settings'),
            icon: const Icon(Icons.settings_outlined),
          ),
          const SizedBox(width: 4),
        ],
      ),
      body: auth.isAuthenticated
          ? _hub(context, t, ref, auth.member)
          : _gateway(context, t),
    );
  }

  // --- Guest gateway -------------------------------------------------------

  Widget _gateway(BuildContext context, AppLocalizations t) {
    final AppPalette c = context.colors;
    return ListView(
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 104),
      children: <Widget>[
        BrandHeroCard(
          deep: true,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: <Widget>[
              const Icon(Icons.card_membership_rounded,
                  color: Colors.white, size: 40),
              const SizedBox(height: 14),
              Text(t.accountGuestTitle,
                  style: const TextStyle(
                      color: Colors.white,
                      fontSize: 20,
                      fontWeight: FontWeight.w800,
                      height: 1.5,
                      fontFamily: AppTheme.fontFamily)),
              const SizedBox(height: 8),
              Text(t.accountGuestSubtitle,
                  style: TextStyle(
                      color: Colors.white.withValues(alpha: 0.92),
                      height: 1.7)),
              const SizedBox(height: 18),
              GradientButton(
                label: t.accountLogin,
                icon: Icons.login_rounded,
                onPressed: () => context.push('/login'),
              ),
            ],
          ),
        ),
        const SizedBox(height: 18),
        _benefit(context, Icons.workspace_premium_rounded, t.authBenefitPoints),
        _benefit(context, Icons.menu_book_rounded, t.authBenefitNews),
        _benefit(context, Icons.card_giftcard_rounded, t.authBenefitClub),
        _benefit(context, Icons.place_rounded, t.authBenefitTourism),
        const SizedBox(height: 16),
        Center(
          child: Text(t.accountGuestBrowse,
              textAlign: TextAlign.center,
              style: Theme.of(context)
                  .textTheme
                  .bodySmall
                  ?.copyWith(color: c.textFaint)),
        ),
      ],
    );
  }

  Widget _benefit(BuildContext context, IconData icon, String label) {
    final AppPalette c = context.colors;
    return Padding(
      padding: const EdgeInsets.only(bottom: 10),
      child: GlassCard(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
        child: Row(
          children: <Widget>[
            IconChip(icon: icon, color: c.brand, size: 40),
            const SizedBox(width: 12),
            Expanded(
              child: Text(label,
                  style: Theme.of(context).textTheme.titleSmall),
            ),
          ],
        ),
      ),
    );
  }

  // --- Member hub ----------------------------------------------------------

  Widget _hub(BuildContext context, AppLocalizations t, WidgetRef ref,
      Member? member) {
    final AppPalette c = context.colors;
    final List<_HubLink> links = <_HubLink>[
      _HubLink(Icons.grid_view_rounded, t.navDashboard, '/dashboard', c.brand),
      _HubLink(Icons.card_giftcard_rounded, t.navClub, '/club', c.gold),
      _HubLink(Icons.monetization_on_rounded, t.navPoints, '/points', c.gold),
      _HubLink(Icons.emoji_events_rounded, t.navBadges, '/badges', c.brand),
      _HubLink(Icons.shopping_bag_rounded, t.navShop, '/shop', c.plum),
      _HubLink(Icons.workspace_premium_rounded, t.navSubscription,
          '/subscription', c.tierGold),
      _HubLink(Icons.local_library_rounded, t.navLibrary, '/library', c.plum),
      _HubLink(Icons.menu_book_rounded, t.navArchive, '/archive',
          c.tierPlatinum),
      _HubLink(Icons.place_rounded, t.navTourism, '/tourism', c.tierPlatinum),
      _HubLink(Icons.notifications_rounded, t.navNotifications,
          '/notifications', c.accentRed),
      _HubLink(Icons.settings_rounded, t.navSettings, '/settings', c.textMuted),
    ];

    return ListView(
      padding: const EdgeInsets.fromLTRB(16, 12, 16, 104),
      children: <Widget>[
        GlassCard(
          onTap: () => context.push('/dashboard'),
          child: Row(
            children: <Widget>[
              CircleAvatar(
                radius: 30,
                backgroundColor: c.brandSoft,
                foregroundImage: (member?.avatarUrl != null &&
                        member!.avatarUrl!.isNotEmpty)
                    ? NetworkImage(member.avatarUrl!)
                    : null,
                child: Text(
                  member?.initials ?? '؟',
                  style: TextStyle(
                      color: c.brand,
                      fontSize: 20,
                      fontWeight: FontWeight.w800,
                      fontFamily: AppTheme.fontFamily),
                ),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: <Widget>[
                    Text(member?.fullName ?? t.accountTitle,
                        style: Theme.of(context).textTheme.titleMedium),
                    const SizedBox(height: 4),
                    Text(member?.mobile ?? '',
                        style: Theme.of(context).textTheme.bodySmall),
                  ],
                ),
              ),
              Icon(Icons.chevron_left_rounded, color: c.textFaint),
            ],
          ),
        ),
        const SizedBox(height: 8),
        Padding(
          padding: const EdgeInsets.symmetric(vertical: 8),
          child: Text(t.accountHubSubtitle,
              style: Theme.of(context).textTheme.bodyMedium),
        ),
        GridView.count(
          crossAxisCount: 2,
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          mainAxisSpacing: 12,
          crossAxisSpacing: 12,
          childAspectRatio: 2.5,
          children: <Widget>[
            for (final _HubLink l in links)
              GlassCard(
                padding: const EdgeInsets.all(14),
                onTap: () => context.push(l.route),
                child: Row(
                  children: <Widget>[
                    IconChip(icon: l.icon, color: l.color, size: 38),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Text(l.label,
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                          style: Theme.of(context).textTheme.titleSmall),
                    ),
                  ],
                ),
              ),
          ],
        ),
        const SizedBox(height: 18),
        OutlinedButton.icon(
          onPressed: () => _confirmLogout(context, t, ref),
          icon: const Icon(Icons.logout_rounded, size: 18),
          label: Text(t.commonLogout),
        ),
      ],
    );
  }

  Future<void> _confirmLogout(
      BuildContext context, AppLocalizations t, WidgetRef ref) async {
    final bool? ok = await showDialog<bool>(
      context: context,
      builder: (BuildContext ctx) => AlertDialog(
        title: Text(t.settingsLogoutConfirmTitle),
        content: Text(t.settingsLogoutConfirmMessage),
        actions: <Widget>[
          TextButton(
            onPressed: () => Navigator.of(ctx).pop(false),
            child: Text(t.commonCancel),
          ),
          TextButton(
            onPressed: () => Navigator.of(ctx).pop(true),
            child: Text(t.commonLogout),
          ),
        ],
      ),
    );
    if (ok == true) {
      await ref.read(authControllerProvider.notifier).logout();
    }
  }
}

class _HubLink {
  const _HubLink(this.icon, this.label, this.route, this.color);
  final IconData icon;
  final String label;
  final String route;
  final Color color;
}
