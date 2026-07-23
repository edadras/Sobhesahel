import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:shimmer/shimmer.dart';

import '../../../core/persian.dart';
import '../../../l10n/app_localizations.dart';
import '../../../theme/app_colors.dart';
import '../../../theme/app_theme.dart';

/// Icon chosen for a product's type, reused by the thumbnail fallback and tags.
IconData productTypeIcon(String type) {
  switch (type) {
    case 'ebook':
      return Icons.menu_book_rounded;
    case 'pdf':
      return Icons.picture_as_pdf_rounded;
    case 'report':
      return Icons.insights_rounded;
    default:
      return Icons.inventory_2_rounded;
  }
}

/// Localized label for a product type, falling back to the raw value.
String productTypeLabel(AppLocalizations t, String type) {
  switch (type) {
    case 'ebook':
      return t.shopTypeEbook;
    case 'pdf':
      return t.shopTypePdf;
    case 'report':
      return t.shopTypeReport;
    default:
      return type;
  }
}

/// Colour + localized label + icon for an order status badge.
({String label, Color color, IconData icon}) orderStatusMeta(
    BuildContext context, AppLocalizations t, String status) {
  final AppPalette c = context.colors;
  switch (status) {
    case 'delivered':
      return (label: t.shopStatusDelivered, color: c.success, icon: Icons.verified_rounded);
    case 'paid':
    case 'completed':
      return (label: t.shopStatusPaid, color: c.success, icon: Icons.check_circle_rounded);
    case 'processing':
    case 'pending':
      return (label: t.shopStatusProcessing, color: c.gold, icon: Icons.hourglass_bottom_rounded);
    case 'failed':
    case 'cancelled':
      return (label: t.shopStatusFailed, color: c.accentRed, icon: Icons.cancel_rounded);
    default:
      return (label: status, color: c.textMuted, icon: Icons.info_outline_rounded);
  }
}

/// A product image with a graceful gradient+icon fallback when [imageUrl] is
/// null or fails to load.
class ProductThumb extends StatelessWidget {
  const ProductThumb({
    super.key,
    required this.imageUrl,
    required this.type,
    this.borderRadius,
    this.height,
    this.iconSize = 42,
  });

  final String? imageUrl;
  final String type;
  final BorderRadius? borderRadius;
  final double? height;
  final double iconSize;

  @override
  Widget build(BuildContext context) {
    final BorderRadius radius =
        borderRadius ?? BorderRadius.circular(AppRadii.md);
    final Widget fallback = _Fallback(type: type, iconSize: iconSize);
    final String? url = (imageUrl != null && imageUrl!.trim().isNotEmpty)
        ? imageUrl
        : null;

    return ClipRRect(
      borderRadius: radius,
      child: SizedBox(
        height: height,
        width: double.infinity,
        child: url == null
            ? fallback
            : CachedNetworkImage(
                imageUrl: url,
                fit: BoxFit.cover,
                placeholder: (_, __) => const ShimmerBox(),
                errorWidget: (_, __, ___) => fallback,
              ),
      ),
    );
  }
}

class _Fallback extends StatelessWidget {
  const _Fallback({required this.type, required this.iconSize});
  final String type;
  final double iconSize;

  @override
  Widget build(BuildContext context) {
    final AppPalette c = context.colors;
    return DecoratedBox(
      decoration: BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: <Color>[
            c.brand.withValues(alpha: 0.22),
            c.plum.withValues(alpha: 0.18),
          ],
        ),
      ),
      child: Center(
        child: Icon(productTypeIcon(type),
            size: iconSize, color: c.brand.withValues(alpha: 0.85)),
      ),
    );
  }
}

/// A shimmering placeholder block used for loading states.
class ShimmerBox extends StatelessWidget {
  const ShimmerBox({super.key, this.height, this.width, this.radius = 12});

  final double? height;
  final double? width;
  final double radius;

  @override
  Widget build(BuildContext context) {
    final AppPalette c = context.colors;
    return Shimmer.fromColors(
      baseColor: c.surface2,
      highlightColor: c.isDark
          ? Colors.white.withValues(alpha: 0.06)
          : Colors.white.withValues(alpha: 0.85),
      child: Container(
        height: height,
        width: width,
        decoration: BoxDecoration(
          color: c.surface2,
          borderRadius: BorderRadius.circular(radius),
        ),
      ),
    );
  }
}

/// Renders a money amount ("۸۵٬۰۰۰ تومان") or a points amount with the
/// active locale's digits.
class PriceText extends StatelessWidget {
  const PriceText({
    super.key,
    required this.amount,
    this.points = false,
    this.style,
    this.color,
  });

  final int amount;
  final bool points;
  final TextStyle? style;
  final Color? color;

  @override
  Widget build(BuildContext context) {
    final AppLocalizations t = AppLocalizations.of(context);
    final AppPalette c = context.colors;
    final String unit = points ? t.commonPoints : t.commonToman;
    final TextStyle base = (style ??
            Theme.of(context)
                .textTheme
                .titleMedium!
                .copyWith(fontWeight: FontWeight.w800))
        .copyWith(color: color, fontFamily: AppTheme.fontFamily);
    return RichText(
      text: TextSpan(
        style: base,
        children: <InlineSpan>[
          TextSpan(text: context.faNum(amount)),
          TextSpan(
            text: ' $unit',
            style: base.copyWith(
              fontSize: (base.fontSize ?? 16) * 0.62,
              fontWeight: FontWeight.w600,
              color: color ?? c.textMuted,
            ),
          ),
        ],
      ),
    );
  }
}
