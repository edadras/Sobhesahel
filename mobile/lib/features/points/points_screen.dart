import 'package:flutter/material.dart';

import '../../l10n/app_localizations.dart';
import '../../theme/glass.dart';
import '../../widgets/common.dart';

// TODO(feature-agent): implement per docs/MEMBER_API.md (/points,
// /points/transactions) + template points.html. Bind to PointsRepository via
// pointsRepositoryProvider (lib/data/providers.dart). Show the balance hero,
// membership levels grid, earn/spend rules and the transaction history table.
// Keep this file path and the class name `PointsScreen` stable — the router
// imports it and only this widget's body should change.
class PointsScreen extends StatelessWidget {
  const PointsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final AppLocalizations t = AppLocalizations.of(context);
    return GlassScaffold(
      appBar: GlassAppBar(title: t.pointsTitle),
      body: ComingSoonPlaceholder(
        title: t.pointsTitle,
        icon: Icons.monetization_on_rounded,
      ),
    );
  }
}
