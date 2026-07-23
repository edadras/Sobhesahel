import 'dart:math' as math;

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

/// Loads the /club payload (streak, today's missions, wheel state).
final clubProvider = FutureProvider.autoDispose<ClubData>((ref) {
  return ref.watch(clubRepositoryProvider).load();
});

/// Members club: login-streak, today's missions and the delightful daily
/// spin wheel. Bound to [ClubRepository] via [clubProvider].
class ClubScreen extends ConsumerWidget {
  const ClubScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final AppLocalizations t = AppLocalizations.of(context);
    final AsyncValue<ClubData> async = ref.watch(clubProvider);

    return GlassScaffold(
      appBar: GlassAppBar(
        title: t.clubTitle,
        subtitle: t.clubSubtitle,
      ),
      body: async.when(
        loading: () => const _ClubSkeleton(),
        error: (Object e, _) => ErrorRetry(
          message: e.toString(),
          onRetry: () => ref.invalidate(clubProvider),
        ),
        data: (ClubData d) => RefreshIndicator(
          onRefresh: () async => ref.invalidate(clubProvider),
          child: ListView(
            padding: const EdgeInsets.fromLTRB(16, 8, 16, 104),
            children: <Widget>[
              _StreakCard(streak: d.streak),
              const SizedBox(height: 18),
              _MissionsCard(missions: d.missions),
              const SizedBox(height: 18),
              _WheelCard(
                wheel: d.wheel,
                onSpin: () => ref.read(clubRepositoryProvider).spinWheel(),
                onSpun: () => ref.invalidate(clubProvider),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

// ---------------------------------------------------------------------------
// Streak
// ---------------------------------------------------------------------------

class _StreakCard extends StatelessWidget {
  const _StreakCard({required this.streak});
  final Streak streak;

  @override
  Widget build(BuildContext context) {
    final AppLocalizations t = AppLocalizations.of(context);
    final AppPalette c = context.colors;
    final int total = streak.nextMilestone <= 0
        ? streak.currentDays
        : math.min(streak.nextMilestone, 30);
    return GlassCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: <Widget>[
          Row(
            children: <Widget>[
              IconChip(
                  icon: Icons.local_fire_department_rounded,
                  color: c.gold,
                  size: 34,
                  radius: 9),
              const SizedBox(width: 10),
              Expanded(
                child: Text(t.clubStreak,
                    style: Theme.of(context).textTheme.titleMedium),
              ),
              StatusPill(
                t.dashStreakDays(streak.currentDays),
                color: c.gold,
                icon: Icons.bolt_rounded,
              ),
            ],
          ),
          const SizedBox(height: 16),
          Row(
            children: <Widget>[
              _stat(context, context.faNum(streak.currentDays),
                  t.clubCurrentStreak, c.brand),
              _stat(context, context.faNum(streak.longestDays),
                  t.clubLongestStreak, c.plum),
              _stat(
                  context,
                  '${context.faNum(streak.nextMilestone)} ${t.commonDay}',
                  t.clubNextMilestone,
                  c.gold),
            ],
          ),
          const SizedBox(height: 16),
          Wrap(
            spacing: 7,
            runSpacing: 7,
            children: <Widget>[
              for (int i = 0; i < total; i++)
                _dot(context, filled: i < streak.currentDays,
                    today: i == streak.currentDays - 1),
            ],
          ),
        ],
      ),
    );
  }

  Widget _stat(BuildContext context, String value, String label, Color color) {
    return Expanded(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: <Widget>[
          Text(
            value,
            style: Theme.of(context)
                .textTheme
                .headlineSmall
                ?.copyWith(color: color, fontWeight: FontWeight.w800),
          ),
          const SizedBox(height: 2),
          Text(label, style: Theme.of(context).textTheme.bodySmall),
        ],
      ),
    );
  }

  Widget _dot(BuildContext context, {required bool filled, required bool today}) {
    final AppPalette c = context.colors;
    final Color bg = today
        ? c.brand
        : filled
            ? c.brandSoft
            : c.surface2;
    final Color fg = today
        ? Colors.white
        : filled
            ? c.brand
            : c.textFaint;
    return Container(
      width: 30,
      height: 30,
      decoration: BoxDecoration(
        color: bg,
        borderRadius: BorderRadius.circular(9),
        border: Border.all(
          color: filled ? c.brand.withValues(alpha: 0.5) : c.border,
        ),
      ),
      child: Icon(
        filled ? Icons.check_rounded : Icons.circle_outlined,
        size: 15,
        color: fg,
      ),
    );
  }
}

// ---------------------------------------------------------------------------
// Missions
// ---------------------------------------------------------------------------

class _MissionsCard extends StatelessWidget {
  const _MissionsCard({required this.missions});
  final List<Mission> missions;

  @override
  Widget build(BuildContext context) {
    final AppLocalizations t = AppLocalizations.of(context);
    final AppPalette c = context.colors;
    final int done = missions.where((Mission m) => m.completed).length;
    return GlassCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: <Widget>[
          Row(
            children: <Widget>[
              IconChip(
                  icon: Icons.flag_rounded, color: c.brand, size: 34, radius: 9),
              const SizedBox(width: 10),
              Expanded(
                child: Text(t.clubDailyMissions,
                    style: Theme.of(context).textTheme.titleMedium),
              ),
            ],
          ),
          const SizedBox(height: 12),
          if (missions.isEmpty)
            const Padding(
              padding: EdgeInsets.symmetric(vertical: 16),
              child: EmptyState(icon: Icons.flag_outlined),
            )
          else
            for (final Mission m in missions) _missionTile(context, t, m),
          const SizedBox(height: 6),
          Container(
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: c.goldSoft.withValues(alpha: c.isDark ? 0.5 : 1),
              borderRadius: BorderRadius.circular(AppRadii.md),
            ),
            child: Row(
              children: <Widget>[
                IconChip(
                    icon: Icons.card_giftcard_rounded,
                    color: c.gold,
                    size: 38,
                    radius: 11),
                const SizedBox(width: 12),
                Expanded(
                  child: Text(t.dashMissionsRewardHint,
                      style: Theme.of(context).textTheme.bodySmall),
                ),
                const SizedBox(width: 8),
                StatusPill(
                  '${context.faNum(done)}/${context.faNum(missions.length)}',
                  color: c.gold,
                  icon: Icons.check_circle_rounded,
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _missionTile(BuildContext context, AppLocalizations t, Mission m) {
    final AppPalette c = context.colors;
    final Color color = m.completed ? c.success : c.plum;
    final double percent = m.goal <= 0
        ? 0.0
        : (m.progress / m.goal * 100).clamp(0, 100).toDouble();
    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: m.completed
            ? c.success.withValues(alpha: 0.08)
            : c.surface.withValues(alpha: c.isDark ? 0.25 : 0.5),
        borderRadius: BorderRadius.circular(AppRadii.md),
        border: Border.all(
          color: m.completed
              ? c.success.withValues(alpha: 0.35)
              : c.glassBorderFaint,
        ),
      ),
      child: Row(
        children: <Widget>[
          IconChip(
            icon: m.completed ? Icons.check_rounded : _missionIcon(m.code),
            color: color,
            size: 42,
            radius: 12,
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: <Widget>[
                Text(m.title,
                    style: Theme.of(context).textTheme.titleSmall),
                const SizedBox(height: 6),
                if (m.completed)
                  Text(
                    t.clubMissionCompleted,
                    style: Theme.of(context)
                        .textTheme
                        .bodySmall
                        ?.copyWith(color: c.success),
                  )
                else ...<Widget>[
                  GradientProgressBar(percent: percent, height: 6),
                  const SizedBox(height: 5),
                  Text(
                    t.clubMissionProgress(
                      context.faNum(m.progress),
                      context.faNum(m.goal),
                    ),
                    style: Theme.of(context).textTheme.bodySmall,
                  ),
                ],
              ],
            ),
          ),
          const SizedBox(width: 8),
          if (m.completed)
            Icon(Icons.verified_rounded, color: c.success, size: 22)
          else
            StatusPill('+${context.faNum(m.points)}', color: c.gold),
        ],
      ),
    );
  }

  IconData _missionIcon(String code) {
    switch (code) {
      case 'read_news':
        return Icons.visibility_rounded;
      case 'comment':
        return Icons.chat_bubble_outline_rounded;
      case 'share':
        return Icons.ios_share_rounded;
      default:
        return Icons.bolt_rounded;
    }
  }
}

// ---------------------------------------------------------------------------
// Daily wheel
// ---------------------------------------------------------------------------

class _WheelCard extends StatefulWidget {
  const _WheelCard({
    required this.wheel,
    required this.onSpin,
    required this.onSpun,
  });

  final WheelInfo wheel;
  final Future<WheelPrize> Function() onSpin;
  final VoidCallback onSpun;

  @override
  State<_WheelCard> createState() => _WheelCardState();
}

class _WheelCardState extends State<_WheelCard>
    with SingleTickerProviderStateMixin {
  static const int _segments = 8;

  late final AnimationController _ctrl = AnimationController(
    vsync: this,
    duration: const Duration(milliseconds: 4200),
  );
  Animation<double> _rotation = const AlwaysStoppedAnimation<double>(0);
  double _angle = 0;
  bool _spinning = false;

  @override
  void dispose() {
    _ctrl.dispose();
    super.dispose();
  }

  Future<void> _spin() async {
    if (_spinning) return;
    setState(() => _spinning = true);
    final AppLocalizations t = AppLocalizations.of(context);

    WheelPrize prize;
    try {
      prize = await widget.onSpin();
    } catch (e) {
      if (!mounted) return;
      setState(() => _spinning = false);
      ScaffoldMessenger.of(context)
        ..clearSnackBars()
        ..showSnackBar(
          SnackBar(
            content: Text(widget.wheel.freeAvailable
                ? t.commonError
                : t.clubNotEnoughPoints),
          ),
        );
      return;
    }

    // Land on a random segment (the server decides the actual prize, which we
    // reveal in the result dialog once the wheel settles).
    final int idx = math.Random().nextInt(_segments);
    const double seg = 2 * math.pi / _segments;
    final double target = _angle -
        (_angle % (2 * math.pi)) +
        (2 * math.pi * 5) +
        (2 * math.pi - (idx * seg + seg / 2));
    _rotation = Tween<double>(begin: _angle, end: target).animate(
      CurvedAnimation(parent: _ctrl, curve: Curves.easeOutQuart),
    );
    _ctrl.reset();
    try {
      await _ctrl.forward().orCancel;
    } catch (_) {
      // Controller disposed mid-spin; nothing to settle.
    }
    if (!mounted) return;
    _angle = target;
    setState(() => _spinning = false);
    await _showResult(prize);
    if (!mounted) return;
    widget.onSpun();
  }

  Future<void> _showResult(WheelPrize prize) async {
    final AppLocalizations t = AppLocalizations.of(context);
    final AppPalette c = context.colors;
    await showDialog<void>(
      context: context,
      barrierColor: Colors.black.withValues(alpha: 0.55),
      builder: (BuildContext context) {
        return Dialog(
          backgroundColor: Colors.transparent,
          insetPadding: const EdgeInsets.symmetric(horizontal: 32),
          child: GlassContainer(
            strong: true,
            padding: const EdgeInsets.all(24),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: <Widget>[
                Container(
                  width: 84,
                  height: 84,
                  decoration: BoxDecoration(
                    gradient: c.brandGradient,
                    shape: BoxShape.circle,
                    boxShadow: <BoxShadow>[
                      BoxShadow(
                        color: c.brand.withValues(alpha: 0.4),
                        blurRadius: 26,
                        offset: const Offset(0, 12),
                      ),
                    ],
                  ),
                  child: const Icon(Icons.celebration_rounded,
                      color: Colors.white, size: 40),
                ),
                const SizedBox(height: 18),
                Text(
                  t.clubPrizeWon(prize.title),
                  textAlign: TextAlign.center,
                  style: Theme.of(context)
                      .textTheme
                      .titleLarge
                      ?.copyWith(fontWeight: FontWeight.w800),
                ),
                const SizedBox(height: 10),
                StatusPill(
                  '${context.faNum(prize.pointsBalance)} ${t.commonPointsUnit}',
                  color: c.gold,
                  icon: Icons.monetization_on_rounded,
                ),
                const SizedBox(height: 20),
                GradientButton(
                  label: t.commonClose,
                  icon: Icons.check_rounded,
                  onPressed: () => Navigator.of(context).pop(),
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    final AppLocalizations t = AppLocalizations.of(context);
    final AppPalette c = context.colors;
    final List<Color> colors = <Color>[
      c.brand,
      c.plum,
      c.gold,
      c.brandDeep,
      c.plumDeep,
      c.brand,
      c.gold,
      c.plum,
    ];

    return GlassCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: <Widget>[
          Row(
            children: <Widget>[
              IconChip(
                  icon: Icons.casino_rounded,
                  color: c.plum,
                  size: 34,
                  radius: 9),
              const SizedBox(width: 10),
              Expanded(
                child: Text(t.clubSpinWheel,
                    style: Theme.of(context).textTheme.titleMedium),
              ),
              StatusPill(
                widget.wheel.freeAvailable
                    ? t.clubFreeSpinAvailable
                    : t.clubSpinCost(context.faNum(widget.wheel.cost)),
                color: widget.wheel.freeAvailable ? c.success : c.gold,
                icon: widget.wheel.freeAvailable
                    ? Icons.card_giftcard_rounded
                    : Icons.monetization_on_rounded,
              ),
            ],
          ),
          const SizedBox(height: 20),
          Center(
            child: SizedBox(
              width: 280,
              height: 280,
              child: Stack(
                alignment: Alignment.center,
                children: <Widget>[
                  AnimatedBuilder(
                    animation: _ctrl,
                    builder: (BuildContext context, Widget? child) {
                      final double a =
                          _ctrl.isAnimating ? _rotation.value : _angle;
                      return Transform.rotate(angle: a, child: child);
                    },
                    child: CustomPaint(
                      size: const Size.square(280),
                      painter: _WheelPainter(
                        colors: colors,
                        border: c.surface,
                        ring: c.brand,
                      ),
                    ),
                  ),
                  // Center hub (does not rotate).
                  Container(
                    width: 64,
                    height: 64,
                    decoration: BoxDecoration(
                      color: c.surface,
                      shape: BoxShape.circle,
                      boxShadow: <BoxShadow>[
                        BoxShadow(
                          color: Colors.black.withValues(alpha: 0.2),
                          blurRadius: 10,
                        ),
                      ],
                    ),
                    alignment: Alignment.center,
                    child: Text(
                      t.appName,
                      textAlign: TextAlign.center,
                      style: TextStyle(
                        color: c.brand,
                        fontSize: 11,
                        height: 1.2,
                        fontWeight: FontWeight.w800,
                        fontFamily: AppTheme.fontFamily,
                      ),
                    ),
                  ),
                  // Pointer at the top, pointing down into the wheel.
                  Positioned(
                    top: -2,
                    child: Icon(Icons.arrow_drop_down_rounded,
                        size: 46, color: c.brand),
                  ),
                ],
              ),
            ),
          ),
          const SizedBox(height: 20),
          GradientButton(
            label: widget.wheel.freeAvailable
                ? t.clubSpinNow
                : t.clubSpinCost(context.faNum(widget.wheel.cost)),
            icon: Icons.casino_rounded,
            loading: _spinning,
            onPressed: _spinning ? null : _spin,
          ),
          const SizedBox(height: 8),
          Center(
            child: Text(
              widget.wheel.freeAvailable
                  ? t.clubFreeSpinAvailable
                  : t.clubComeBackTomorrow,
              style: Theme.of(context).textTheme.bodySmall,
            ),
          ),
        ],
      ),
    );
  }
}

class _WheelPainter extends CustomPainter {
  _WheelPainter({
    required this.colors,
    required this.border,
    required this.ring,
  });

  final List<Color> colors;
  final Color border;
  final Color ring;

  @override
  void paint(Canvas canvas, Size size) {
    final Offset center = size.center(Offset.zero);
    final double radius = size.width / 2;
    final int n = colors.length;
    final double sweep = 2 * math.pi / n;
    final Rect rect = Rect.fromCircle(center: center, radius: radius - 8);

    // Outer ring.
    canvas.drawCircle(
      center,
      radius,
      Paint()..color = ring,
    );
    canvas.drawCircle(
      center,
      radius - 4,
      Paint()..color = border,
    );

    for (int i = 0; i < n; i++) {
      final Paint p = Paint()
        ..style = PaintingStyle.fill
        ..color = colors[i];
      final double start = -math.pi / 2 + i * sweep;
      canvas.drawArc(rect, start, sweep, true, p);
      // Slice separator.
      canvas.drawArc(
        rect,
        start,
        sweep,
        true,
        Paint()
          ..style = PaintingStyle.stroke
          ..strokeWidth = 2
          ..color = border.withValues(alpha: 0.85),
      );
      // A small decorative dot near the outer edge of each slice.
      final double mid = start + sweep / 2;
      final Offset dot = center +
          Offset(math.cos(mid), math.sin(mid)) * (radius - 26);
      canvas.drawCircle(
        dot,
        4,
        Paint()..color = Colors.white.withValues(alpha: 0.9),
      );
    }
  }

  @override
  bool shouldRepaint(covariant _WheelPainter old) =>
      old.colors != colors || old.border != border || old.ring != ring;
}

// ---------------------------------------------------------------------------
// Loading skeleton
// ---------------------------------------------------------------------------

class _ClubSkeleton extends StatelessWidget {
  const _ClubSkeleton();

  @override
  Widget build(BuildContext context) {
    return _Shimmer(
      child: ListView(
        padding: const EdgeInsets.fromLTRB(16, 8, 16, 104),
        physics: const NeverScrollableScrollPhysics(),
        children: <Widget>[
          _bar(context, height: 150),
          const SizedBox(height: 18),
          _bar(context, height: 240),
          const SizedBox(height: 18),
          _bar(context, height: 420),
        ],
      ),
    );
  }

  Widget _bar(BuildContext context, {required double height}) {
    return Container(
      height: height,
      decoration: BoxDecoration(
        color: context.colors.surface2,
        borderRadius: BorderRadius.circular(AppRadii.lg),
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
