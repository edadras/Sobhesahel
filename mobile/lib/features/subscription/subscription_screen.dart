import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:shimmer/shimmer.dart';

import '../../core/persian.dart';
import '../../data/models/models.dart';
import '../../data/providers.dart';
import '../../data/repositories/repositories.dart';
import '../../l10n/app_localizations.dart';
import '../../theme/app_colors.dart';
import '../../theme/app_theme.dart';
import '../../theme/glass.dart';
import '../../widgets/common.dart';

/// Async provider wrapping [SubscriptionRepository.overview]. Lives in the
/// feature folder (not the data-layer core).
final subscriptionOverviewProvider =
    FutureProvider.autoDispose<SubscriptionOverview>((ref) {
  return ref.watch(subscriptionRepositoryProvider).overview();
});

bool _isYearly(SubscriptionPlan p) => p.durationDays >= 300;

/// My subscription: current plan state with a days-left ring, selectable plan
/// cards (monthly/yearly with a savings badge), cash / points purchase flows
/// and a "request consultation" contact action.
class SubscriptionScreen extends ConsumerWidget {
  const SubscriptionScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final AppLocalizations t = AppLocalizations.of(context);
    final AsyncValue<SubscriptionOverview> async =
        ref.watch(subscriptionOverviewProvider);

    return GlassScaffold(
      appBar: GlassAppBar(title: t.subTitle),
      body: async.when(
        loading: () => const _Skeleton(),
        error: (Object e, _) => ErrorRetry(
          message: e.toString(),
          onRetry: () => ref.invalidate(subscriptionOverviewProvider),
        ),
        data: (SubscriptionOverview data) => RefreshIndicator(
          onRefresh: () async =>
              ref.invalidate(subscriptionOverviewProvider),
          child: ListView(
            padding: const EdgeInsets.fromLTRB(16, 8, 16, 104),
            children: <Widget>[
              _CurrentCard(current: data.current),
              const SizedBox(height: 20),
              SectionTitle(t.subChoosePlan),
              for (final SubscriptionPlan plan in data.plans) ...<Widget>[
                _PlanCard(plan: plan),
                const SizedBox(height: 14),
              ],
              const SizedBox(height: 6),
              _ConsultCard(),
            ],
          ),
        ),
      ),
    );
  }
}

// ---------------------------------------------------------------------------
// Current subscription state
// ---------------------------------------------------------------------------

class _CurrentCard extends StatelessWidget {
  const _CurrentCard({required this.current});

  final ActiveSubscription? current;

