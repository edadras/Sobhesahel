import 'package:flutter/material.dart';

import '../../l10n/app_localizations.dart';
import '../../theme/glass.dart';
import '../../widgets/common.dart';

// TODO(feature-agent): implement per docs/MEMBER_API.md (/badges) + template
// badges.html. Bind to BadgesRepository via badgesRepositoryProvider
// (lib/data/providers.dart). Show earned vs locked badges with condition text.
// Keep this file path and the class name `BadgesScreen` stable.
class BadgesScreen extends StatelessWidget {
  const BadgesScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final AppLocalizations t = AppLocalizations.of(context);
    return GlassScaffold(
      appBar: GlassAppBar(title: t.badgesTitle, subtitle: t.badgesSubtitle),
      body: ComingSoonPlaceholder(
        title: t.badgesTitle,
        icon: Icons.emoji_events_rounded,
      ),
    );
  }
}
