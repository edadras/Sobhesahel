import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../data/models/news_models.dart';
import '../../../data/providers.dart';
import '../../../data/repositories/news_repository.dart';
import '../../../l10n/app_localizations.dart';
import '../../../theme/app_colors.dart';
import '../../../theme/app_theme.dart';
import '../../../theme/glass.dart';
import '../../../widgets/common.dart';
import '../widgets/news_widgets.dart';

/// The multimedia hub (چندرسانه‌ای): videos / podcasts / photo galleries in
/// three tabs, each a paginated-in-one-shot list from [newsRepositoryProvider].
class MultimediaHubScreen extends ConsumerWidget {
  const MultimediaHubScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final AppLocalizations t = AppLocalizations.of(context);
    final AppPalette c = context.colors;
    return DefaultTabController(
      length: 3,
      child: GlassScaffold(
        appBar: GlassAppBar(
          title: t.mediaTitle,
          automaticallyImplyLeading: false,
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
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 8, 16, 4),
              child: Container(
                decoration: BoxDecoration(
                  color: c.surface2.withValues(alpha: c.isDark ? 0.5 : 0.7),
                  borderRadius: BorderRadius.circular(AppRadii.pill),
                ),
                child: TabBar(
                  indicator: BoxDecoration(
                    gradient: c.brandGradient,
                    borderRadius: BorderRadius.circular(AppRadii.pill),
                  ),
                  indicatorSize: TabBarIndicatorSize.tab,
                  dividerColor: Colors.transparent,
                  labelColor: Colors.white,
                  unselectedLabelColor: c.textMuted,
                  labelStyle: const TextStyle(
                      fontWeight: FontWeight.w700,
                      fontSize: 13.5,
                      fontFamily: AppTheme.fontFamily),
                  tabs: <Widget>[
                    Tab(text: t.mediaVideos),
                    Tab(text: t.mediaPodcasts),
                    Tab(text: t.mediaPhotos),
                  ],
                ),
              ),
            ),
            Expanded(
              child: TabBarView(
                children: <Widget>[
                  _MediaList(kind: _MediaKind.video),
                  _MediaList(kind: _MediaKind.podcast),
                  _MediaList(kind: _MediaKind.photo),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

enum _MediaKind { video, podcast, photo }

class _MediaList extends ConsumerStatefulWidget {
  const _MediaList({required this.kind});
  final _MediaKind kind;

  @override
  ConsumerState<_MediaList> createState() => _MediaListState();
}

class _MediaListState extends ConsumerState<_MediaList>
    with AutomaticKeepAliveClientMixin {
  List<NewsCard> _items = <NewsCard>[];
  bool _loading = true;
  Object? _error;

  @override
  bool get wantKeepAlive => true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final NewsRepository repo = ref.read(newsRepositoryProvider);
      final Paged<NewsCard> res;
      switch (widget.kind) {
        case _MediaKind.video:
          res = await repo.videos();
          break;
        case _MediaKind.podcast:
          res = await repo.podcasts();
          break;
        case _MediaKind.photo:
          res = await repo.galleries();
          break;
      }
      if (!mounted) return;
      setState(() {
        _items = res.items;
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

  @override
  Widget build(BuildContext context) {
    super.build(context);
    if (_loading) return const NewsShimmerList();
    if (_error != null) {
      return ErrorRetry(message: _error.toString(), onRetry: _load);
    }
    if (_items.isEmpty) return const EmptyState();

    if (widget.kind == _MediaKind.photo) {
      return RefreshIndicator(
        onRefresh: _load,
        child: GridView.builder(
          padding: const EdgeInsets.fromLTRB(16, 12, 16, 104),
          gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
            crossAxisCount: 2,
            mainAxisSpacing: 12,
            crossAxisSpacing: 12,
            childAspectRatio: 0.82,
          ),
          itemCount: _items.length,
          itemBuilder: (_, int i) => _PhotoTile(card: _items[i]),
        ),
      );
    }

    return RefreshIndicator(
      onRefresh: _load,
      child: ListView.separated(
        padding: const EdgeInsets.fromLTRB(16, 12, 16, 104),
        itemCount: _items.length,
        separatorBuilder: (_, __) => const SizedBox(height: 12),
        itemBuilder: (_, int i) => NewsListTile(card: _items[i]),
      ),
    );
  }
}

/// A grid-safe gallery tile: cover fills the cell, title pinned below.
class _PhotoTile extends StatelessWidget {
  const _PhotoTile({required this.card});
  final NewsCard card;

  @override
  Widget build(BuildContext context) {
    return GlassCard(
      padding: const EdgeInsets.all(8),
      onTap: () => openArticle(context, card),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: <Widget>[
          Expanded(
            child: NewsImage(
                url: card.imageUrl, radius: 12, overlayType: 'photo'),
          ),
          const SizedBox(height: 8),
          Text(
            card.title,
            maxLines: 2,
            overflow: TextOverflow.ellipsis,
            style:
                Theme.of(context).textTheme.titleSmall?.copyWith(height: 1.4),
          ),
        ],
      ),
    );
  }
}
