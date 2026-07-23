import 'package:flutter/material.dart';

import '../../l10n/app_localizations.dart';
import '../../theme/glass.dart';
import '../../widgets/common.dart';

// TODO(feature-agent): implement per docs/MEMBER_API.md (/library/bookmarks,
// /library/authors, /library/reading) + template library.html. Bind to
// LibraryRepository via libraryRepositoryProvider (lib/data/providers.dart).
// Show tabs for bookmarks, followed authors and continue-reading.
// This is a primary bottom-nav tab.
// Keep this file path and the class name `LibraryScreen` stable.
class LibraryScreen extends StatelessWidget {
  const LibraryScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final AppLocalizations t = AppLocalizations.of(context);
    return GlassScaffold(
      appBar: GlassAppBar(
        title: t.libraryTitle,
        subtitle: t.librarySubtitle,
        automaticallyImplyLeading: false,
      ),
      body: ComingSoonPlaceholder(
        title: t.libraryTitle,
        icon: Icons.local_library_rounded,
      ),
    );
  }
}
