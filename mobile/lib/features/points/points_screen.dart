import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../core/persian.dart';
import '../../data/models/models.dart';
import '../../data/providers.dart';
import '../../l10n/app_localizations.dart';
import '../../theme/app_colors.dart';
import '../../theme/app_theme.dart';
import '../../theme/glass.dart';
import '../../widgets/common.dart';

/// Loads the /points overview (balance, level, earn/spend rules).
final pointsOverviewProvider = FutureProvider.autoDispose<PointsOverview>((ref) {
  return ref.watch(pointsRepositoryProvider).overview();
});

/// Points & levels: balance hero, progress ring to the next tier, the four
/// membership tiers, earn/spend rules and a paginated (infinite-scroll)
/// transaction history with Jalali dates. Bound to [PointsRepository].
class PointsScreen extends ConsumerStatefulWidget {
  const PointsScreen({super.key});

  @override
  ConsumerState<PointsScreen> createState() => _PointsScreenState();
}

class _PointsScreenState extends ConsumerState<PointsScreen> {
  final ScrollController _scroll = ScrollController();
  final List<PointTransaction> _txns = <PointTransaction>[];
  int _page = 1;
  int _lastPage = 1;
  bool _loading = false;
  bool _loadedOnce = false;
  Object? _txnError;

  @override
  void initState() {
    super.initState();
    _scroll.addListener(_onScroll);
    WidgetsBinding.instance.addPostFrameCallback((_) => _loadMore());
  }

  @override
  void dispose() {
    _scroll.removeListener(_onScroll);
    _scroll.dispose();
    super.dispose();
  }

  bool get _hasMore => _page <= _lastPage;

  void _onScroll() {
    if (!_scroll.hasClients) return;
    if (_scroll.position.pixels >= _scroll.position.maxScrollExtent - 320) {
      _loadMore();
    }
  }

