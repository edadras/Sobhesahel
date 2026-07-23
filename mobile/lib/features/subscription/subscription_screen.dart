import 'package:flutter/material.dart';

import '../../l10n/app_localizations.dart';
import '../../theme/glass.dart';
import '../../widgets/common.dart';

// TODO(feature-agent): implement per docs/MEMBER_API.md (/subscription,
// /subscription/purchase, /subscription/payment/confirm) + template
// subscription.html. Bind to SubscriptionRepository via
// subscriptionRepositoryProvider (lib/data/providers.dart). Show the current
// plan, plan cards with features, and cash/points purchase flows.
// Keep this file path and the class name `SubscriptionScreen` stable.
class SubscriptionScreen extends StatelessWidget {
  const SubscriptionScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final AppLocalizations t = AppLocalizations.of(context);
    return GlassScaffold(
      appBar: GlassAppBar(title: t.subTitle),
      body: ComingSoonPlaceholder(
        title: t.subTitle,
        icon: Icons.workspace_premium_rounded,
      ),
    );
  }
}
