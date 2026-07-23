import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../core/persian.dart';
import '../../data/models/models.dart';
import '../../data/providers.dart';
import '../../l10n/app_localizations.dart';
import '../../theme/app_colors.dart';
import '../../theme/app_theme.dart';
import '../../theme/glass.dart';
import '../../widgets/common.dart';

/// The member home. Greeting hero, points/level ring, streak, today's missions
/// summary, subscription card and quick links — all bound to
/// [DashboardRepository] via [dashboardProvider].
class DashboardScreen extends ConsumerWidget {
  const DashboardScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final AppLocalizations t = AppLocalizations.of(context);
    final AsyncValue<DashboardData> async = ref.watch(dashboardProvider);

    return GlassScaffold(
      appBar: GlassAppBar(
        title: t.appName,
        subtitle: t.appTagline,
        actions: <Widget>[
          IconButton(
            onPressed: () => context.push('/notifications'),
            icon: const Icon(Icons.notifications_none_rounded),
          ),
          const SizedBox(width: 4),
        ],
      ),
      body: async.when(
        loading: () => const LoadingView(),
        error: (e, _) => ErrorRetry(
          message: e is Object ? e.toString() : null,
          onRetry: () => ref.invalidate(dashboardProvider),
        ),
        data: (DashboardData d) => RefreshIndicator(
          onRefresh: () async => ref.invalidate(dashboardProvider),
          child: ListView(
            padding: const EdgeInsets.fromLTRB(16, 8, 16, 104),
            children: <Widget>[
              _welcome(context, t, d),
              const SizedBox(height: 18),
              _statGrid(context, t, d),
              const SizedBox(height: 18),
              _levelProgress(context, t, d),
              const SizedBox(height: 18),
              _missions(context, t, d),
              const SizedBox(height: 18),
              _subscription(context, t, d),
              const SizedBox(height: 18),
              _quickLinks(context, t),
            ],
          ),
        ),
      ),
    );
  }

  Widget _welcome(BuildContext context, AppLocalizations t, DashboardData d) {
    return BrandHeroCard(
      child: Row(
        children: <Widget>[
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: <Widget>[
                Text(
                  t.dashGreeting(d.member.firstName),
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 21,
                    fontWeight: FontWeight.w800,
                    fontFamily: AppTheme.fontFamily,
                  ),
                ),
                const SizedBox(height: 8),
                Text(
                  t.dashSubtitle(context.faNum(d.todayPointsEarned)),
                  style:
                      TextStyle(color: Colors.white.withValues(alpha: 0.9)),
                ),
                const SizedBox(height: 12),
                StatusPill(
                  t.dashStreakDays(d.streak.currentDays),
                  color: Colors.white,
                  icon: Icons.local_fire_department_rounded,
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _statGrid(BuildContext context, AppLocalizations t, DashboardData d) {
    final AppPalette c = context.colors;
    final List<Widget> tiles = <Widget>[
      _stat(context, Icons.monetization_on_rounded, c.gold,
          context.faNum(d.points.balance), t.dashTotalPoints),
      _stat(context, Icons.workspace_premium_rounded, c.brand,
          d.points.level.title, t.dashMembershipLevel),
      _stat(
          context,
          Icons.schedule_rounded,
          c.plum,
          d.subscription == null
              ? '—'
              : context.faNum(d.subscription!.daysLeft),
          t.dashSubscriptionRemaining),
      _stat(context, Icons.bookmark_rounded, c.success,
          context.faNum(d.badgesCount), t.navBadges),
    ];
    return GridView.count(
      crossAxisCount: 2,
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      mainAxisSpacing: 12,
      crossAxisSpacing: 12,
      childAspectRatio: 1.55,
      children: tiles,
    );
  }

  Widget _stat(BuildContext context, IconData icon, Color color, String value,
      String caption) {
    return GlassCard(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: <Widget>[
          IconChip(icon: icon, color: color),
          const Spacer(),
          Text(
            value,
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
            style: Theme.of(context)
                .textTheme
                .headlineSmall
                ?.copyWith(fontWeight: FontWeight.w800),
          ),
          const SizedBox(height: 2),
          Text(caption, style: Theme.of(context).textTheme.bodySmall),
        ],
      ),
    );
  }

  Widget _levelProgress(
      BuildContext context, AppLocalizations t, DashboardData d) {
    final MemberLevel level = d.points.level;
    final int remaining =
        (level.nextThreshold - d.points.balance).clamp(0, level.nextThreshold);
    return GlassCard(
      child: Row(
        children: <Widget>[
          ProgressRing(
            percent: level.progressPercent,
            center: Text(
              '${context.faNum(level.progressPercent.round())}٪',
              style: Theme.of(context)
                  .textTheme
                  .titleMedium
                  ?.copyWith(fontWeight: FontWeight.w800),
            ),
          ),
          const SizedBox(width: 18),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: <Widget>[
                Text(t.dashProgressToNextLevel,
                    style: Theme.of(context).textTheme.titleMedium),
                const SizedBox(height: 8),
                Text(
                  t.dashPointsToLevel(
                      context.faNum(remaining), t.tierPlatinum),
                  style: Theme.of(context).textTheme.bodyMedium,
                ),
                const SizedBox(height: 12),
                GradientProgressBar(percent: level.progressPercent),
                const SizedBox(height: 6),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: <Widget>[
                    Text(context.faNum(d.points.balance),
                        style: Theme.of(context).textTheme.bodySmall),
                    Text(context.faNum(level.nextThreshold),
                        style: Theme.of(context).textTheme.bodySmall),
                  ],
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _missions(BuildContext context, AppLocalizations t, DashboardData d) {
    final AppPalette c = context.colors;
    return GlassCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: <Widget>[
          Row(
            children: <Widget>[
              IconChip(
                  icon: Icons.card_giftcard_rounded,
                  color: c.gold,
                  size: 34,
                  radius: 9),
              const SizedBox(width: 10),
              Expanded(
                child: Text(t.dashTodayMissions,
                    style: Theme.of(context).textTheme.titleMedium),
              ),
              TextButton(
                onPressed: () => context.go('/club'),
                child: Text(t.dashCustomerClub),
              ),
            ],
          ),
          const SizedBox(height: 6),
          for (final Mission m in d.missionsToday) _missionRow(context, m),
          const Divider(height: 24),
          Row(
            children: <Widget>[
              StatusPill(
                t.dashMissionsReward(context.faNum(20)),
                color: c.gold,
                icon: Icons.monetization_on_rounded,
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Text(t.dashMissionsRewardHint,
                    style: Theme.of(context).textTheme.bodySmall),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _missionRow(BuildContext context, Mission m) {
    final AppPalette c = context.colors;
    final Color color = m.completed ? c.success : c.plum;
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 7),
      child: Row(
        children: <Widget>[
          IconChip(
            icon: m.completed
                ? Icons.check_rounded
                : Icons.ios_share_rounded,
            color: color,
            size: 34,
            radius: 10,
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Text(m.title,
                style: Theme.of(context).textTheme.titleSmall),
          ),
          Text(
            m.completed
                ? AppLocalizations.of(context).clubMissionCompleted
                : '${context.faNum(m.progress)}/${context.faNum(m.goal)}',
            style: Theme.of(context)
                .textTheme
                .bodySmall
                ?.copyWith(color: m.completed ? c.success : c.textMuted),
          ),
        ],
      ),
    );
  }

  Widget _subscription(
      BuildContext context, AppLocalizations t, DashboardData d) {
    final AppPalette c = context.colors;
    final ActiveSubscription? sub = d.subscription;
    return GlassCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: <Widget>[
          Text(t.dashMySubscription,
              style: Theme.of(context).textTheme.titleMedium),
          const SizedBox(height: 14),
          if (sub != null)
            Row(
              children: <Widget>[
                ProgressRing(
                  size: 76,
                  stroke: 7,
                  percent: (sub.daysLeft / 30 * 100).clamp(0, 100),
                  color: c.gold,
                  center: Text(
                    context.faNum(sub.daysLeft),
                    style: Theme.of(context)
                        .textTheme
                        .titleMedium
                        ?.copyWith(fontWeight: FontWeight.w800),
                  ),
                ),
                const SizedBox(width: 14),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: <Widget>[
                      StatusPill(sub.planTitle,
                          color: c.tierGold,
                          icon: Icons.workspace_premium_rounded),
                      const SizedBox(height: 8),
                      Text(
                        t.dashRenewalOn(sub.endsAtJalali),
                        style: Theme.of(context).textTheme.bodySmall,
                      ),
                    ],
                  ),
                ),
              ],
            )
          else
            Text(t.subExpired,
                style: Theme.of(context).textTheme.bodyMedium),
          const SizedBox(height: 16),
          GradientButton(
            label: t.dashRenewUpgrade,
            icon: Icons.workspace_premium_rounded,
            onPressed: () => context.push('/subscription'),
          ),
        ],
      ),
    );
  }

  Widget _quickLinks(BuildContext context, AppLocalizations t) {
    final AppPalette c = context.colors;
    final List<_QuickLink> links = <_QuickLink>[
      _QuickLink(Icons.monetization_on_rounded, t.navPoints, '/points', c.gold),
      _QuickLink(Icons.emoji_events_rounded, t.navBadges, '/badges', c.brand),
      _QuickLink(Icons.menu_book_rounded, t.navArchive, '/archive', c.plum),
      _QuickLink(Icons.place_rounded, t.navTourism, '/tourism',
          c.tierPlatinum),
    ];
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: <Widget>[
        SectionTitle(t.dashQuickLinks),
        GridView.count(
          crossAxisCount: 2,
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          mainAxisSpacing: 12,
          crossAxisSpacing: 12,
          childAspectRatio: 2.4,
          children: <Widget>[
            for (final _QuickLink l in links)
              GlassCard(
                padding: const EdgeInsets.all(14),
                onTap: () => context.push(l.route),
                child: Row(
                  children: <Widget>[
                    IconChip(icon: l.icon, color: l.color, size: 38),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Text(
                        l.label,
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                        style: Theme.of(context).textTheme.titleSmall,
                      ),
                    ),
                  ],
                ),
              ),
          ],
        ),
      ],
    );
  }
}

class _QuickLink {
  const _QuickLink(this.icon, this.label, this.route, this.color);
  final IconData icon;
  final String label;
  final String route;
  final Color color;
}
