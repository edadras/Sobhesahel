import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../data/models/news_models.dart';
import '../../../data/providers.dart';
import '../../../data/repositories/news_repository.dart';
import '../../../l10n/app_localizations.dart';
import '../../../theme/app_colors.dart';
import '../../../theme/glass.dart';
import '../../../widgets/common.dart';
import '../widgets/news_widgets.dart';

/// Describes the source a [ContentListScreen] pulls from. Exactly one of
/// [type] / [categorySlug] / [tag] is used to pick the repository call.
class ContentListArgs {
  const ContentListArgs({
    this.type,
    this.category,
    this.categorySlug,
    this.tag,
    required this.title,
  });

  /// A content type (`news`, `note`, `video`, `podcast`, `photo`).
  final String? type;

  /// Optional category filter applied to a type list.
  final String? category;

  /// A category slug (uses the `/category/{slug}` endpoint).
  final String? categorySlug;

  /// A tag name (uses the `/tag/{name}` endpoint).
  final String? tag;

  final String title;
}

/// A reusable, paginated content list with a newest/oldest sort toggle.
class ContentListScreen extends ConsumerStatefulWidget {
  const ContentListScreen({super.key, required this.args});

  final ContentListArgs args;

  @override
  ConsumerState<ContentListScreen> createState() => _ContentListScreenState();
}

class _ContentListScreenState extends ConsumerState<ContentListScreen> {
  final ScrollController _scroll = ScrollController();
  final List<NewsCard> _items = <NewsCard>[];

  NewsSort _sort = NewsSort.newest;
  int _page = 1;
  int _lastPage = 1;
  bool _loading = false;
  bool _loadingMore = false;
  Object? _error;

  @override
  void initState() {
    super.initState();
    _scroll.addListener(_onScroll);
    _load(reset: true);
  }

  @override
  void dispose() {
    _scroll.removeListener(_onScroll);
    _scroll.dispose();
    super.dispose();
  }

  NewsRepository get _repo => ref.read(newsRepositoryProvider);

  Future<Paged<NewsCard>> _fetch(int page) {
    final ContentListArgs a = widget.args;
    if (a.categorySlug != null) {
      return _repo.category(a.categorySlug!, page: page);
    }
    if (a.tag != null) {
      return _repo.tag(a.tag!, page: page);
    }
    return _repo.contentList(
      type: a.type ?? 'news',
      category: a.category,
      sort: _sort,
      page: page,
    );
  }

  Future<void> _load({required bool reset}) async {
    if (_loading) return;
    setState(() {
      _loading = true;
      _error = null;
      if (reset) {
        _items.clear();
        _page = 1;
      }
    });
    try {
      final Paged<NewsCard> res = await _fetch(1);
      if (!mounted) return;
      setState(() {
        _items
          ..clear()
          ..addAll(res.items);
        _page = res.currentPage;
        _lastPage = res.lastPage;
        _loading = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _error = e;
        _loading = false;
      });
    }
  }

  Future<void> _loadMore() async {
    if (_loadingMore || _loading || _page >= _lastPage) return;
    setState(() => _loadingMore = true);
    try {
      final Paged<NewsCard> res = await _fetch(_page + 1);
      if (!mounted) return;
      setState(() {
        _items.addAll(res.items);
        _page = res.currentPage;
        _lastPage = res.lastPage;
        _loadingMore = false;
      });
    } catch (_) {
      if (!mounted) return;
      setState(() => _loadingMore = false);
    }
  }

  void _onScroll() {
    if (_scroll.position.pixels >=
        _scroll.position.maxScrollExtent - 400) {
      _loadMore();
    }
  }

  void _setSort(NewsSort sort) {
    if (sort == _sort) return;
    setState(() => _sort = sort);
    _load(reset: true);
  }

  @override
  Widget build(BuildContext context) {
    final AppLocalizations t = AppLocalizations.of(context);
    final bool canSort =
        widget.args.categorySlug == null && widget.args.tag == null;

    return GlassScaffold(
      appBar: GlassAppBar(
        title: widget.args.title,
        actions: <Widget>[
          IconButton(
            tooltip: t.searchTitle,
            onPressed: () => context.push('/search'),
            icon: const Icon(Icons.search_rounded),
          ),
          const SizedBox(width: 4),
        ],
      ),
      body: Column(
        children: <Widget>[
          if (canSort) _sortBar(context, t),
          Expanded(child: _body(context, t)),
        ],
      ),
    );
  }

  Widget _sortBar(BuildContext context, AppLocalizations t) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 10, 16, 4),
      child: Row(
        children: <Widget>[
          _sortChip(context, t.contentSortNewest, NewsSort.newest),
          const SizedBox(width: 8),
          _sortChip(context, t.contentSortOldest, NewsSort.oldest),
        ],
      ),
    );
  }

  Widget _sortChip(BuildContext context, String label, NewsSort sort) {
    final AppPalette c = context.colors;
    final bool selected = _sort == sort;
    return GestureDetector(
      onTap: () => _setSort(sort),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
        decoration: BoxDecoration(
          color: selected ? c.brandSoft : c.surface2,
          borderRadius: BorderRadius.circular(999),
          border: Border.all(color: selected ? c.brand : c.border),
        ),
        child: Text(
          label,
          style: Theme.of(context).textTheme.labelLarge?.copyWith(
                color: selected ? c.brand : c.textMuted,
                fontWeight: FontWeight.w700,
              ),
        ),
      ),
    );
  }

  Widget _body(BuildContext context, AppLocalizations t) {
    if (_loading && _items.isEmpty) return const NewsShimmerList();
    if (_error != null && _items.isEmpty) {
      return ErrorRetry(
        message: _error.toString(),
        onRetry: () => _load(reset: true),
      );
    }
    if (_items.isEmpty) {
      return EmptyState(message: t.contentEmpty);
    }
    return RefreshIndicator(
      onRefresh: () => _load(reset: true),
      child: ListView.separated(
        controller: _scroll,
        padding: const EdgeInsets.fromLTRB(16, 10, 16, 104),
        itemCount: _items.length + 1,
        separatorBuilder: (_, __) => const SizedBox(height: 12),
        itemBuilder: (_, int i) {
          if (i == _items.length) {
            if (_loadingMore) {
              return const Padding(
                padding: EdgeInsets.all(16),
                child: Center(
                  child: SizedBox(
                    width: 24,
                    height: 24,
                    child: CircularProgressIndicator(strokeWidth: 2.4),
                  ),
                ),
              );
            }
            return const SizedBox(height: 8);
          }
          return NewsListTile(card: _items[i]);
        },
      ),
    );
  }
}