  @override
  Widget build(BuildContext context) {
    final AppLocalizations t = AppLocalizations.of(context);
    final AppPalette c = context.colors;
    final ActiveSubscription? sub = current;

    if (sub == null) {
      return BrandHeroCard(
        child: Row(
          children: <Widget>[
            const Icon(Icons.workspace_premium_rounded,
                color: Colors.white, size: 40),
            const SizedBox(width: 16),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: <Widget>[
                  Text(
                    t.subNoActive,
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 18,
                      fontWeight: FontWeight.w800,
                      fontFamily: AppTheme.fontFamily,
                    ),
                  ),
                  const SizedBox(height: 6),
                  Text(
                    t.subChoosePlan,
                    style:
                        TextStyle(color: Colors.white.withValues(alpha: 0.9)),
                  ),
                ],
              ),
            ),
          ],
        ),
      );
    }

    return GlassCard(
      strong: true,
      child: Row(
        children: <Widget>[
          ProgressRing(
            size: 92,
            stroke: 8,
            percent: (sub.daysLeft / 30 * 100).clamp(0, 100).toDouble(),
            color: sub.isActive ? c.gold : c.textFaint,
            center: Column(
              mainAxisSize: MainAxisSize.min,
              children: <Widget>[
                Text(
                  context.faNum(sub.daysLeft),
                  style: Theme.of(context)
                      .textTheme
                      .titleLarge
                      ?.copyWith(fontWeight: FontWeight.w800),
                ),
                Text(t.commonDays,
                    style: Theme.of(context).textTheme.bodySmall),
              ],
            ),
          ),
          const SizedBox(width: 18),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: <Widget>[
                Text(t.subCurrentPlan,
                    style: Theme.of(context).textTheme.bodySmall),
                const SizedBox(height: 6),
                Text(sub.planTitle,
                    style: Theme.of(context).textTheme.titleLarge),
                const SizedBox(height: 10),
                Row(
                  children: <Widget>[
                    StatusPill(
                      sub.isActive ? t.subActive : t.subExpired,
                      color: sub.isActive ? c.success : c.accentRed,
                      icon: sub.isActive
                          ? Icons.check_circle_rounded
                          : Icons.error_outline_rounded,
                    ),
                  ],
                ),
                const SizedBox(height: 10),
                Text(
                  t.subExpiresOn(sub.endsAtJalali),
                  style: Theme.of(context).textTheme.bodySmall,
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

// ---------------------------------------------------------------------------
// Plan card
// ---------------------------------------------------------------------------

class _PlanCard extends StatelessWidget {
  const _PlanCard({required this.plan});

  final SubscriptionPlan plan;

  @override
  Widget build(BuildContext context) {
    final AppLocalizations t = AppLocalizations.of(context);
    final AppPalette c = context.colors;
    final bool featured = plan.badge != null && plan.badge!.trim().isNotEmpty;
    final bool yearly = _isYearly(plan);

    return GlassCard(
      strong: featured,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: <Widget>[
          Row(
            children: <Widget>[
              IconChip(
                icon: Icons.workspace_premium_rounded,
                color: featured ? c.brand : c.plum,
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Text(plan.name,
                    style: Theme.of(context).textTheme.titleMedium),
              ),
              if (featured)
                StatusPill(plan.badge!,
                    color: c.brand, icon: Icons.star_rounded),
            ],
          ),
          const SizedBox(height: 16),
          Row(
            crossAxisAlignment: CrossAxisAlignment.end,
            children: <Widget>[
              Text(
                context.faNum(plan.price),
                style: Theme.of(context)
                    .textTheme
                    .headlineMedium
                    ?.copyWith(fontWeight: FontWeight.w900),
              ),
              const SizedBox(width: 6),
              Padding(
                padding: const EdgeInsets.only(bottom: 4),
                child: Text(
                  '${t.commonToman} ${yearly ? t.subPerYear : t.subPerMonth}',
                  style: Theme.of(context).textTheme.bodySmall,
                ),
              ),
              const Spacer(),
              if (yearly)
                StatusPill(t.subTwoMonthsFree,
                    color: c.success, icon: Icons.savings_rounded),
            ],
          ),
          if (plan.pointsPrice != null) ...<Widget>[
            const SizedBox(height: 8),
            Row(
              children: <Widget>[
                Icon(Icons.monetization_on_rounded, size: 16, color: c.gold),
                const SizedBox(width: 6),
                Text(
                  '${context.faNum(plan.pointsPrice!)} ${t.commonPoints}',
                  style: Theme.of(context)
                      .textTheme
                      .bodyMedium
                      ?.copyWith(color: c.gold, fontWeight: FontWeight.w700),
                ),
              ],
            ),
          ],
          const Divider(height: 26),
          for (final String feature in plan.features)
            Padding(
              padding: const EdgeInsets.symmetric(vertical: 5),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: <Widget>[
                  Icon(Icons.check_circle_rounded,
                      size: 18, color: c.success),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Text(feature,
                        style: Theme.of(context).textTheme.bodyMedium),
                  ),
                ],
              ),
            ),
          const SizedBox(height: 18),
          GradientButton(
            label: t.subSubscribe,
            icon: Icons.workspace_premium_rounded,
            onPressed: () => showModalBottomSheet<void>(
              context: context,
              isScrollControlled: true,
              builder: (_) => _PurchaseSheet(plan: plan),
            ),
          ),
        ],
      ),
    );
  }
}

// ---------------------------------------------------------------------------
// Consultation contact
// ---------------------------------------------------------------------------

class _ConsultCard extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    final AppLocalizations t = AppLocalizations.of(context);
    final AppPalette c = context.colors;
    return GlassCard(
      onTap: () => showDialog<void>(
        context: context,
        builder: (BuildContext ctx) => AlertDialog(
          title: Text(t.subConsult),
          content: Text(t.subConsultDesc),
          actions: <Widget>[
            TextButton(
              onPressed: () => Navigator.of(ctx).pop(),
              child: Text(t.commonClose),
            ),
          ],
        ),
      ),
      child: Row(
        children: <Widget>[
          IconChip(icon: Icons.support_agent_rounded, color: c.plum, size: 46),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: <Widget>[
                Text(t.subConsult,
                    style: Theme.of(context).textTheme.titleSmall),
                const SizedBox(height: 4),
                Text(t.subConsultDesc,
                    style: Theme.of(context).textTheme.bodySmall),
              ],
            ),
          ),
          const SizedBox(width: 8),
          Icon(Icons.chevron_left_rounded, color: c.textFaint),
        ],
      ),
    );
  }
}