  Future<void> _loadMore() async {
    if (_loading || (_loadedOnce && !_hasMore)) return;
    setState(() {
      _loading = true;
      _txnError = null;
    });
    try {
      final Paged<PointTransaction> paged =
          await ref.read(pointsRepositoryProvider).transactions(page: _page);
      if (!mounted) return;
      setState(() {
        _txns.addAll(paged.items);
        _lastPage = paged.lastPage;
        _page = paged.currentPage + 1;
        _loadedOnce = true;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _txnError = e;
        _loadedOnce = true;
      });
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _refresh() async {
    ref.invalidate(pointsOverviewProvider);
    setState(() {
      _txns.clear();
      _page = 1;
      _lastPage = 1;
      _loadedOnce = false;
      _txnError = null;
    });
    await _loadMore();
  }

  void _exportReport() {
    final AppLocalizations t = AppLocalizations.of(context);
    // The mock/API PointsRepository exposes no export method, so surface the
    // affordance with a friendly notice instead of a silent no-op.
    ScaffoldMessenger.of(context)
      ..clearSnackBars()
      ..showSnackBar(
        SnackBar(
          content: Text('${t.pointsDownloadReport} — ${t.commonComingSoon}'),
        ),
      );
  }

  @override
  Widget build(BuildContext context) {
    final AppLocalizations t = AppLocalizations.of(context);
    final AsyncValue<PointsOverview> async = ref.watch(pointsOverviewProvider);

    return GlassScaffold(
      appBar: GlassAppBar(
        title: t.pointsTitle,
        actions: <Widget>[
          IconButton(
            tooltip: t.pointsDownloadReport,
            onPressed: _exportReport,
            icon: const Icon(Icons.download_rounded),
          ),
          const SizedBox(width: 4),
        ],
      ),
      body: async.when(
        loading: () => const _PointsSkeleton(),
        error: (Object e, _) => ErrorRetry(
          message: e.toString(),
          onRetry: () => ref.invalidate(pointsOverviewProvider),
        ),
        data: (PointsOverview o) => RefreshIndicator(
          onRefresh: _refresh,
          child: ListView(
            controller: _scroll,
            padding: const EdgeInsets.fromLTRB(16, 8, 16, 104),
            children: <Widget>[
              _hero(context, t, o),
              const SizedBox(height: 18),
              _levelProgress(context, t, o),
              const SizedBox(height: 18),
              _tiers(context, t, o),
              const SizedBox(height: 18),
              _rulesCard(
                context,
                title: t.pointsEarnWays,
                icon: Icons.savings_rounded,
                color: context.colors.success,
                rules: o.earnRules,
                earn: true,
              ),
              const SizedBox(height: 14),
              _rulesCard(
                context,
                title: t.pointsSpendWays,
                icon: Icons.redeem_rounded,
                color: context.colors.brand,
                rules: o.spendRules,
                earn: false,
              ),
              const SizedBox(height: 18),
              _history(context, t),
            ],
          ),
        ),
      ),
    );
  }

  // --- Hero -----------------------------------------------------------------

  Widget _hero(BuildContext context, AppLocalizations t, PointsOverview o) {
    return BrandHeroCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: <Widget>[
          Row(
            crossAxisAlignment: CrossAxisAlignment.center,
            children: <Widget>[
              const Icon(Icons.monetization_on_rounded,
                  color: Colors.white, size: 34),
              const SizedBox(width: 10),
              Flexible(
                child: Text(
                  context.faNum(o.balance),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 40,
                    height: 1,
                    fontWeight: FontWeight.w900,
                    fontFamily: AppTheme.fontFamily,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 8),
          Text(
            t.pointsBalanceLabel,
            style: TextStyle(color: Colors.white.withValues(alpha: 0.9)),
          ),
          const SizedBox(height: 12),
          StatusPill(
            o.level.title,
            color: Colors.white,
            icon: Icons.workspace_premium_rounded,
          ),
        ],
      ),
    );
  }

  // --- Progress to next tier ------------------------------------------------

  Widget _levelProgress(
      BuildContext context, AppLocalizations t, PointsOverview o) {
    final MemberLevel level = o.level;
    final String nextTitle = _nextTierTitle(t, level.tier);
    final int remaining =
        (level.nextThreshold - o.balance).clamp(0, level.nextThreshold);
    return GlassCard(
      child: Row(
        children: <Widget>[
          ProgressRing(
            percent: level.progressPercent,
            center: Text(
              '${context.faNum(level.progressPercent.round())}${context.isFa ? '٪' : '%'}',
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
                  t.dashPointsToLevel(context.faNum(remaining), nextTitle),
                  style: Theme.of(context).textTheme.bodyMedium,
                ),
                const SizedBox(height: 12),
                GradientProgressBar(percent: level.progressPercent),
                const SizedBox(height: 6),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: <Widget>[
                    Text(context.faNum(o.balance),
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

  // --- Membership tiers -----------------------------------------------------

  Widget _tiers(BuildContext context, AppLocalizations t, PointsOverview o) {
    final MemberTier current = o.level.tier;
    final List<_TierSpec> specs = <_TierSpec>[
      _TierSpec(MemberTier.bronze, t.tierBronze, 0, 1000, Icons.star_rounded),
      _TierSpec(MemberTier.silver, t.tierSilver, 1000, 5000, Icons.star_rounded),
      _TierSpec(MemberTier.gold, t.tierGold, 5000, 10000,
          Icons.workspace_premium_rounded),
      _TierSpec(MemberTier.platinum, t.tierPlatinum, 10000, null,
          Icons.diamond_rounded),
    ];
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: <Widget>[
        SectionTitle(t.pointsUserLevels),
        GridView.count(
          crossAxisCount: 2,
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          mainAxisSpacing: 12,
          crossAxisSpacing: 12,
          childAspectRatio: 1.35,
          children: <Widget>[
            for (final _TierSpec s in specs)
              _tierTile(context, t, s, s.tier == current),
          ],
        ),
      ],
    );
  }

  Widget _tierTile(
      BuildContext context, AppLocalizations t, _TierSpec s, bool current) {
    final AppPalette c = context.colors;
    final Color color = _tierColor(c, s.tier);
    final String range = s.max == null
        ? '${context.faNum(s.min)}+'
        : '${context.faNum(s.min)}–${context.faNum(s.max!)}';
    return GlassCard(
      strong: current,
      padding: const EdgeInsets.all(14),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: <Widget>[
          Row(
            children: <Widget>[
              IconChip(icon: s.icon, color: color, size: 34, radius: 9),
              const Spacer(),
              if (current) StatusPill(t.pointsLevelYours, color: color),
            ],
          ),
          const Spacer(),
          Text(
            s.title,
            style: Theme.of(context)
                .textTheme
                .titleMedium
                ?.copyWith(color: color, fontWeight: FontWeight.w800),
          ),
          const SizedBox(height: 4),
          Text(
            '$range ${t.commonPointsUnit}',
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
            style: Theme.of(context).textTheme.bodySmall,
          ),
        ],
      ),
    );
  }

  // --- Earn / spend rules ---------------------------------------------------

  Widget _rulesCard(
    BuildContext context, {
    required String title,
    required IconData icon,
    required Color color,
    required List<PointsRule> rules,
    required bool earn,
  }) {
    return GlassCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: <Widget>[
          Row(
            children: <Widget>[
              IconChip(icon: icon, color: color, size: 34, radius: 9),
              const SizedBox(width: 10),
              Expanded(
                child: Text(title,
                    style: Theme.of(context).textTheme.titleMedium),
              ),
            ],
          ),
          const SizedBox(height: 4),
          if (rules.isEmpty)
            Padding(
              padding: const EdgeInsets.symmetric(vertical: 12),
              child: Text(AppLocalizations.of(context).commonEmpty,
                  style: Theme.of(context).textTheme.bodyMedium),
            )
          else
            for (int i = 0; i < rules.length; i++)
              _ruleRow(context, rules[i], color, earn,
                  last: i == rules.length - 1),
        ],
      ),
    );
  }

  Widget _ruleRow(BuildContext context, PointsRule rule, Color color, bool earn,
      {required bool last}) {
    final AppPalette c = context.colors;
    final Color valueColor = earn ? c.success : c.brand;
    final String value = (earn ? '+' : '') + context.faDigits(rule.points);
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 11),
      decoration: BoxDecoration(
        border: last
            ? null
            : Border(bottom: BorderSide(color: c.glassBorderFaint)),
      ),
      child: Row(
        children: <Widget>[
          IconChip(
              icon: _ruleIcon(rule.code), color: color, size: 34, radius: 9),
          const SizedBox(width: 12),
          Expanded(
            child: Text(rule.title,
                style: Theme.of(context).textTheme.titleSmall),
          ),
          const SizedBox(width: 8),
          Text(
            value,
            style: Theme.of(context)
                .textTheme
                .titleSmall
                ?.copyWith(color: valueColor, fontWeight: FontWeight.w800),
          ),
        ],
      ),
    );
  }

  // --- Transaction history --------------------------------------------------

  Widget _history(BuildContext context, AppLocalizations t) {
    final AppPalette c = context.colors;
    return GlassCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: <Widget>[
          Row(
            children: <Widget>[
              IconChip(
                  icon: Icons.receipt_long_rounded,
                  color: c.plum,
                  size: 34,
                  radius: 9),
              const SizedBox(width: 10),
              Expanded(
                child: Text(t.pointsTransactionHistory,
                    style: Theme.of(context).textTheme.titleMedium),
              ),
              TextButton.icon(
                onPressed: _exportReport,
                icon: const Icon(Icons.download_rounded, size: 18),
                label: Text(t.pointsDownloadReport),
              ),
            ],
          ),
          const SizedBox(height: 6),
          if (_txnError != null && _txns.isEmpty)
            Padding(
              padding: const EdgeInsets.symmetric(vertical: 16),
              child: ErrorRetry(
                message: _txnError.toString(),
                onRetry: _loadMore,
              ),
            )
          else if (_txns.isEmpty && _loadedOnce && !_loading)
            const Padding(
              padding: EdgeInsets.symmetric(vertical: 24),
              child: EmptyState(icon: Icons.receipt_long_outlined),
            )
          else ...<Widget>[
            for (int i = 0; i < _txns.length; i++)
              _txnRow(context, _txns[i], last: i == _txns.length - 1),
            if (_loading)
              const Padding(
                padding: EdgeInsets.symmetric(vertical: 16),
                child: Center(
                  child: SizedBox(
                    width: 22,
                    height: 22,
                    child: CircularProgressIndicator(strokeWidth: 2.4),
                  ),
                ),
              ),
          ],
        ],
      ),
    );
  }

  Widget _txnRow(BuildContext context, PointTransaction tx,
      {required bool last}) {
    final AppPalette c = context.colors;
    final bool earn = tx.isEarn;
    final Color color = earn ? c.success : c.brand;
    final String sign = earn ? '+' : '−';
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 11),
      decoration: BoxDecoration(
        border: last
            ? null
            : Border(bottom: BorderSide(color: c.glassBorderFaint)),
      ),
      child: Row(
        children: <Widget>[
          IconChip(
            icon:
                earn ? Icons.arrow_upward_rounded : Icons.arrow_downward_rounded,
            color: color,
            size: 36,
            radius: 10,
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: <Widget>[
                Text(
                  tx.description,
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                  style: Theme.of(context).textTheme.titleSmall,
                ),
                const SizedBox(height: 2),
                Text(
                  context.faDigits(tx.createdAtJalali),
                  style: Theme.of(context).textTheme.bodySmall,
                ),
              ],
            ),
          ),
          const SizedBox(width: 8),
          Text(
            '$sign${context.faNum(tx.points.abs())}',
            style: Theme.of(context)
                .textTheme
                .titleMedium
                ?.copyWith(color: color, fontWeight: FontWeight.w800),
          ),
        ],
      ),
    );
  }

  // --- Helpers --------------------------------------------------------------

  String _nextTierTitle(AppLocalizations t, MemberTier tier) {
    switch (tier) {
      case MemberTier.bronze:
        return t.tierSilver;
      case MemberTier.silver:
        return t.tierGold;
      case MemberTier.gold:
        return t.tierPlatinum;
      case MemberTier.platinum:
        return t.tierPlatinum;
    }
  }

  Color _tierColor(AppPalette c, MemberTier tier) {
    switch (tier) {
      case MemberTier.bronze:
        return c.tierBronze;
      case MemberTier.silver:
        return c.tierSilver;
      case MemberTier.gold:
        return c.tierGold;
      case MemberTier.platinum:
        return c.tierPlatinum;
    }
  }

  IconData _ruleIcon(String code) {
    switch (code) {
      case 'register':
        return Icons.person_add_alt_1_rounded;
      case 'profile':
        return Icons.edit_rounded;
      case 'daily_login':
        return Icons.schedule_rounded;
      case 'read_news':
        return Icons.visibility_rounded;
      case 'read_full':
        return Icons.menu_book_rounded;
      case 'share':
        return Icons.ios_share_rounded;
      case 'comment':
        return Icons.chat_bubble_outline_rounded;
      case 'subscribe':
      case 'sub_day':
      case 'sub_week':
        return Icons.workspace_premium_rounded;
      case 'referral':
        return Icons.group_add_rounded;
      case 'bag':
        return Icons.shopping_bag_rounded;
      case 'pdf':
        return Icons.picture_as_pdf_rounded;
      case 'ebook':
        return Icons.menu_book_rounded;
      case 'coupon':
        return Icons.local_offer_rounded;
      case 'raffle':
        return Icons.confirmation_number_rounded;
      default:
        return Icons.stars_rounded;
    }
  }
}

