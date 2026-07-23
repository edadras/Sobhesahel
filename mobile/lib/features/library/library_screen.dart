import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
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

/// Saved bookmarks (news), followed authors and continue-reading progress —
/// bound to [LibraryRepository] via [libraryRepositoryProvider]. Mirrors
/// `library.html` but intentionally omits the books tab (product decision).
final _bookmarksProvider = FutureProvider.autoDispose<Paged<Bookmark>>((ref) {
  return ref.watch(libraryRepositoryProvider).bookmarks();
});

final _authorsProvider = FutureProvider.autoDispose<List<Author>>((ref) {
  return ref.watch(libraryRepositoryProvider).authors();
});

final _readingProvider = FutureProvider.autoDispose<List<ReadingItem>>((ref) {
  return ref.watch(libraryRepositoryProvider).reading();
});

class LibraryScreen extends ConsumerStatefulWidget {
  const LibraryScreen({super.key});

  @override
  ConsumerState<LibraryScreen> createState() => _LibraryScreenState();
}

class _LibraryScreenState extends ConsumerState<LibraryScreen> {
  int _tab = 0;

  @override
  Widget build(BuildContext context) {
    final AppLocalizations t = AppLocalizations.of(context);
    final AsyncValue<Paged<Bookmark>> bookmarks = ref.watch(_bookmarksProvider);
    final AsyncValue<List<Author>> authors = ref.watch(_authorsProvider);
    final AsyncValue<List<ReadingItem>> reading = ref.watch(_readingProvider);

    return GlassScaffold(
      appBar: GlassAppBar(
        title: t.libraryTitle,
        subtitle: t.librarySubtitle,
        automaticallyImplyLeading: false,
      ),
      body: Column(
        children: <Widget>[
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 8, 16, 4),
            child: _Segmented(
              selected: _tab,
              onChanged: (int i) => setState(() => _tab = i),
              items: <String>[
                _labelWithCount(context, t.libraryBookmarks,
                    bookmarks.valueOrNull?.items.length),
                _labelWithCount(context, t.libraryFollowedAuthors,
                    authors.valueOrNull?.length),
                _labelWithCount(context, t.libraryContinueReading,
                    reading.valueOrNull?.length),
              ],
            ),
          ),
          Expanded(
            child: IndexedStack(
              index: _tab,
              children: <Widget>[
                _bookmarksTab(context, t, bookmarks),
                _authorsTab(context, t, authors),
                _readingTab(context, t, reading),
              ],
            ),
          ),
        ],
      ),
    );
  }

  String _labelWithCount(BuildContext context, String label, int? count) {
    if (count == null) return label;
    return '$label (${context.faNum(count)})';
  }

  // --- Bookmarks -----------------------------------------------------------

  Widget _bookmarksTab(BuildContext context, AppLocalizations t,
      AsyncValue<Paged<Bookmark>> async) {
    return async.when(
      loading: () => const _ListShimmer(),
      error: (Object e, _) => ErrorRetry(
        message: e.toString(),
        onRetry: () => ref.invalidate(_bookmarksProvider),
      ),
      data: (Paged<Bookmark> page) {
        if (page.items.isEmpty) {
          return EmptyState(
            message: t.libraryNoBookmarks,
            icon: Icons.bookmark_border_rounded,
          );
        }
        return RefreshIndicator(
          onRefresh: () async => ref.invalidate(_bookmarksProvider),
          child: ListView.builder(
            padding: const EdgeInsets.fromLTRB(16, 8, 16, 104),
            itemCount: page.items.length,
            itemBuilder: (BuildContext c, int i) =>
                _bookmarkRow(context, t, page.items[i]),
          ),
        );
      },
    );
  }

  Widget _bookmarkRow(BuildContext context, AppLocalizations t, Bookmark b) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: GlassCard(
        padding: const EdgeInsets.all(12),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: <Widget>[
            _Thumb(url: b.imageUrl, width: 96, height: 68),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: <Widget>[
                  Text(
                    b.title,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                    style: Theme.of(context).textTheme.titleSmall,
                  ),
                  const SizedBox(height: 8),
                  Row(
                    children: <Widget>[
                      Icon(Icons.schedule_rounded,
                          size: 13, color: context.colors.textFaint),
                      const SizedBox(width: 4),
                      Text(
                        context.faDigits(b.dateJalali),
                        style: Theme.of(context).textTheme.bodySmall,
                      ),
                    ],
                  ),
                ],
              ),
            ),
            IconButton(
              tooltip: t.libraryRemoveBookmark,
              onPressed: () => _removeBookmark(b),
              icon: Icon(Icons.bookmark_remove_rounded,
                  color: context.colors.brand),
            ),
          ],
        ),
      ),
    );
  }

  Future<void> _removeBookmark(Bookmark b) async {
    final AppLocalizations t = AppLocalizations.of(context);
    try {
      await ref
          .read(libraryRepositoryProvider)
          .toggleBookmark(type: b.type, id: b.itemId);
      ref.invalidate(_bookmarksProvider);
    } catch (_) {
      if (mounted) _toast(context, t.commonError);
    }
  }

  // --- Authors -------------------------------------------------------------

  Widget _authorsTab(BuildContext context, AppLocalizations t,
      AsyncValue<List<Author>> async) {
    return async.when(
      loading: () => const _ListShimmer(),
      error: (Object e, _) => ErrorRetry(
        message: e.toString(),
        onRetry: () => ref.invalidate(_authorsProvider),
      ),
      data: (List<Author> authors) {
        if (authors.isEmpty) {
          return EmptyState(
            message: t.commonEmpty,
            icon: Icons.person_outline_rounded,
          );
        }
        return RefreshIndicator(
          onRefresh: () async => ref.invalidate(_authorsProvider),
          child: ListView.builder(
            padding: const EdgeInsets.fromLTRB(16, 8, 16, 104),
            itemCount: authors.length,
            itemBuilder: (BuildContext c, int i) =>
                _authorRow(context, t, authors[i]),
          ),
        );
      },
    );
  }

  Widget _authorRow(BuildContext context, AppLocalizations t, Author a) {
    final AppPalette c = context.colors;
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: GlassCard(
        padding: const EdgeInsets.all(12),
        child: Row(
          children: <Widget>[
            _Avatar(url: a.avatarUrl, name: a.name),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: <Widget>[
                  Text(
                    a.name,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: Theme.of(context).textTheme.titleSmall,
                  ),
                  if (a.type.isNotEmpty) ...<Widget>[
                    const SizedBox(height: 4),
                    Text(
                      a.type,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: Theme.of(context)
                          .textTheme
                          .bodySmall
                          ?.copyWith(color: c.textFaint),
                    ),
                  ],
                ],
              ),
            ),
            const SizedBox(width: 8),
            OutlinedButton.icon(
              onPressed: () => _unfollow(a),
              icon: const Icon(Icons.person_remove_rounded, size: 16),
              label: Text(t.libraryUnfollow),
            ),
          ],
        ),
      ),
    );
  }

  Future<void> _unfollow(Author a) async {
    final AppLocalizations t = AppLocalizations.of(context);
    try {
      await ref.read(libraryRepositoryProvider).toggleAuthor(a.id);
      ref.invalidate(_authorsProvider);
    } catch (_) {
      if (mounted) _toast(context, t.commonError);
    }
  }

  // --- Continue reading ----------------------------------------------------

  Widget _readingTab(BuildContext context, AppLocalizations t,
      AsyncValue<List<ReadingItem>> async) {
    return async.when(
      loading: () => const _ListShimmer(),
      error: (Object e, _) => ErrorRetry(
        message: e.toString(),
        onRetry: () => ref.invalidate(_readingProvider),
      ),
      data: (List<ReadingItem> items) {
        if (items.isEmpty) {
          return EmptyState(
            message: t.commonEmpty,
            icon: Icons.menu_book_outlined,
          );
        }
        return RefreshIndicator(
          onRefresh: () async => ref.invalidate(_readingProvider),
          child: ListView.builder(
            padding: const EdgeInsets.fromLTRB(16, 8, 16, 104),
            itemCount: items.length,
            itemBuilder: (BuildContext c, int i) =>
                _readingRow(context, t, items[i]),
          ),
        );
      },
    );
  }

  Widget _readingRow(BuildContext context, AppLocalizations t, ReadingItem r) {
    final int percent = r.progressPercent.clamp(0, 100).round();
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: GlassCard(
        padding: const EdgeInsets.all(12),
        onTap: () => _copyLink(r.url),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: <Widget>[
            Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: <Widget>[
                _Thumb(url: r.imageUrl, width: 96, height: 68),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: <Widget>[
                      Text(
                        r.title,
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                        style: Theme.of(context).textTheme.titleSmall,
                      ),
                      if (r.updatedAtJalali.isNotEmpty) ...<Widget>[
                        const SizedBox(height: 6),
                        Text(
                          t.libraryLastRead(
                              context.faDigits(r.updatedAtJalali)),
                          style: Theme.of(context).textTheme.bodySmall,
                        ),
                      ],
                    ],
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),
            Row(
              children: <Widget>[
                Expanded(
                    child: GradientProgressBar(percent: percent.toDouble())),
                const SizedBox(width: 10),
                Text(
                  t.libraryReadingProgress(context.faNum(percent)),
                  style: Theme.of(context)
                      .textTheme
                      .bodySmall
                      ?.copyWith(fontWeight: FontWeight.w700),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  void _copyLink(String url) {
    if (url.isEmpty) return;
    Clipboard.setData(ClipboardData(text: url));
    _toast(context, AppLocalizations.of(context).commonCopied);
  }
}

void _toast(BuildContext context, String message) {
  ScaffoldMessenger.of(context)
    ..hideCurrentSnackBar()
    ..showSnackBar(SnackBar(content: Text(message)));
}

/// Rounded segmented control mirroring the site's `.seg` pill switcher.
class _Segmented extends StatelessWidget {
  const _Segmented({
    required this.items,
    required this.selected,
    required this.onChanged,
  });

  final List<String> items;
  final int selected;
  final ValueChanged<int> onChanged;

  @override
  Widget build(BuildContext context) {
    final AppPalette c = context.colors;
    return Container(
      padding: const EdgeInsets.all(5),
      decoration: BoxDecoration(
        color: c.surface2.withValues(alpha: 0.7),
        borderRadius: BorderRadius.circular(AppRadii.pill),
        border: Border.all(color: c.glassBorderFaint),
      ),
      child: Row(
        children: <Widget>[
          for (int i = 0; i < items.length; i++)
            Expanded(
              child: GestureDetector(
                onTap: () => onChanged(i),
                child: AnimatedContainer(
                  duration: const Duration(milliseconds: 200),
                  curve: Curves.easeOut,
                  padding: const EdgeInsets.symmetric(vertical: 9),
                  decoration: BoxDecoration(
                    color: i == selected ? c.surface : Colors.transparent,
                    borderRadius: BorderRadius.circular(AppRadii.pill),
                    boxShadow: i == selected
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
                    items[i],
                    textAlign: TextAlign.center,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: TextStyle(
                      fontFamily: AppTheme.fontFamily,
                      fontWeight: FontWeight.w700,
                      fontSize: 12.5,
                      color: i == selected ? c.brand : c.textMuted,
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

/// A rounded network thumbnail with a shimmer placeholder and graceful
/// fallback when no image is available.
class _Thumb extends StatelessWidget {
  const _Thumb({required this.url, required this.width, required this.height});

  final String? url;
  final double width;
  final double height;

  @override
  Widget build(BuildContext context) {
    final AppPalette c = context.colors;
    final BorderRadius radius = BorderRadius.circular(AppRadii.md);
    final Widget fallback = Container(
      width: width,
      height: height,
      color: c.surface2,
      child: Icon(Icons.image_outlined, color: c.textFaint, size: 22),
    );
    if (url == null || url!.isEmpty) {
      return ClipRRect(borderRadius: radius, child: fallback);
    }
    return ClipRRect(
      borderRadius: radius,
      child: CachedNetworkImage(
        imageUrl: url!,
        width: width,
        height: height,
        fit: BoxFit.cover,
        placeholder: (BuildContext c2, String u) => _ShimmerBox(
          width: width,
          height: height,
          baseColor: c.surface2,
          highlightColor: c.surface,
        ),
        errorWidget: (BuildContext c2, String u, Object e) => fallback,
      ),
    );
  }
}

/// Circular avatar with an initial fallback.
class _Avatar extends StatelessWidget {
  const _Avatar({required this.url, required this.name});

  final String? url;
  final String name;

  @override
  Widget build(BuildContext context) {
    final AppPalette c = context.colors;
    const double size = 48;
    final Widget fallback = Container(
      width: size,
      height: size,
      alignment: Alignment.center,
      decoration:
          BoxDecoration(gradient: c.brandGradient, shape: BoxShape.circle),
      child: Text(
        name.isNotEmpty ? name.characters.first : '?',
        style: const TextStyle(
          color: Colors.white,
          fontWeight: FontWeight.w800,
          fontFamily: AppTheme.fontFamily,
        ),
      ),
    );
    if (url == null || url!.isEmpty) return fallback;
    return ClipOval(
      child: CachedNetworkImage(
        imageUrl: url!,
        width: size,
        height: size,
        fit: BoxFit.cover,
        placeholder: (BuildContext c2, String u) => _ShimmerBox(
          width: size,
          height: size,
          baseColor: c.surface2,
          highlightColor: c.surface,
        ),
        errorWidget: (BuildContext c2, String u, Object e) => fallback,
      ),
    );
  }
}

class _ShimmerBox extends StatelessWidget {
  const _ShimmerBox({
    required this.width,
    required this.height,
    required this.baseColor,
    required this.highlightColor,
  });

  final double width;
  final double height;
  final Color baseColor;
  final Color highlightColor;

  @override
  Widget build(BuildContext context) {
    return Shimmer.fromColors(
      baseColor: baseColor,
      highlightColor: highlightColor,
      child: Container(width: width, height: height, color: baseColor),
    );
  }
}

/// A shimmer list placeholder shown while a tab's data loads.
class _ListShimmer extends StatelessWidget {
  const _ListShimmer();

  @override
  Widget build(BuildContext context) {
    final AppPalette c = context.colors;
    return ListView.builder(
      padding: const EdgeInsets.fromLTRB(16, 8, 16, 104),
      itemCount: 6,
      itemBuilder: (BuildContext ctx, int i) => Padding(
        padding: const EdgeInsets.only(bottom: 12),
        child: Shimmer.fromColors(
          baseColor: c.surface2,
          highlightColor: c.surface,
          child: Container(
            height: 92,
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
