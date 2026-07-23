import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:shimmer/shimmer.dart';

import '../../core/persian.dart';
import '../../data/models/models.dart';
import '../../data/providers.dart';
import '../../l10n/app_localizations.dart';
import '../../theme/app_colors.dart';
import '../../theme/app_theme.dart';
import '../../theme/glass.dart';
import '../../widgets/common.dart';

/// The notifications inbox — all / unread filter, per-item and bulk mark-read,
/// and a (disabled, coming-soon) Web Push toggle. Bound to
/// [NotificationsRepository] via [notificationsRepositoryProvider]. Mirrors
/// `notifications.html`.
final _notifProvider = FutureProvider.autoDispose
    .family<Paged<AppNotification>, String>((ref, String filter) {
  return ref.watch(notificationsRepositoryProvider).list(filter: filter);
});

class NotificationsScreen extends ConsumerStatefulWidget {
  const NotificationsScreen({super.key});

  @override
  ConsumerState<NotificationsScreen> createState() =>
      _NotificationsScreenState();
}

class _NotificationsScreenState extends ConsumerState<NotificationsScreen> {
  String _filter = 'all';
  bool _markingAll = false;

  @override
  Widget build(BuildContext context) {
    final AppLocalizations t = AppLocalizations.of(context);
    final AsyncValue<Paged<AppNotification>> async =
        ref.watch(_notifProvider(_filter));

    return GlassScaffold(
      appBar: GlassAppBar(
        title: t.notifTitle,
        actions: <Widget>[
          IconButton(
            tooltip: t.notifMarkAllRead,
            onPressed: _markingAll ? null : _markAllRead,
            icon: _markingAll
                ? const SizedBox(
                    width: 18,
                    height: 18,
                    child: CircularProgressIndicator(strokeWidth: 2.2),
                  )
                : const Icon(Icons.done_all_rounded),
          ),
          const SizedBox(width: 4),
        ],
      ),
      body: Column(
        children: <Widget>[
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 8, 16, 4),
            child: _FilterTabs(
              selected: _filter,
              onChanged: (String f) => setState(() => _filter = f),
              allLabel: t.notifFilterAll,
              unreadLabel: t.notifFilterUnread,
            ),
          ),
          Expanded(
            child: async.when(
              loading: () => const _NotifShimmer(),
              error: (Object e, _) => ErrorRetry(
                message: e.toString(),
                onRetry: () => ref.invalidate(_notifProvider(_filter)),
              ),
              data: (Paged<AppNotification> page) =>
                  _list(context, t, page.items),
            ),
          ),
        ],
      ),
    );
  }

  Widget _list(
      BuildContext context, AppLocalizations t, List<AppNotification> items) {
    return RefreshIndicator(
      onRefresh: () async => ref.invalidate(_notifProvider(_filter)),
      child: ListView(
        padding: const EdgeInsets.fromLTRB(16, 8, 16, 104),
        children: <Widget>[
          _webPushCard(context, t),
          const SizedBox(height: 16),
          if (items.isEmpty)
            Padding(
              padding: const EdgeInsets.only(top: 60),
              child: EmptyState(
                message: t.notifEmpty,
                icon: Icons.notifications_none_rounded,
              ),
            )
          else
            for (final AppNotification n in items) _row(context, t, n),
        ],
      ),
    );
  }

  Widget _row(BuildContext context, AppLocalizations t, AppNotification n) {
    final AppPalette c = context.colors;
    final _NotifStyle st = _styleFor(n.icon, c);
    return Padding(
      padding: const EdgeInsets.only(bottom: 10),
      child: GlassContainer(
        padding: const EdgeInsets.all(14),
        borderRadius: BorderRadius.circular(AppRadii.lg),
        strong: !n.read,
        borderColor: n.read ? null : c.brand.withValues(alpha: 0.35),
        onTap: n.read ? null : () => _markRead(n.id),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: <Widget>[
            IconChip(icon: st.icon, color: st.color, size: 42, radius: 12),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: <Widget>[
                  Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: <Widget>[
                      Expanded(
                        child: Text(
                          n.title,
                          style: Theme.of(context)
                              .textTheme
                              .titleSmall
                              ?.copyWith(
                                fontWeight:
                                    n.read ? FontWeight.w600 : FontWeight.w800,
                              ),
                        ),
                      ),
                      if (!n.read) ...<Widget>[
                        const SizedBox(width: 8),
                        Container(
                          margin: const EdgeInsets.only(top: 5),
                          width: 8,
                          height: 8,
                          decoration: BoxDecoration(
                              color: c.brand, shape: BoxShape.circle),
                        ),
                      ],
                    ],
                  ),
                  if (n.body.isNotEmpty) ...<Widget>[
                    const SizedBox(height: 4),
                    Text(
                      n.body,
                      style: Theme.of(context)
                          .textTheme
                          .bodySmall
                          ?.copyWith(color: c.textMuted, height: 1.5),
                    ),
                  ],
                  const SizedBox(height: 6),
                  Row(
                    children: <Widget>[
                      Icon(Icons.schedule_rounded, size: 12, color: c.textFaint),
                      const SizedBox(width: 4),
                      Text(
                        context.faDigits(n.createdAtJalali),
                        style: Theme.of(context)
                            .textTheme
                            .bodySmall
                            ?.copyWith(color: c.textFaint, fontSize: 11.5),
                      ),
                      const Spacer(),
                      if (!n.read)
                        TextButton(
                          style: TextButton.styleFrom(
                            padding: const EdgeInsets.symmetric(horizontal: 8),
                            minimumSize: const Size(0, 32),
                            tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                          ),
                          onPressed: () => _markRead(n.id),
                          child: Text(t.notifMarkRead),
                        ),
                    ],
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _webPushCard(BuildContext context, AppLocalizations t) {
    final AppPalette c = context.colors;
    return GlassCard(
      gradient: LinearGradient(
        begin: Alignment.topRight,
        end: Alignment.bottomLeft,
        colors: <Color>[
          c.brandSoft.withValues(alpha: c.isDark ? 0.5 : 1),
          c.surface.withValues(alpha: c.isDark ? 0.2 : 0.5),
        ],
      ),
      child: Row(
        children: <Widget>[
          IconChip(
              icon: Icons.notifications_active_rounded,
              color: c.brand,
              size: 44,
              radius: 13),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: <Widget>[
                Row(
                  children: <Widget>[
                    Flexible(
                      child: Text(
                        t.notifWebPush,
                        style: Theme.of(context).textTheme.titleSmall,
                      ),
                    ),
                    const SizedBox(width: 8),
                    StatusPill(t.commonComingSoon, color: c.textMuted),
                  ],
                ),
                const SizedBox(height: 4),
                Text(
                  t.notifWebPushDesc,
                  style: Theme.of(context)
                      .textTheme
                      .bodySmall
                      ?.copyWith(color: c.textMuted),
                ),
              ],
            ),
          ),
          const SizedBox(width: 8),
          // Rendered disabled — Web Push is not available yet.
          const IgnorePointer(
            child: Opacity(
              opacity: 0.5,
              child: Switch(value: false, onChanged: null),
            ),
          ),
        ],
      ),
    );
  }

  Future<void> _markRead(int id) async {
    final AppLocalizations t = AppLocalizations.of(context);
    try {
      await ref.read(notificationsRepositoryProvider).markRead(id);
      ref.invalidate(_notifProvider);
    } catch (_) {
      if (mounted) _toast(context, t.commonError);
    }
  }

  Future<void> _markAllRead() async {
    final AppLocalizations t = AppLocalizations.of(context);
    setState(() => _markingAll = true);
    try {
      await ref.read(notificationsRepositoryProvider).markAllRead();
      ref.invalidate(_notifProvider);
      if (mounted) _toast(context, t.notifAllMarkedRead);
    } catch (_) {
      if (mounted) _toast(context, t.commonError);
    } finally {
      if (mounted) setState(() => _markingAll = false);
    }
  }
}

void _toast(BuildContext context, String message) {
  ScaffoldMessenger.of(context)
    ..hideCurrentSnackBar()
    ..showSnackBar(SnackBar(content: Text(message)));
}

class _NotifStyle {
  const _NotifStyle(this.icon, this.color);
  final IconData icon;
  final Color color;
}

_NotifStyle _styleFor(String key, AppPalette c) {
  switch (key) {
    case 'coins':
    case 'points':
      return _NotifStyle(Icons.monetization_on_rounded, c.gold);
    case 'gift':
      return _NotifStyle(Icons.card_giftcard_rounded, c.gold);
    case 'award':
    case 'badge':
      return _NotifStyle(Icons.military_tech_rounded, c.plum);
    case 'crown':
    case 'subscription':
      return _NotifStyle(Icons.workspace_premium_rounded, c.brand);
    case 'news':
    case 'book':
      return _NotifStyle(Icons.article_rounded, c.brand);
    case 'pin':
    case 'tourism':
      return _NotifStyle(Icons.place_rounded, c.success);
    default:
      return _NotifStyle(Icons.notifications_rounded, c.brand);
  }
}

class _FilterTabs extends StatelessWidget {
  const _FilterTabs({
    required this.selected,
    required this.onChanged,
    required this.allLabel,
    required this.unreadLabel,
  });

  final String selected;
  final ValueChanged<String> onChanged;
  final String allLabel;
  final String unreadLabel;

  @override
  Widget build(BuildContext context) {
    final AppPalette c = context.colors;
    final List<MapEntry<String, String>> tabs = <MapEntry<String, String>>[
      MapEntry<String, String>('all', allLabel),
      MapEntry<String, String>('unread', unreadLabel),
    ];
    return Container(
      padding: const EdgeInsets.all(5),
      decoration: BoxDecoration(
        color: c.surface2.withValues(alpha: 0.7),
        borderRadius: BorderRadius.circular(AppRadii.pill),
        border: Border.all(color: c.glassBorderFaint),
      ),
      child: Row(
        children: <Widget>[
          for (final MapEntry<String, String> e in tabs)
            Expanded(
              child: GestureDetector(
                onTap: () => onChanged(e.key),
                child: AnimatedContainer(
                  duration: const Duration(milliseconds: 200),
                  curve: Curves.easeOut,
                  padding: const EdgeInsets.symmetric(vertical: 9),
                  decoration: BoxDecoration(
                    color: e.key == selected ? c.surface : Colors.transparent,
                    borderRadius: BorderRadius.circular(AppRadii.pill),
                    boxShadow: e.key == selected
                        ? <BoxShadow>[
                            BoxShadow(
                              color: c.plum.withValues(alpha: 0.12),
                              blurRadius: 10,
                              offset: const Offset(0, 4),
                            ),
                          ]
                        : null,
                  ),
                  child: Text(
                    e.value,
                    textAlign: TextAlign.center,
                    style: TextStyle(
                      fontFamily: AppTheme.fontFamily,
                      fontWeight: FontWeight.w700,
                      fontSize: 13,
                      color: e.key == selected ? c.brand : c.textMuted,
                    ),
                  ),
                ),
              ),
            ),
        ],
      ),
    );
  }
}

class _NotifShimmer extends StatelessWidget {
  const _NotifShimmer();

  @override
  Widget build(BuildContext context) {
    final AppPalette c = context.colors;
    return ListView.builder(
      padding: const EdgeInsets.fromLTRB(16, 8, 16, 104),
      itemCount: 7,
      itemBuilder: (BuildContext ctx, int i) => Padding(
        padding: const EdgeInsets.only(bottom: 10),
        child: Shimmer.fromColors(
          baseColor: c.surface2,
          highlightColor: c.surface,
          child: Container(
            height: 84,
            decoration: BoxDecoration(
              color: c.surface2,
              borderRadius: BorderRadius.circular(AppRadii.lg),
            ),
          ),
        ),
      ),
    );
  }
}
