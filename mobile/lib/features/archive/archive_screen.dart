import 'package:cached_network_image/cached_network_image.dart';
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

/// Newspaper archive: latest-issue hero, year filter and a paginated grid of
/// issues with lock badges + unlock-with-points flow. Bound to
/// [ArchiveRepository] via [archiveRepositoryProvider]. Mirrors `archive.html`.
final _issuesProvider = FutureProvider.autoDispose<List<ArchiveIssue>>((ref) {
  return ref.watch(archiveRepositoryProvider).issues();
});

const int _pageSize = 8;

class ArchiveScreen extends ConsumerStatefulWidget {
  const ArchiveScreen({super.key});

  @override
  ConsumerState<ArchiveScreen> createState() => _ArchiveScreenState();
}

class _ArchiveScreenState extends ConsumerState<ArchiveScreen> {
  String? _year;
  int _visible = _pageSize;
  bool _busy = false;

  @override
  Widget build(BuildContext context) {
    final AppLocalizations t = AppLocalizations.of(context);
    final AsyncValue<List<ArchiveIssue>> async = ref.watch(_issuesProvider);

    return GlassScaffold(
      appBar: GlassAppBar(title: t.archiveTitle, subtitle: t.archiveSubtitle),
      body: async.when(
        loading: () => const _ArchiveShimmer(),
        error: (Object e, _) => ErrorRetry(
          message: e.toString(),
          onRetry: () => ref.invalidate(_issuesProvider),
        ),
        data: (List<ArchiveIssue> issues) => _content(context, t, issues),
      ),
    );
  }

  Widget _content(
      BuildContext context, AppLocalizations t, List<ArchiveIssue> issues) {
    if (issues.isEmpty) {
      return EmptyState(message: t.commonEmpty, icon: Icons.menu_book_outlined);
    }

    final ArchiveIssue latest = issues.first;
    final List<String> years = _years(issues);
    final String activeYear = _year ?? (years.isNotEmpty ? years.first : '');
    final List<ArchiveIssue> filtered = years.isEmpty
        ? issues
        : issues.where((ArchiveIssue i) => _yearOf(i.dateJalali) == activeYear)
            .toList();
    final int shown = _visible.clamp(0, filtered.length);
    final List<ArchiveIssue> visible = filtered.take(shown).toList();

    return RefreshIndicator(
      onRefresh: () async {
        ref.invalidate(_issuesProvider);
      },
      child: ListView(
        padding: const EdgeInsets.fromLTRB(16, 12, 16, 104),
        children: <Widget>[
          _hero(context, t, latest),
          const SizedBox(height: 22),
          if (years.isNotEmpty) ...<Widget>[
            SectionTitle(t.archiveYear),
            _yearFilter(context, years, activeYear),
            const SizedBox(height: 16),
          ],
          _grid(context, t, visible),
          if (shown < filtered.length) ...<Widget>[
            const SizedBox(height: 18),
            Center(
              child: OutlinedButton.icon(
                onPressed: () =>
                    setState(() => _visible = shown + _pageSize),
                icon: const Icon(Icons.expand_more_rounded, size: 18),
                label: Text(t.commonSeeMore),
              ),
            ),
          ],
        ],
      ),
    );
  }

  // --- Hero ----------------------------------------------------------------

