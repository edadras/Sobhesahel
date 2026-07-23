import 'package:flutter/material.dart';

import '../../l10n/app_localizations.dart';
import '../../theme/glass.dart';
import '../../widgets/common.dart';

// TODO(feature-agent): implement per docs/MEMBER_API.md (/tourism) + template
// tourism.html. Bind to TourismRepository via tourismRepositoryProvider
// (lib/data/providers.dart). Show the Hormozgan tourism feed with images,
// excerpts and bookmark toggles.
// Keep this file path and the class name `TourismScreen` stable.
class TourismScreen extends StatelessWidget {
  const TourismScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final AppLocalizations t = AppLocalizations.of(context);
    return GlassScaffold(
      appBar: GlassAppBar(title: t.tourismTitle, subtitle: t.tourismSubtitle),
      body: ComingSoonPlaceholder(
        title: t.tourismTitle,
        icon: Icons.place_rounded,
      ),
    );
  }
}
