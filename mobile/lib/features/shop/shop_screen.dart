import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../data/models/models.dart';
import '../../l10n/app_localizations.dart';
import '../../theme/app_colors.dart';
import '../../theme/app_theme.dart';
import '../../theme/glass.dart';
import '../../widgets/common.dart';
import 'shop_providers.dart';
import 'widgets/shop_widgets.dart';

/// The digital shop: a product grid with a sort control, plus a "my orders"
/// tab showing order status and download links for delivered digital items.
/// Bound to [ShopRepository] via [shopProductsProvider] / [shopOrdersProvider].
/// This is a primary bottom-nav tab.
class ShopScreen extends ConsumerStatefulWidget {
  const ShopScreen({super.key});

  @override
  ConsumerState<ShopScreen> createState() => _ShopScreenState();
}

enum _Tab { products, orders }

class _ShopScreenState extends ConsumerState<ShopScreen> {
  _Tab _tab = _Tab.products;
  String _sort = 'newest';

  @override
  Widget build(BuildContext context) {
    final AppLocalizations t = AppLocalizations.of(context);
    return GlassScaffold(
      appBar: GlassAppBar(
        title: t.shopTitle,
        subtitle: t.shopSubtitle,
        automaticallyImplyLeading: false,
      ),
      body: Column(
        children: <Widget>[
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 8, 16, 4),
            child: _SegmentedTabs(
              current: _tab,
              onChanged: (_Tab v) => setState(() => _tab = v),
            ),
          ),
          Expanded(
            child: _tab == _Tab.products
                ? _ProductsTab(
                    sort: _sort,
                    onSortChanged: (String s) => setState(() => _sort = s),
                  )
                : const _OrdersTab(),
          ),
        ],
      ),
    );
  }
}

// ---------------------------------------------------------------------------
// Segmented tab switch
// ---------------------------------------------------------------------------

class _SegmentedTabs extends StatelessWidget {
  const _SegmentedTabs({required this.current, required this.onChanged});

  final _Tab current;
  final ValueChanged<_Tab> onChanged;

  @override
  Widget build(BuildContext context) {
    final AppLocalizations t = AppLocalizations.of(context);
    final AppPalette c = context.colors;
    Widget seg(_Tab tab, String label, IconData icon) {
      final bool active = tab == current;
      return Expanded(
        child: GestureDetector(
          onTap: () => onChanged(tab),
          child: AnimatedContainer(
            duration: const Duration(milliseconds: 200),
            padding: const EdgeInsets.symmetric(vertical: 11),
            decoration: BoxDecoration(
              gradient: active ? c.brandGradient : null,
              borderRadius: BorderRadius.circular(AppRadii.pill),
            ),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: <Widget>[
                Icon(icon, size: 17, color: active ? Colors.white : c.textMuted),
                const SizedBox(width: 7),
                Text(
                  label,
                  style: TextStyle(
                    color: active ? Colors.white : c.textMuted,
                    fontWeight: FontWeight.w700,
                    fontSize: 13.5,
                    fontFamily: AppTheme.fontFamily,
                  ),
                ),
              ],
            ),
          ),
        ),
      );
    }

    return GlassContainer(
      padding: const EdgeInsets.all(5),
      borderRadius: BorderRadius.circular(AppRadii.pill),
      child: Row(
        children: <Widget>[
          seg(_Tab.products, t.navShop, Icons.grid_view_rounded),
          seg(_Tab.orders, t.shopMyOrders, Icons.receipt_long_rounded),
        ],
      ),
    );
  }
}

// ---------------------------------------------------------------------------
// Products tab
// ---------------------------------------------------------------------------

class _ProductsTab extends ConsumerWidget {
  const _ProductsTab({required this.sort, required this.onSortChanged});

  final String sort;
  final ValueChanged<String> onSortChanged;

  static const SliverGridDelegateWithFixedCrossAxisCount _grid =
      SliverGridDelegateWithFixedCrossAxisCount(
    crossAxisCount: 2,
    mainAxisSpacing: 14,
    crossAxisSpacing: 14,
    childAspectRatio: 0.62,
  );

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final AppLocalizations t = AppLocalizations.of(context);
    final AsyncValue<List<Product>> async =
        ref.watch(shopProductsProvider(sort));

