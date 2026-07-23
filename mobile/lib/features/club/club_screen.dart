import 'package:flutter/material.dart';

import '../../l10n/app_localizations.dart';
import '../../theme/glass.dart';
import '../../widgets/common.dart';

// TODO(feature-agent): implement per docs/MEMBER_API.md (/club, /club/wheel/spin)
// + template club.html. Bind to ClubRepository via clubRepositoryProvider
// (lib/data/providers.dart). Show the streak card, daily missions with progress
// and the spin wheel. This is a primary bottom-nav tab.
// Keep this file path and the class name `ClubScreen` stable.
class ClubScreen extends StatelessWidget {
  const ClubScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final AppLocalizations t = AppLocalizations.of(context);
    return GlassScaffold(
      appBar: GlassAppBar(
        title: t.clubTitle,
        subtitle: t.clubSubtitle,
        automaticallyImplyLeading: false,
      ),
      body: ComingSoonPlaceholder(
        title: t.clubTitle,
        icon: Icons.card_giftcard_rounded,
      ),
    );
  }
}
