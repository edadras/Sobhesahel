import 'package:flutter/material.dart' hide Badge;
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../core/persian.dart';
import '../../data/models/models.dart';
import '../../data/providers.dart';
import '../../l10n/app_localizations.dart';
import '../../theme/app_colors.dart';
import '../../theme/app_theme.dart';
import '../../theme/glass.dart';
import '../../widgets/common.dart';

/// Loads the member's badge collection from /badges.
final badgesProvider = FutureProvider.autoDispose<List<Badge>>((ref) {
  return ref.watch(badgesRepositoryProvider).list();
});

/// Badges: a responsive grid of every badge — earned ones in colour with their
/// Jalali award date, locked ones desaturated with the Persian unlock
/// condition. Bound to [BadgesRepository] via [badgesProvider].
class BadgesScreen extends ConsumerWidget {
  const BadgesScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final AppLocalizations t = AppLocalizations.of(context);
    final AsyncValue<List<Badge>> async = ref.watch(badgesProvider);

    return GlassScaffold(
      appBar: GlassAppBar(title: t.badgesTitle, subtitle: t.badgesSubtitle),
      body: async.when(
        loading: () => const _BadgesSkeleton(),
        error: (Object e, _) => ErrorRetry(
          message: e.toString(),
          onRetry: () => ref.invalidate(badgesProvider),
        ),
        data: (List<Badge> badges) => RefreshIndicator(
          onRefresh: () async => ref.invalidate(badgesProvider),
          child: badges.isEmpty
              ? ListView(
                  children: const <Widget>[
                    SizedBox(height: 120),
                    EmptyState(icon: Icons.emoji_events_outlined),
                  ],
                )
              : _BadgesBody(badges: badges),
        ),
      ),
    );
  }
}

class _BadgesBody extends StatefulWidget {
  const _BadgesBody({required this.badges});
  final List<Badge> badges;

  @override
  State<_BadgesBody> createState() => _BadgesBodyState();
}

