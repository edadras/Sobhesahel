import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../data/models/models.dart';
import '../../data/providers.dart';
import '../../data/repositories/repositories.dart';
import '../../l10n/app_localizations.dart';
import '../../theme/app_colors.dart';
import '../../theme/app_theme.dart';
import '../../theme/glass.dart';
import '../../widgets/common.dart';
import 'shop_providers.dart';
import 'widgets/shop_widgets.dart';

/// Product detail: hero image, description, price/points and the cash /
/// points purchase actions. Bound to [ShopRepository] via
/// [shopProductProvider]. The `slug` route param is passed by the router.
class ShopDetailScreen extends ConsumerWidget {
  const ShopDetailScreen({super.key, required this.slug});

  final String slug;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final AppLocalizations t = AppLocalizations.of(context);
    final AsyncValue<Product> async = ref.watch(shopProductProvider(slug));
    final Product? product = async.valueOrNull;

    return GlassScaffold(
      appBar: GlassAppBar(title: t.shopProductDetails),
      bottomNavigationBar:
          product == null ? null : _BuyBar(product: product),
      body: async.when(
        loading: () => const _DetailSkeleton(),
        error: (Object e, _) => ErrorRetry(
          message: e.toString(),
          onRetry: () => ref.invalidate(shopProductProvider(slug)),
        ),
        data: (Product p) => _Detail(product: p),
      ),
    );
  }
}

class _Detail extends StatelessWidget {
  const _Detail({required this.product});

  final Product product;

  @override
  Widget build(BuildContext context) {
    final AppLocalizations t = AppLocalizations.of(context);
    final AppPalette c = context.colors;
    return ListView(
      padding: const EdgeInsets.fromLTRB(16, 8, 16, 24),
      children: <Widget>[
        Hero(
          tag: 'product-${product.slug}',
          child: ProductThumb(
            imageUrl: product.imageUrl,
            type: product.type,
            height: 240,
            iconSize: 72,
            borderRadius: BorderRadius.circular(AppRadii.xl),
          ),
        ),
        const SizedBox(height: 18),
        Row(
          children: <Widget>[
            StatusPill(
              productTypeLabel(t, product.type),
              color: c.plum,
              icon: productTypeIcon(product.type),
            ),
            const SizedBox(width: 8),
            StatusPill(
              product.inStock ? t.shopInStock : t.shopOutOfStock,
              color: product.inStock ? c.success : c.accentRed,
              icon: product.inStock
                  ? Icons.check_circle_rounded
                  : Icons.remove_shopping_cart_rounded,
            ),
          ],
        ),
        const SizedBox(height: 14),
        Text(product.title, style: Theme.of(context).textTheme.headlineSmall),
        const SizedBox(height: 16),
        GlassCard(
          child: Row(
            children: <Widget>[
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: <Widget>[
                    Text(t.shopPrice,
                        style: Theme.of(context).textTheme.bodySmall),
                    const SizedBox(height: 4),
                    PriceText(
                      amount: product.price,
                      style: Theme.of(context)
                          .textTheme
                          .titleLarge
                          ?.copyWith(fontWeight: FontWeight.w800),
                    ),
                  ],
                ),
              ),
              if (product.pointsPrice != null)
                Column(
                  crossAxisAlignment: CrossAxisAlignment.end,
                  children: <Widget>[
                    Text(t.shopPointsPrice,
                        style: Theme.of(context).textTheme.bodySmall),
                    const SizedBox(height: 4),
                    PriceText(
                      amount: product.pointsPrice!,
                      points: true,
                      color: c.gold,
                      style: Theme.of(context)
                          .textTheme
                          .titleLarge
                          ?.copyWith(fontWeight: FontWeight.w800),
                    ),
                  ],
                ),
            ],
          ),
        ),
        const SizedBox(height: 18),
        SectionTitle(t.shopProductDetails),
        Text(
          product.description,
          style: Theme.of(context)
              .textTheme
              .bodyLarge
              ?.copyWith(color: c.textMuted, height: 1.9),
        ),
      ],
    );
  }
}

class _DetailSkeleton extends StatelessWidget {
  const _DetailSkeleton();

  @override
  Widget build(BuildContext context) {
    return ListView(
      padding: const EdgeInsets.fromLTRB(16, 8, 16, 24),
      children: const <Widget>[
        ShimmerBox(height: 240, radius: 24),
        SizedBox(height: 18),
        ShimmerBox(height: 22, width: 120),
        SizedBox(height: 14),
        ShimmerBox(height: 26),
        SizedBox(height: 16),
        ShimmerBox(height: 80, radius: 20),
        SizedBox(height: 18),
        ShimmerBox(height: 14),
        SizedBox(height: 8),
        ShimmerBox(height: 14),
        SizedBox(height: 8),
        ShimmerBox(height: 14, width: 200),
      ],
    );
  }
}

// ---------------------------------------------------------------------------
// Purchase action bar
// ---------------------------------------------------------------------------

class _BuyBar extends ConsumerStatefulWidget {
  const _BuyBar({required this.product});

  final Product product;

  @override
  ConsumerState<_BuyBar> createState() => _BuyBarState();
}

class _BuyBarState extends ConsumerState<_BuyBar> {
  bool _busy = false;

  Product get product => widget.product;

