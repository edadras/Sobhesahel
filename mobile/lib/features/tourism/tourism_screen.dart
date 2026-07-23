import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:shimmer/shimmer.dart';

import '../../data/models/models.dart';
import '../../data/providers.dart';
import '../../l10n/app_localizations.dart';
import '../../theme/app_colors.dart';
import '../../theme/app_theme.dart';
import '../../theme/glass.dart';
import '../../widgets/common.dart';

/// The Hormozgan tourism feed — image / title / excerpt cards with a bookmark
/// toggle. Content comes from [TourismRepository] via [tourismRepositoryProvider];
/// bookmarking persists through the shared library bookmark endpoint. Mirrors
/// `tourism.html`.
class TourismScreen extends ConsumerStatefulWidget {
  const TourismScreen({super.key});

  @override
  ConsumerState<TourismScreen> createState() => _TourismScreenState();
}

class _TourismScreenState extends ConsumerState<TourismScreen> {
  final List<TourismItem> _items = <TourismItem>[];
  final Map<int, bool> _bookmarked = <int, bool>{};
  Object? _error;
  bool _loading = true;
  bool _loadingMore = false;
  int _page = 1;
  int _lastPage = 1;

  @override
  void initState() {
    super.initState();
    // Deferred so the first synchronous setState in _load runs after mount.
    Future<void>.microtask(() => _load(reset: true));
  }

  Future<void> _load({bool reset = false}) async {
    if (reset) {
      setState(() {
        _loading = true;
        _error = null;
        _page = 1;
      });
    } else {
      setState(() => _loadingMore = true);
    }
    try {
      final Paged<TourismItem> page =
          await ref.read(tourismRepositoryProvider).feed(page: _page);
      if (!mounted) return;
      setState(() {
        if (reset) _items.clear();
        _items.addAll(page.items);
        for (final TourismItem it in page.items) {
          _bookmarked.putIfAbsent(it.id, () => it.bookmarked);
        }
        _lastPage = page.lastPage;
        _loading = false;
        _loadingMore = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _error = e;
        _loading = false;
        _loadingMore = false;
      });
    }
  }

  Future<void> _loadMore() async {
    if (_loadingMore || _page >= _lastPage) return;
    _page += 1;
    await _load();
  }

  Future<void> _toggleBookmark(TourismItem item) async {
    final bool current = _bookmarked[item.id] ?? item.bookmarked;
    setState(() => _bookmarked[item.id] = !current);
    try {
      final bool now = await ref
          .read(libraryRepositoryProvider)
          .toggleBookmark(type: 'tourism', id: item.id);
      if (!mounted) return;
      setState(() => _bookmarked[item.id] = now);
    } catch (_) {
      if (!mounted) return;
      setState(() => _bookmarked[item.id] = current); // revert
    }
  }

  @override
  Widget build(BuildContext context) {
    final AppLocalizations t = AppLocalizations.of(context);
    return GlassScaffold(
      appBar: GlassAppBar(title: t.tourismTitle, subtitle: t.tourismSubtitle),
      body: _body(context, t),
    );
  }

  Widget _body(BuildContext context, AppLocalizations t) {
    if (_loading) return const _TourismShimmer();
    if (_error != null) {
      return ErrorRetry(
        message: _error.toString(),
        onRetry: () => _load(reset: true),
      );
    }
    if (_items.isEmpty) {
      return RefreshIndicator(
        onRefresh: () => _load(reset: true),
        child: ListView(
          children: <Widget>[
            SizedBox(height: MediaQuery.of(context).size.height * 0.28),
            EmptyState(message: t.tourismEmpty, icon: Icons.map_outlined),
          ],
        ),
      );
    }
    return RefreshIndicator(
      onRefresh: () => _load(reset: true),
      child: ListView.builder(
        padding: const EdgeInsets.fromLTRB(16, 12, 16, 104),
        itemCount: _items.length + 1,
        itemBuilder: (BuildContext ctx, int i) {
          if (i == _items.length) return _footer(context, t);
          return _card(context, t, _items[i]);
        },
      ),
    );
  }