  Widget _hero(BuildContext context, AppLocalizations t, ArchiveIssue issue) {
    return GlassCard(
      strong: true,
      child: LayoutBuilder(
        builder: (BuildContext ctx, BoxConstraints cons) {
          final bool wide = cons.maxWidth > 420;
          final Widget cover = _Cover(
            url: issue.coverUrl,
            accessible: issue.accessible,
            width: wide ? 150 : 120,
          );
          final Widget info = _heroInfo(context, t, issue);
          if (wide) {
            return Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: <Widget>[
                cover,
                const SizedBox(width: 18),
                Expanded(child: info),
              ],
            );
          }
          return Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: <Widget>[
              Center(child: cover),
              const SizedBox(height: 16),
              info,
            ],
          );
        },
      ),
    );
  }

  Widget _heroInfo(BuildContext context, AppLocalizations t, ArchiveIssue issue) {
    final AppPalette c = context.colors;
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: <Widget>[
        StatusPill(t.archiveLatestIssue,
            color: c.accentRed, icon: Icons.fiber_new_rounded),
        const SizedBox(height: 10),
        Text(
          issue.title,
          style: Theme.of(context)
              .textTheme
              .titleLarge
              ?.copyWith(fontWeight: FontWeight.w800),
        ),
        const SizedBox(height: 6),
        Row(
          children: <Widget>[
            Icon(Icons.event_rounded, size: 15, color: c.textMuted),
            const SizedBox(width: 5),
            Expanded(
              child: Text(
                context.faDigits(issue.dateJalali),
                style: Theme.of(context).textTheme.bodyMedium,
              ),
            ),
          ],
        ),
        const SizedBox(height: 16),
        if (issue.accessible) ...<Widget>[
          GradientButton(
            label: t.archiveReadOnline,
            icon: Icons.auto_stories_rounded,
            onPressed: () => _openIssue(issue),
          ),
          const SizedBox(height: 10),
          OutlinedButton.icon(
            onPressed: () => _openIssue(issue),
            icon: const Icon(Icons.download_rounded, size: 18),
            label: Text(t.archiveDownloadPdf),
          ),
        ] else
          GradientButton(
            label: t.archiveUnlockCost(context.faNum(issue.unlockCost)),
            icon: Icons.lock_open_rounded,
            loading: _busy,
            onPressed: () => _unlock(issue),
          ),
      ],
    );
  }

  // --- Year filter ---------------------------------------------------------

  Widget _yearFilter(
      BuildContext context, List<String> years, String activeYear) {
    return SizedBox(
      height: 40,
      child: ListView.separated(
        scrollDirection: Axis.horizontal,
        itemCount: years.length,
        separatorBuilder: (_, __) => const SizedBox(width: 8),
        itemBuilder: (BuildContext ctx, int i) {
          final String y = years[i];
          final bool active = y == activeYear;
          final AppPalette c = context.colors;
          return GestureDetector(
            onTap: () => setState(() {
              _year = y;
              _visible = _pageSize;
            }),
            child: AnimatedContainer(
              duration: const Duration(milliseconds: 180),
              alignment: Alignment.center,
              padding: const EdgeInsets.symmetric(horizontal: 18),
              decoration: BoxDecoration(
                gradient: active ? c.brandGradient : null,
                color: active ? null : c.surface2.withValues(alpha: 0.7),
                borderRadius: BorderRadius.circular(AppRadii.pill),
                border: Border.all(
                    color: active ? Colors.transparent : c.glassBorderFaint),
              ),
              child: Text(
                context.faDigits(y),
                style: TextStyle(
                  fontFamily: AppTheme.fontFamily,
                  fontWeight: FontWeight.w700,
                  fontSize: 13.5,
                  color: active ? Colors.white : c.textMuted,
                ),
              ),
            ),
          );
        },
      ),
    );
  }

  // --- Grid ----------------------------------------------------------------

  Widget _grid(
      BuildContext context, AppLocalizations t, List<ArchiveIssue> issues) {
    final double w = MediaQuery.of(context).size.width;
    final int cols = w > 620 ? 3 : 2;
    return GridView.builder(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      itemCount: issues.length,
      gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: cols,
        mainAxisSpacing: 14,
        crossAxisSpacing: 14,
        childAspectRatio: 0.62,
      ),
      itemBuilder: (BuildContext ctx, int i) =>
          _issueCard(context, t, issues[i]),
    );
  }

  Widget _issueCard(BuildContext context, AppLocalizations t, ArchiveIssue i) {
    return GlassCard(
      padding: EdgeInsets.zero,
      onTap: () => i.accessible ? _openIssue(i) : _unlock(i),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: <Widget>[
          Expanded(
            child: ClipRRect(
              borderRadius: const BorderRadius.vertical(top: Radius.circular(18)),
              child: _Cover(
                url: i.coverUrl,
                accessible: i.accessible,
                width: double.infinity,
                fill: true,
              ),
            ),
          ),
          Padding(
            padding: const EdgeInsets.fromLTRB(10, 8, 10, 10),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: <Widget>[
                Text(
                  i.title,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: Theme.of(context)
                      .textTheme
                      .titleSmall
                      ?.copyWith(fontSize: 12.5),
                ),
                const SizedBox(height: 3),
                Text(
                  context.faDigits(i.dateJalali),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: Theme.of(context)
                      .textTheme
                      .bodySmall
                      ?.copyWith(color: context.colors.textFaint),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  // --- Actions -------------------------------------------------------------

  Future<void> _openIssue(ArchiveIssue issue) async {
    final AppLocalizations t = AppLocalizations.of(context);
    // A pragmatic in-app PDF viewer is out of scope without a plugin, and
    // url_launcher isn't a dependency — so we surface a glass dialog confirming
    // that access has been granted.
    await _glassDialog(
      context,
      icon: Icons.check_circle_rounded,
      title: t.archiveAccessGranted,
      message: t.archiveViewerNote,
      confirmLabel: t.commonClose,
    );
  }

  Future<void> _unlock(ArchiveIssue issue) async {
    if (_busy) return;
    final AppLocalizations t = AppLocalizations.of(context);
    final bool? ok = await _glassDialog(
      context,
      icon: Icons.lock_open_rounded,
      title: t.archiveUnlock,
      message: t.archiveUnlockConfirm(context.faNum(issue.unlockCost)),
      confirmLabel: t.commonConfirm,
      cancelLabel: t.commonCancel,
    );
    if (ok != true) return;
    setState(() => _busy = true);
    try {
      await ref.read(archiveRepositoryProvider).unlock(issue.id);
      ref.invalidate(_issuesProvider);
      if (mounted) _toast(context, t.archiveUnlocked);
    } catch (_) {
      if (mounted) _toast(context, t.commonError);
    } finally {
      if (mounted) setState(() => _busy = false);
    }
  }

  // --- Year helpers --------------------------------------------------------

  List<String> _years(List<ArchiveIssue> issues) {
    final List<String> out = <String>[];
    for (final ArchiveIssue i in issues) {
      final String y = _yearOf(i.dateJalali);
      if (y.isNotEmpty && !out.contains(y)) out.add(y);
    }
    out.sort((String a, String b) =>
        _digitsOnly(b).compareTo(_digitsOnly(a)));
    return out;
  }

  static String _yearOf(String dateJalali) {
    for (final String token in dateJalali.split(RegExp(r'\s+'))) {
      if (_digitsOnly(token).length == 4) return token;
    }
    return '';
  }

  static String _digitsOnly(String s) {
    final StringBuffer b = StringBuffer();
    for (final int code in s.runes) {
      if (code >= 0x30 && code <= 0x39) {
        b.writeCharCode(code);
      } else if (code >= 0x06F0 && code <= 0x06F9) {
        b.writeCharCode(0x30 + (code - 0x06F0));
      }
    }
    return b.toString();
  }
}

Future<bool?> _glassDialog(
  BuildContext context, {
  required IconData icon,
  required String title,
  required String message,
  required String confirmLabel,
  String? cancelLabel,
}) {
  final AppPalette c = context.colors;
  return showDialog<bool>(
    context: context,
    builder: (BuildContext ctx) => Dialog(
      backgroundColor: Colors.transparent,
      elevation: 0,
      insetPadding: const EdgeInsets.symmetric(horizontal: 32),
      child: GlassContainer(
        strong: true,
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: <Widget>[
            Align(
              alignment: Alignment.center,
              child: Container(
                width: 60,
                height: 60,
                decoration: BoxDecoration(
                  gradient: c.brandGradient,
                  shape: BoxShape.circle,
                ),
                child: Icon(icon, color: Colors.white, size: 28),
              ),
            ),
            const SizedBox(height: 16),
            Text(
              title,
              textAlign: TextAlign.center,
              style: Theme.of(ctx).textTheme.titleMedium,
            ),
            const SizedBox(height: 10),
            Text(
              message,
              textAlign: TextAlign.center,
              style: Theme.of(ctx)
                  .textTheme
                  .bodyMedium
                  ?.copyWith(color: c.textMuted),
            ),
            const SizedBox(height: 20),
            Row(
              children: <Widget>[
                if (cancelLabel != null) ...<Widget>[
                  Expanded(
                    child: OutlinedButton(
                      onPressed: () => Navigator.of(ctx).pop(false),
                      child: Text(cancelLabel),
                    ),
                  ),
                  const SizedBox(width: 12),
                ],
                Expanded(
                  child: GradientButton(
                    label: confirmLabel,
                    height: 46,
                    onPressed: () => Navigator.of(ctx).pop(true),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    ),
  );
}

void _toast(BuildContext context, String message) {
  ScaffoldMessenger.of(context)
    ..hideCurrentSnackBar()
    ..showSnackBar(SnackBar(content: Text(message)));
}

/// A newspaper cover with a masthead-style fallback and a crown lock overlay
/// for inaccessible issues.
class _Cover extends StatelessWidget {
  const _Cover({
    required this.url,
    required this.accessible,
    required this.width,
    this.fill = false,
  });

  final String? url;
  final bool accessible;
  final double width;
  final bool fill;

  @override
  Widget build(BuildContext context) {
    final AppPalette c = context.colors;
    final Widget image = (url == null || url!.isEmpty)
        ? _mastheadFallback(context)
        : CachedNetworkImage(
            imageUrl: url!,
            fit: BoxFit.cover,
            placeholder: (BuildContext ctx, String u) => Shimmer.fromColors(
              baseColor: c.surface2,
              highlightColor: c.surface,
              child: Container(color: c.surface2),
            ),
            errorWidget: (BuildContext ctx, String u, Object e) =>
                _mastheadFallback(context),
          );

    final Widget stack = Stack(
      fit: StackFit.expand,
      children: <Widget>[
        image,
        if (!accessible)
          DecoratedBox(
            decoration: BoxDecoration(
              color: Colors.black.withValues(alpha: 0.5),
            ),
            child: const Center(
              child: Icon(Icons.workspace_premium_rounded,
                  color: Colors.white, size: 30),
            ),
          ),
      ],
    );

    final Widget sized = fill
        ? SizedBox.expand(child: stack)
        : SizedBox(
            width: width,
            height: width * 1.38,
            child: stack,
          );

    if (fill) return sized;
    return ClipRRect(
      borderRadius: BorderRadius.circular(AppRadii.md),
      child: sized,
    );
  }

  Widget _mastheadFallback(BuildContext context) {
    final AppPalette c = context.colors;
    return Container(
      color: c.surface2,
      alignment: Alignment.topCenter,
      child: Container(
        width: double.infinity,
        padding: const EdgeInsets.symmetric(vertical: 8),
        decoration: BoxDecoration(
          color: c.surface,
          border: Border(bottom: BorderSide(color: c.brand, width: 2)),
        ),
        child: Text(
          AppLocalizations.of(context).appName,
          textAlign: TextAlign.center,
          style: TextStyle(
            color: c.brand,
            fontWeight: FontWeight.w900,
            fontSize: 12,
            fontFamily: AppTheme.fontFamily,
          ),
        ),
      ),
    );
  }
}

class _ArchiveShimmer extends StatelessWidget {
  const _ArchiveShimmer();

  @override
  Widget build(BuildContext context) {
    final AppPalette c = context.colors;
    return Shimmer.fromColors(
      baseColor: c.surface2,
      highlightColor: c.surface,
      child: ListView(
        padding: const EdgeInsets.fromLTRB(16, 12, 16, 104),
        children: <Widget>[
          Container(
            height: 220,
            decoration: BoxDecoration(
              color: c.surface2,
              borderRadius: BorderRadius.circular(AppRadii.lg),
            ),
          ),
          const SizedBox(height: 22),
          GridView.builder(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            itemCount: 6,
            gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
              crossAxisCount: 2,
              mainAxisSpacing: 14,
              crossAxisSpacing: 14,
              childAspectRatio: 0.62,
            ),
            itemBuilder: (BuildContext ctx, int i) => Container(
              decoration: BoxDecoration(
                color: c.surface2,
                borderRadius: BorderRadius.circular(AppRadii.lg),
              ),
            ),
          ),
        ],
      ),
    );
  }
}