  @override
  Widget build(BuildContext context) {
    final AppLocalizations t = AppLocalizations.of(context);
    final AppPalette c = context.colors;
    final bool enabled = product.inStock && !_busy;

    return SafeArea(
      top: false,
      child: Padding(
        padding: const EdgeInsets.fromLTRB(16, 8, 16, 12),
        child: GlassContainer(
          padding: const EdgeInsets.all(14),
          child: Row(
            children: <Widget>[
              Expanded(
                child: GradientButton(
                  label: t.shopBuyNow,
                  icon: Icons.shopping_bag_rounded,
                  loading: _busy,
                  onPressed: enabled ? _buyWithCash : null,
                ),
              ),
              if (product.pointsPrice != null) ...<Widget>[
                const SizedBox(width: 12),
                Expanded(
                  child: _OutlineAction(
                    label: t.shopBuyWithPoints,
                    icon: Icons.monetization_on_rounded,
                    color: c.gold,
                    enabled: enabled,
                    onPressed: _buyWithPoints,
                  ),
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }

  Future<Order?> _placeOrder(PayWith payWith) {
    return ref.read(shopRepositoryProvider).createOrder(
          productId: product.id,
          payWith: payWith,
        );
  }

  Future<void> _buyWithCash() async {
    final String? refCode = await showModalBottomSheet<String>(
      context: context,
      isScrollControlled: true,
      builder: (_) => _CashSheet(product: product),
    );
    if (refCode == null || !mounted) return; // dismissed
    setState(() => _busy = true);
    try {
      await _placeOrder(PayWith.cash);
      ref.invalidate(shopOrdersProvider);
      if (!mounted) return;
      _snack(AppLocalizations.of(context).subManualReview, success: true);
    } catch (_) {
      if (mounted) _snack(AppLocalizations.of(context).commonError);
    } finally {
      if (mounted) setState(() => _busy = false);
    }
  }

  Future<void> _buyWithPoints() async {
    final AppLocalizations t = AppLocalizations.of(context);
    final bool ok = await showDialog<bool>(
          context: context,
          builder: (BuildContext ctx) => AlertDialog(
            title: Text(t.shopConfirmPurchase),
            content: Text(
              t.shopConfirmPointsMessage(product.title),
            ),
            actions: <Widget>[
              TextButton(
                onPressed: () => Navigator.of(ctx).pop(false),
                child: Text(t.commonCancel),
              ),
              TextButton(
                onPressed: () => Navigator.of(ctx).pop(true),
                child: Text(t.commonConfirm),
              ),
            ],
          ),
        ) ??
        false;
    if (!ok || !mounted) return;
    setState(() => _busy = true);
    try {
      await _placeOrder(PayWith.points);
      ref.invalidate(shopOrdersProvider);
      if (!mounted) return;
      _snack(t.commonSavedSuccessfully, success: true);
    } catch (_) {
      // The purchase endpoint returns 422 when the member lacks the points.
      if (mounted) _snack(t.clubNotEnoughPoints);
    } finally {
      if (mounted) setState(() => _busy = false);
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

class _OutlineAction extends StatelessWidget {
  const _OutlineAction({
    required this.label,
    required this.icon,
    required this.color,
    required this.enabled,
    required this.onPressed,
  });

  final String label;
  final IconData icon;
  final Color color;
  final bool enabled;
  final VoidCallback onPressed;

  @override
  Widget build(BuildContext context) {
    return Opacity(
      opacity: enabled ? 1 : 0.5,
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          borderRadius: BorderRadius.circular(AppRadii.pill),
          onTap: enabled ? onPressed : null,
          child: Container(
            height: 52,
            alignment: Alignment.center,
            decoration: BoxDecoration(
              color: color.withValues(alpha: 0.14),
              borderRadius: BorderRadius.circular(AppRadii.pill),
              border: Border.all(color: color.withValues(alpha: 0.5)),
            ),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: <Widget>[
                Icon(icon, size: 18, color: color),
                const SizedBox(width: 8),
                Flexible(
                  child: Text(
                    label,
                    overflow: TextOverflow.ellipsis,
                    style: TextStyle(
                      color: color,
                      fontWeight: FontWeight.w700,
                      fontSize: 14,
                      fontFamily: AppTheme.fontFamily,
                    ),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

/// Cash-payment sheet: instructions + tracking-code entry. Returns the entered
/// reference code (or empty string) when confirmed; null when dismissed.
class _CashSheet extends StatefulWidget {
  const _CashSheet({required this.product});

  final Product product;

  @override
  State<_CashSheet> createState() => _CashSheetState();
}

class _CashSheetState extends State<_CashSheet> {
  final TextEditingController _controller = TextEditingController();

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final AppLocalizations t = AppLocalizations.of(context);
    final AppPalette c = context.colors;
    return Padding(
      padding: EdgeInsets.only(
        left: 20,
        right: 20,
        top: 20,
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
          Text(t.subPaymentInstructions,
              style: Theme.of(context).textTheme.titleLarge),
          const SizedBox(height: 8),
          Text(
            t.shopCashInstructions,
            style: Theme.of(context)
                .textTheme
                .bodyMedium
                ?.copyWith(height: 1.8),
          ),
          const SizedBox(height: 12),
          GlassCard(
            padding: const EdgeInsets.all(14),
            child: Row(
              children: <Widget>[
                Expanded(
                  child: Text(widget.product.title,
                      style: Theme.of(context).textTheme.titleSmall),
                ),
                PriceText(amount: widget.product.price),
              ],
            ),
          ),
          const SizedBox(height: 16),
          TextField(
            controller: _controller,
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
            onPressed: () =>
                Navigator.of(context).pop(_controller.text.trim()),
          ),
          const SizedBox(height: 8),
          Center(
            child: TextButton(
              onPressed: () => Navigator.of(context).pop(),
              child: Text(t.commonCancel),
            ),
          ),
        ],
      ),
    );
  }
}
