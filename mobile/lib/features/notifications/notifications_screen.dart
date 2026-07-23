import 'package:flutter/material.dart';

import '../../l10n/app_localizations.dart';
import '../../theme/glass.dart';
import '../../widgets/common.dart';

// TODO(feature-agent): implement per docs/MEMBER_API.md (/notifications,
// /notifications/{id}/read, /notifications/read-all, /notifications/preferences)
// + template notifications.html. Bind to NotificationsRepository via
// notificationsRepositoryProvider (lib/data/providers.dart). Show all/unread
// filters, mark-read actions and the preferences section.
// Keep this file path and the class name `NotificationsScreen` stable.
class NotificationsScreen extends StatelessWidget {
  const NotificationsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final AppLocalizations t = AppLocalizations.of(context);
    return GlassScaffold(
      appBar: GlassAppBar(title: t.notifTitle),
      body: ComingSoonPlaceholder(
        title: t.notifTitle,
        icon: Icons.notifications_rounded,
      ),
    );
  }
}