  Widget _footer(BuildContext context, AppLocalizations t) {
    if (_page >= _lastPage) return const SizedBox(height: 8);
    return Padding(
      padding: const EdgeInsets.only(top: 6),
      child: Center(
        child: _loadingMore
            ? const Padding(
                padding: EdgeInsets.all(12),
                child: SizedBox(
                  width: 24,
                  height: 24,
                  child: CircularProgressIndicator(strokeWidth: 2.4),
                ),
              )
            : OutlinedButton.icon(
                onPressed: _loadMore,
                icon: const Icon(Icons.expand_more_rounded, size: 18),
                label: Text(t.commonSeeMore),
              ),
      ),
    );
  }

  Widget _card(BuildContext context, AppLocalizations t, TourismItem item) {
    final AppPalette c = context.colors;
    final bool saved = _bookmarked[item.id] ?? item.bookmarked;
    return Padding(
      padding: const EdgeInsets.only(bottom: 14),
      child: GlassCard(
        padding: EdgeInsets.zero,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: <Widget>[
            ClipRRect(
              borderRadius:
                  const BorderRadius.vertical(top: Radius.circular(18)),
              child: AspectRatio(
                aspectRatio: 16 / 10,
                child: _CoverImage(url: item.imageUrl),
              ),
            ),
            Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: <Widget>[
                  Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: <Widget>[
                      Expanded(
                        child: Text(
                          item.title,
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                          style: Theme.of(context).textTheme.titleMedium,
                        ),
                      ),
                      const SizedBox(width: 8),
                      _BookmarkButton(
                        saved: saved,
                        onTap: () => _toggleBookmark(item),
                      ),
                    ],
                  ),
                  if (item.excerpt.isNotEmpty) ...<Widget>[
                    const SizedBox(height: 8),
                    Text(
                      item.excerpt,
                      maxLines: 3,
                      overflow: TextOverflow.ellipsis,
                      style: Theme.of(context)
                          .textTheme
                          .bodyMedium
                          ?.copyWith(color: c.textMuted, height: 1.6),
                    ),
                  ],
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _BookmarkButton extends StatelessWidget {
  const _BookmarkButton({required this.saved, required this.onTap});

  final bool saved;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    final AppPalette c = context.colors;
    return Material(
      color: Colors.transparent,
      child: InkResponse(
        onTap: onTap,
        radius: 24,
        child: Container(
          width: 40,
          height: 40,
          alignment: Alignment.center,
          decoration: BoxDecoration(
            color: saved ? c.brandSoft : c.surface2.withValues(alpha: 0.6),
            borderRadius: BorderRadius.circular(12),
          ),
          child: Icon(
            saved ? Icons.bookmark_rounded : Icons.bookmark_border_rounded,
            color: saved ? c.brand : c.textMuted,
            size: 20,
          ),
        ),
      ),
    );
  }
}

class _CoverImage extends StatelessWidget {
  const _CoverImage({required this.url});

  final String? url;

  @override
  Widget build(BuildContext context) {
    final AppPalette c = context.colors;
    final Widget fallback = Container(
      color: c.surface2,
      alignment: Alignment.center,
      child: Icon(Icons.landscape_rounded, color: c.textFaint, size: 34),
    );
    if (url == null || url!.isEmpty) return fallback;
    return CachedNetworkImage(
      imageUrl: url!,
      fit: BoxFit.cover,
      placeholder: (BuildContext ctx, String u) => Shimmer.fromColors(
        baseColor: c.surface2,
        highlightColor: c.surface,
        child: Container(color: c.surface2),
      ),
      errorWidget: (BuildContext ctx, String u, Object e) => fallback,
    );
  }
}

class _TourismShimmer extends StatelessWidget {
  const _TourismShimmer();

  @override
  Widget build(BuildContext context) {
    final AppPalette c = context.colors;
    return Shimmer.fromColors(
      baseColor: c.surface2,
      highlightColor: c.surface,
      child: ListView.builder(
        padding: const EdgeInsets.fromLTRB(16, 12, 16, 104),
        itemCount: 4,
        itemBuilder: (BuildContext ctx, int i) => Padding(
          padding: const EdgeInsets.only(bottom: 14),
          child: Container(
            height: 240,
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