    return RefreshIndicator(
      onRefresh: () async => ref.invalidate(shopProductsProvider(sort)),
      child: CustomScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        slivers: <Widget>[
          SliverPadding(
            padding: const EdgeInsets.fromLTRB(16, 10, 16, 10),
            sliver: SliverToBoxAdapter(
              child: _SortBar(sort: sort, onChanged: onSortChanged),
            ),
          ),
          async.when(
            loading: () => _skeletonSliver(),
            error: (Object e, _) => SliverToBoxAdapter(
              child: Padding(
                padding: const EdgeInsets.only(top: 100),
                child: ErrorRetry(
                  message: e.toString(),
                  onRetry: () => ref.invalidate(shopProductsProvider(sort)),
                ),
              ),
            ),
            data: (List<Product> products) {
              if (products.isEmpty) {
                return SliverToBoxAdapter(
                  child: Padding(
                    padding: const EdgeInsets.only(top: 100),
                    child: EmptyState(message: t.commonEmpty),
                  ),
                );
              }
              return SliverPadding(
                padding: const EdgeInsets.fromLTRB(16, 0, 16, 104),
                sliver: SliverGrid(
                  gridDelegate: _grid,
                  delegate: SliverChildBuilderDelegate(
                    (_, int i) => _ProductCard(product: products[i]),
                    childCount: products.length,
                  ),
                ),
              );
            },
          ),
        ],
      ),
    );
  }

  Widget _skeletonSliver() {
    return SliverPadding(
      padding: const EdgeInsets.fromLTRB(16, 0, 16, 104),
      sliver: SliverGrid(
        gridDelegate: _grid,
        delegate: SliverChildBuilderDelegate(
          (_, __) => const GlassCard(
            padding: EdgeInsets.all(10),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: <Widget>[
                Expanded(child: ShimmerBox(radius: 14)),
                SizedBox(height: 10),
                ShimmerBox(height: 12),
                SizedBox(height: 6),
                ShimmerBox(height: 12, width: 70),
              ],
            ),
          ),
          childCount: 6,
        ),
      ),
    );
  }
}

/// Full-width sort control (newest / cheapest).
class _SortBar extends StatelessWidget {
  const _SortBar({required this.sort, required this.onChanged});

  final String sort;
  final ValueChanged<String> onChanged;

  @override
  Widget build(BuildContext context) {
    final AppLocalizations t = AppLocalizations.of(context);
    final AppPalette c = context.colors;

    Widget chip(String value, String label) {
      final bool active = value == sort;
      return Padding(
        padding: const EdgeInsets.only(right: 8),
        child: GestureDetector(
          onTap: () => onChanged(value),
          child: Container(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
            decoration: BoxDecoration(
              color: active ? c.brandSoft : c.surface2,
              borderRadius: BorderRadius.circular(AppRadii.pill),
              border: Border.all(color: active ? c.brand : c.border),
            ),
            child: Text(
              label,
              style: TextStyle(
                color: active ? c.brand : c.textMuted,
                fontWeight: FontWeight.w700,
                fontSize: 12.5,
                fontFamily: AppTheme.fontFamily,
              ),
            ),
          ),
        ),
      );
    }

    return Row(
      children: <Widget>[
        Icon(Icons.sort_rounded, size: 18, color: c.textMuted),
        const SizedBox(width: 8),
        Text('${t.shopSortBy}:',
            style: Theme.of(context).textTheme.bodySmall),
        const SizedBox(width: 10),
        chip('newest', t.shopSortNewest),
        chip('cheapest', t.shopSortPriceLow),
      ],
    );
  }
}

class _ProductCard extends StatelessWidget {
  const _ProductCard({required this.product});

  final Product product;