class _TierSpec {
  const _TierSpec(this.tier, this.title, this.min, this.max, this.icon);
  final MemberTier tier;
  final String title;
  final int min;
  final int? max;
  final IconData icon;
}

/// Shimmering skeleton shown while the /points overview loads.
class _PointsSkeleton extends StatelessWidget {
  const _PointsSkeleton();

  @override
  Widget build(BuildContext context) {
    return _Shimmer(
      child: ListView(
        padding: const EdgeInsets.fromLTRB(16, 8, 16, 104),
        physics: const NeverScrollableScrollPhysics(),
        children: <Widget>[
          _bar(context, height: 120, radius: AppRadii.xl),
          const SizedBox(height: 18),
          _bar(context, height: 120),
          const SizedBox(height: 18),
          Row(
            children: <Widget>[
              Expanded(child: _bar(context, height: 96)),
              const SizedBox(width: 12),
              Expanded(child: _bar(context, height: 96)),
            ],
          ),
          const SizedBox(height: 18),
          _bar(context, height: 220),
        ],
      ),
    );
  }

  Widget _bar(BuildContext context,
      {required double height, double radius = AppRadii.lg}) {
    return Container(
      height: height,
      decoration: BoxDecoration(
        color: context.colors.surface2,
        borderRadius: BorderRadius.circular(radius),
      ),
    );
  }
}

/// A gentle opacity pulse used by the loading skeletons.
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