class _BadgesBodyState extends State<_BadgesBody>
    with SingleTickerProviderStateMixin {
  late final AnimationController _reveal = AnimationController(
    vsync: this,
    duration: const Duration(milliseconds: 900),
  );

  static const List<int> _iconPalette = <int>[0, 1, 2, 3, 4, 5];

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (mounted) _reveal.forward();
    });
  }

  @override
  void dispose() {
    _reveal.dispose();
    super.dispose();
  }

  int _columns(double width) {
    if (width >= 680) return 4;
    if (width >= 480) return 3;
    return 2;
  }

  Color _colorFor(AppPalette c, int index) {
    final List<Color> colors = <Color>[
      c.brand,
      c.plum,
      c.tierGold,
      c.tierPlatinum,
      c.tierSilver,
      c.tierBronze,
    ];
    return colors[_iconPalette[index % _iconPalette.length]];
  }

  @override
  Widget build(BuildContext context) {
    final AppLocalizations t = AppLocalizations.of(context);
    final int earned = widget.badges.where((Badge b) => b.earned).length;
    final int total = widget.badges.length;

    return LayoutBuilder(
      builder: (BuildContext context, BoxConstraints constraints) {
        final int columns = _columns(constraints.maxWidth);
        return CustomScrollView(
          slivers: <Widget>[
            SliverPadding(
              padding: const EdgeInsets.fromLTRB(16, 8, 16, 0),
              sliver: SliverToBoxAdapter(
                child: _summary(context, t, earned, total),
              ),
            ),
            SliverPadding(
              padding: const EdgeInsets.fromLTRB(16, 18, 16, 104),
              sliver: SliverGrid(
                gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
                  crossAxisCount: columns,
                  mainAxisSpacing: 14,
                  crossAxisSpacing: 14,
                  childAspectRatio: 0.74,
                ),
                delegate: SliverChildBuilderDelegate(
                  (BuildContext context, int index) {
                    final Badge badge = widget.badges[index];
                    return _revealed(
                      index: index,
                      count: total,
                      child: _BadgeTile(
                        badge: badge,
                        color: _colorFor(context.colors, index),
                      ),
                    );
                  },
                  childCount: total,
                ),
              ),
            ),
          ],
        );
      },
    );
  }

  Widget _summary(
      BuildContext context, AppLocalizations t, int earned, int total) {
    final AppPalette c = context.colors;
    final double percent = total == 0 ? 0.0 : earned / total * 100;
    return GlassCard(
      child: Row(
        children: <Widget>[
          ProgressRing(
            size: 84,
            stroke: 8,
            percent: percent,
            color: c.gold,
            center: Text(
              '${context.faNum(earned)}/${context.faNum(total)}',
              style: Theme.of(context)
                  .textTheme
                  .titleMedium
                  ?.copyWith(fontWeight: FontWeight.w800),
            ),
          ),
          const SizedBox(width: 16),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: <Widget>[
                Text(t.badgesTitle,
                    style: Theme.of(context).textTheme.titleMedium),
                const SizedBox(height: 10),
                Wrap(
                  spacing: 8,
                  runSpacing: 8,
                  children: <Widget>[
                    StatusPill(
                      '${context.faNum(earned)} ${t.badgesEarned}',
                      color: c.success,
                      icon: Icons.verified_rounded,
                    ),
                    StatusPill(
                      '${context.faNum(total - earned)} ${t.badgesLocked}',
                      color: c.textMuted,
                      icon: Icons.lock_rounded,
                    ),
                  ],
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _revealed(
      {required int index, required int count, required Widget child}) {
    final double start =
        count <= 1 ? 0.0 : (index / count * 0.5).clamp(0.0, 0.5);
    final double end = (start + 0.5).clamp(0.0, 1.0);
    final Animation<double> anim = CurvedAnimation(
      parent: _reveal,
      curve: Interval(start, end, curve: Curves.easeOutBack),
    );
    return AnimatedBuilder(
      animation: anim,
      builder: (BuildContext context, Widget? c) {
        final double v = anim.value.clamp(0.0, 1.0);
        return Opacity(
          opacity: v,
          child: Transform.translate(
            offset: Offset(0, (1 - v) * 14),
            child: Transform.scale(scale: 0.92 + 0.08 * v, child: c),
          ),
        );
      },
      child: child,
    );
  }
}

class _BadgeTile extends StatelessWidget {
  const _BadgeTile({required this.badge, required this.color});
  final Badge badge;
  final Color color;

  @override
  Widget build(BuildContext context) {
    final AppLocalizations t = AppLocalizations.of(context);
    final AppPalette c = context.colors;
    final bool earned = badge.earned;
    final Color discColor = earned ? color : c.textFaint;

    return GlassCard(
      strong: earned,
      padding: const EdgeInsets.all(14),
      child: Opacity(
        opacity: earned ? 1 : 0.72,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.center,
          children: <Widget>[
            Align(
              alignment: AlignmentDirectional.centerEnd,
              child: Icon(
                earned ? Icons.check_circle_rounded : Icons.lock_rounded,
                size: 18,
                color: earned ? c.success : c.textFaint,
              ),
            ),
            const SizedBox(height: 2),
            Container(
              width: 66,
              height: 66,
              decoration: BoxDecoration(
                color: earned
                    ? discColor.withValues(alpha: 0.16)
                    : c.surface2,
                shape: BoxShape.circle,
                border: Border.all(
                  color: earned
                      ? discColor.withValues(alpha: 0.4)
                      : c.border,
                  width: 1.4,
                ),
              ),
              child: Icon(_badgeIcon(badge.icon), color: discColor, size: 30),
            ),
            const SizedBox(height: 12),
            Text(
              badge.title,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              textAlign: TextAlign.center,
              style: Theme.of(context)
                  .textTheme
                  .titleSmall
                  ?.copyWith(fontWeight: FontWeight.w800),
            ),
            const SizedBox(height: 4),
            Expanded(
              child: Text(
                earned ? badge.description : badge.conditionText,
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
                textAlign: TextAlign.center,
                style: Theme.of(context).textTheme.bodySmall,
              ),
            ),
            const SizedBox(height: 8),
            if (earned)
              StatusPill(
                badge.awardedAtJalali != null
                    ? t.badgesEarnedOn(context.faDigits(badge.awardedAtJalali!))
                    : t.badgesEarned,
                color: c.success,
                icon: Icons.event_available_rounded,
              )
            else
              StatusPill(
                t.badgesLocked,
                color: c.textMuted,
                icon: Icons.lock_outline_rounded,
              ),
          ],
        ),
      ),
    );
  }

  IconData _badgeIcon(String icon) {
    switch (icon) {
      case 'sun':
        return Icons.wb_sunny_rounded;
      case 'book':
        return Icons.menu_book_rounded;
      case 'share':
        return Icons.ios_share_rounded;
      case 'bag':
        return Icons.shopping_bag_rounded;
      case 'crown':
        return Icons.workspace_premium_rounded;
      case 'pin':
        return Icons.place_rounded;
      case 'star':
        return Icons.star_rounded;
      case 'heart':
        return Icons.favorite_rounded;
      default:
        return Icons.emoji_events_rounded;
    }
  }
}

// ---------------------------------------------------------------------------
// Loading skeleton
// ---------------------------------------------------------------------------

class _BadgesSkeleton extends StatelessWidget {
  const _BadgesSkeleton();

  @override
  Widget build(BuildContext context) {
    return _Shimmer(
      child: GridView.count(
        crossAxisCount: 2,
        padding: const EdgeInsets.fromLTRB(16, 90, 16, 104),
        mainAxisSpacing: 14,
        crossAxisSpacing: 14,
        childAspectRatio: 0.74,
        physics: const NeverScrollableScrollPhysics(),
        children: <Widget>[
          for (int i = 0; i < 6; i++)
            Container(
              decoration: BoxDecoration(
                color: context.colors.surface2,
                borderRadius: BorderRadius.circular(AppRadii.lg),
              ),
            ),
        ],
      ),
    );
  }
}

class _Shimmer extends StatefulWidget {
  const _Shimmer({required this.child});
  final Widget child;

  @override
  State<_Shimmer> createState() => _ShimmerState();
}

class _ShimmerState extends State<_Shimmer>
    with SingleTickerProviderStateMixin {
  late final AnimationController _c = AnimationController(
    vsync: this,
    duration: const Duration(milliseconds: 1100),
  )..repeat(reverse: true);

  @override
  void dispose() {
    _c.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return AnimatedBuilder(
      animation: _c,
      builder: (BuildContext context, Widget? child) =>
          Opacity(opacity: 0.35 + _c.value * 0.4, child: child),
      child: widget.child,
    );
  }
}