  @override
  Widget build(BuildContext context) {
    final AppLocalizations t = AppLocalizations.of(context);
    final AppPalette c = context.colors;
    return GlassCard(
      padding: const EdgeInsets.all(10),
      onTap: () => context.push('/shop/${product.slug}'),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: <Widget>[
          Expanded(
            child: Stack(
              children: <Widget>[
                Positioned.fill(
                  child: ProductThumb(
                    imageUrl: product.imageUrl,
                    type: product.type,
                    borderRadius: BorderRadius.circular(14),
                  ),
                ),
                Positioned(
                  top: 8,
                  right: 8,
                  child: StatusPill(
                    productTypeLabel(t, product.type),
                    color: c.plum,
                    icon: productTypeIcon(product.type),
                  ),
                ),
                if (!product.inStock)
                  Positioned(
                    left: 8,
                    bottom: 8,
                    child: StatusPill(t.shopOutOfStock, color: c.accentRed),
                  ),
              ],
            ),
          ),
          const SizedBox(height: 10),
          Text(
            product.title,
            maxLines: 2,
            overflow: TextOverflow.ellipsis,
            style: Theme.of(context).textTheme.titleSmall,
          ),
          const SizedBox(height: 8),
          PriceText(
            amount: product.price,
            style: Theme.of(context)
                .textTheme
                .titleSmall
                ?.copyWith(fontWeight: FontWeight.w800),
          ),
          if (product.pointsPrice != null) ...<Widget>[
            const SizedBox(height: 3),
            PriceText(
              amount: product.pointsPrice!,
              points: true,
              color: c.gold,
              style: Theme.of(context)
                  .textTheme
                  .bodySmall
                  ?.copyWith(fontWeight: FontWeight.w700),
            ),
          ],
        ],
      ),
    );
  }
}

// ---------------------------------------------------------------------------
// Orders tab
// ---------------------------------------------------------------------------

class _OrdersTab extends ConsumerWidget {
  const _OrdersTab();

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final AppLocalizations t = AppLocalizations.of(context);
    final AsyncValue<List<Order>> async = ref.watch(shopOrdersProvider);

    return RefreshIndicator(
      onRefresh: () async => ref.invalidate(shopOrdersProvider),
      child: async.when(
        loading: () => ListView(
          padding: const EdgeInsets.fromLTRB(16, 8, 16, 104),
          children: List<Widget>.generate(
            4,
            (_) => const Padding(
              padding: EdgeInsets.only(bottom: 12),
              child: ShimmerBox(height: 96, radius: 20),
            ),
          ),
        ),
        error: (Object e, _) => ListView(
          children: <Widget>[
            const SizedBox(height: 120),
            ErrorRetry(
              message: e.toString(),
              onRetry: () => ref.invalidate(shopOrdersProvider),
            ),
          ],
        ),
        data: (List<Order> orders) {
          if (orders.isEmpty) {
            return ListView(
              physics: const AlwaysScrollableScrollPhysics(),
              children: <Widget>[
                const SizedBox(height: 120),
                EmptyState(
                  message: t.shopNoOrders,
                  icon: Icons.receipt_long_rounded,
                ),
              ],
            );
          }
          return ListView.separated(
            padding: const EdgeInsets.fromLTRB(16, 8, 16, 104),
            itemCount: orders.length,
            separatorBuilder: (_, __) => const SizedBox(height: 12),
            itemBuilder: (_, int i) => _OrderCard(order: orders[i]),
          );
        },
      ),
    );
  }
}

class _OrderCard extends StatelessWidget {
  const _OrderCard({required this.order});

  final Order order;

  @override
  Widget build(BuildContext context) {
    final AppLocalizations t = AppLocalizations.of(context);
    final AppPalette c = context.colors;
    final meta = orderStatusMeta(context, t, order.status);
    final bool isPoints = order.paidWith == 'points';
    final bool canDownload =
        order.downloadUrl != null && order.downloadUrl!.trim().isNotEmpty;

    return GlassCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: <Widget>[
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: <Widget>[
              Expanded(
                child: Text(
                  order.productTitle,
                  style: Theme.of(context).textTheme.titleSmall,
                ),
              ),
              const SizedBox(width: 10),
              StatusPill(meta.label, color: meta.color, icon: meta.icon),
            ],
          ),
          const SizedBox(height: 12),
          Row(
            children: <Widget>[
              Icon(Icons.calendar_today_rounded, size: 14, color: c.textFaint),
              const SizedBox(width: 6),
              Text(order.createdAtJalali,
                  style: Theme.of(context).textTheme.bodySmall),
              const Spacer(),
              PriceText(
                amount: order.amount,
                points: isPoints,
                color: isPoints ? c.gold : null,
                style: Theme.of(context)
                    .textTheme
                    .titleSmall
                    ?.copyWith(fontWeight: FontWeight.w800),
              ),
            ],
          ),
          if (canDownload) ...<Widget>[
            const SizedBox(height: 14),
            GradientButton(
              label: t.commonDownload,
              icon: Icons.download_rounded,
              height: 44,
              onPressed: () {
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(content: Text(t.shopDownloadStarted)),
                );
              },
            ),
          ],
        ],
      ),
    );
  }
}
