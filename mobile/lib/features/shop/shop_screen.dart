import 'package:flutter/material.dart';

import '../../l10n/app_localizations.dart';
import '../../theme/glass.dart';
import '../../widgets/common.dart';

// TODO(feature-agent): implement per docs/MEMBER_API.md (/shop/products,
// /shop/orders) + template shop.html. Bind to ShopRepository via
// shopRepositoryProvider (lib/data/providers.dart). Show the product grid with
// sort, and a "my orders" section. Tapping a product pushes /shop/:slug.
// This is a primary bottom-nav tab.
// Keep this file path and the class name `ShopScreen` stable.
class ShopScreen extends StatelessWidget {
  const ShopScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final AppLocalizations t = AppLocalizations.of(context);
    return GlassScaffold(
      appBar: GlassAppBar(
        title: t.shopTitle,
        subtitle: t.shopSubtitle,
        automaticallyImplyLeading: false,
      ),
      body: ComingSoonPlaceholder(
        title: t.shopTitle,
        icon: Icons.shopping_bag_rounded,
      ),
    );
  }
}
