import 'package:flutter/material.dart';

import '../../l10n/app_localizations.dart';
import '../../theme/glass.dart';
import '../../widgets/common.dart';

// TODO(feature-agent): implement per docs/MEMBER_API.md (/archive,
// /archive/{id}, /archive/{id}/unlock) + template archive.html. Bind to
// ArchiveRepository via archiveRepositoryProvider (lib/data/providers.dart).
// Show issues by year with covers, accessible/locked state and unlock action.
// Keep this file path and the class name `ArchiveScreen` stable.
class ArchiveScreen extends StatelessWidget {
  const ArchiveScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final AppLocalizations t = AppLocalizations.of(context);
    return GlassScaffold(
      appBar: GlassAppBar(title: t.archiveTitle, subtitle: t.archiveSubtitle),
      body: ComingSoonPlaceholder(
        title: t.archiveTitle,
        icon: Icons.menu_book_rounded,
      ),
    );
  }
}
