import 'package:flutter/material.dart';

import '../../l10n/app_localizations.dart';
import '../../theme/glass.dart';
import '../../widgets/common.dart';

// TODO(feature-agent): implement per docs/MEMBER_API.md (/shop/products/{slug},
// /shop/orders) + template shop.html. Bind to ShopRepository.product(slug) via
// shopRepositoryProvider (lib/data/providers.dart). Show the product detail,
// price / points price, and buy-with-cash / buy-with-points actions.
// The `slug` route param is passed in by the router (see lib/router.dart).
// Keep this file path and the class name `ShopDetailScreen` (with its `slug`
// constructor argument) stable.
class ShopDetailScreen extends StatelessWidget {
  const ShopDetailScreen({super.key, required this.slug});

  final String slug;

  @override
  Widget build(BuildContext context) {
    final AppLocalizations t = AppLocalizations.of(context);
    return GlassScaffold(
      appBar: GlassAppBar(title: t.shopProductDetails),
      body: ComingSoonPlaceholder(
        title: t.shopProductDetails,
        icon: Icons.inventory_2_rounded,
      ),
    );
  }
}