// ---------------------------------------------------------------------------
// Purchase sheet (cash / points)
// ---------------------------------------------------------------------------

class _PurchaseSheet extends ConsumerStatefulWidget {
  const _PurchaseSheet({required this.plan});

  final SubscriptionPlan plan;

  @override
  ConsumerState<_PurchaseSheet> createState() => _PurchaseSheetState();
}

class _PurchaseSheetState extends ConsumerState<_PurchaseSheet> {
  final TextEditingController _refController = TextEditingController();

  bool _busy = false;

  // Cash flow state, populated once the payment is created.
  String? _paymentToken;
  String? _instructions;
  String? _cardInfo;

  SubscriptionPlan get plan => widget.plan;

  @override
  void dispose() {
    _refController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final AppLocalizations t = AppLocalizations.of(context);
    final AppPalette c = context.colors;
    final bool payingCash = _paymentToken != null;

    return Padding(
      padding: EdgeInsets.only(
        left: 20,
        right: 20,
        top: 18,
        bottom: MediaQuery.of(context).viewInsets.bottom + 24,
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.start,
        children: <Widget>[
          Center(
            child: Container(
              width: 44,
              height: 4,
              decoration: BoxDecoration(
                color: c.borderStrong,
                borderRadius: BorderRadius.circular(2),
              ),
            ),
          ),
          const SizedBox(height: 18),
          Text(plan.name, style: Theme.of(context).textTheme.titleLarge),
          const SizedBox(height: 4),
          Text(
            '${context.faNum(plan.price)} ${t.commonToman}',
            style: Theme.of(context)
                .textTheme
                .titleMedium
                ?.copyWith(fontWeight: FontWeight.w800),
          ),
          const SizedBox(height: 18),
          if (!payingCash) ...<Widget>[
            GradientButton(
              label: t.subPayWithCash,
              icon: Icons.credit_card_rounded,
              loading: _busy,
              onPressed: _busy ? null : _startCash,
            ),
            if (plan.pointsPrice != null) ...<Widget>[
              const SizedBox(height: 12),
              OutlinedButton.icon(
                onPressed: _busy ? null : _payWithPoints,
                icon: const Icon(Icons.monetization_on_rounded, size: 18),
                label: Text(
                  '${t.subPayWithPoints} · ${context.faNum(plan.pointsPrice!)} ${t.commonPoints}',
                ),
              ),
            ],
          ] else ...<Widget>[
            Text(t.subPaymentInstructions,
                style: Theme.of(context).textTheme.titleMedium),
            const SizedBox(height: 8),
            if (_instructions != null)
              Text(_instructions!,
                  style: Theme.of(context)
                      .textTheme
                      .bodyMedium
                      ?.copyWith(height: 1.8)),
            if (_cardInfo != null) ...<Widget>[
              const SizedBox(height: 12),
              GlassCard(
                padding: const EdgeInsets.all(14),
                child: Row(
                  children: <Widget>[
                    Icon(Icons.credit_card_rounded,
                        size: 20, color: c.brand),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Text(_cardInfo!,
                          style: Theme.of(context).textTheme.titleSmall),
                    ),
                  ],
                ),
              ),
            ],
            const SizedBox(height: 16),
            TextField(
              controller: _refController,
              keyboardType: TextInputType.number,
              decoration: InputDecoration(
                labelText: t.subRefCode,
                prefixIcon: const Icon(Icons.confirmation_number_outlined),
              ),
            ),
            const SizedBox(height: 18),
            GradientButton(
              label: t.subConfirmPayment,
              icon: Icons.check_rounded,
              loading: _busy,
              onPressed: _busy ? null : _confirmCash,
            ),
          ],
          const SizedBox(height: 8),
          Center(
            child: TextButton(
              onPressed: _busy ? null : () => Navigator.of(context).pop(),
              child: Text(t.commonCancel),
            ),
          ),
        ],
      ),
    );
  }

  Future<void> _startCash() async {
    setState(() => _busy = true);
    try {
      final Map<String, dynamic> res =
          await ref.read(subscriptionRepositoryProvider).purchase(
                planId: plan.id,
                payWith: PayWith.cash,
              );
      final Map<String, dynamic> payment =
          (res['payment'] as Map?)?.cast<String, dynamic>() ??
              const <String, dynamic>{};
      if (!mounted) return;
      setState(() {
        _paymentToken = payment['token']?.toString() ?? '';
        _instructions = payment['instructions']?.toString();
        _cardInfo = payment['card_info']?.toString();
        _busy = false;
      });
    } catch (_) {
      if (!mounted) return;
      setState(() => _busy = false);
      _snack(AppLocalizations.of(context).commonError);
    }
  }

  Future<void> _confirmCash() async {
    final AppLocalizations t = AppLocalizations.of(context);
    setState(() => _busy = true);
    try {
      await ref.read(subscriptionRepositoryProvider).confirmPayment(
            paymentToken: _paymentToken ?? '',
            refCode: _refController.text.trim(),
          );
      ref.invalidate(subscriptionOverviewProvider);
      if (!mounted) return;
      Navigator.of(context).pop();
      _snack(t.subManualReview, success: true);
    } catch (_) {
      if (!mounted) return;
      setState(() => _busy = false);
      _snack(t.commonError);
    }
  }

  Future<void> _payWithPoints() async {
    final AppLocalizations t = AppLocalizations.of(context);
    setState(() => _busy = true);
    try {
      final Map<String, dynamic> res =
          await ref.read(subscriptionRepositoryProvider).purchase(
                planId: plan.id,
                payWith: PayWith.points,
              );
      ref.invalidate(subscriptionOverviewProvider);
      if (!mounted) return;
      Navigator.of(context).pop();
      final bool activated = res['activated'] == true;
      _snack(activated ? t.subActivated : t.commonSavedSuccessfully,
          success: true);
    } catch (_) {
      // 422 → not enough points.
      if (!mounted) return;
      setState(() => _busy = false);
      _snack(t.clubNotEnoughPoints);
    }
  }

  void _snack(String message, {bool success = false}) {
    final AppPalette c = context.colors;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: success ? c.success : null,
      ),
    );
  }
}

// ---------------------------------------------------------------------------
// Loading skeleton
// ---------------------------------------------------------------------------

class _Skeleton extends StatelessWidget {
  const _Skeleton();

  @override
  Widget build(BuildContext context) {
    final AppPalette c = context.colors;
    Widget box(double h, {double? w}) => Container(
          height: h,
          width: w,
          margin: const EdgeInsets.only(bottom: 14),
          decoration: BoxDecoration(
            color: c.surface2,
            borderRadius: BorderRadius.circular(16),
          ),
        );
    return Shimmer.fromColors(
      baseColor: c.surface2,
      highlightColor: c.isDark
          ? Colors.white.withValues(alpha: 0.06)
          : Colors.white.withValues(alpha: 0.85),
      child: ListView(
        padding: const EdgeInsets.fromLTRB(16, 8, 16, 104),
        physics: const NeverScrollableScrollPhysics(),
        children: <Widget>[
          box(120, w: double.infinity),
          const SizedBox(height: 8),
          box(220, w: double.infinity),
          box(220, w: double.infinity),
        ],
      ),
    );
  }
}
