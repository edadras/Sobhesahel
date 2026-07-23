import 'dart:async';

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../data/models/news_models.dart';
import '../../../data/providers.dart';
import '../../../l10n/app_localizations.dart';
import '../../../theme/app_colors.dart';
import '../../../theme/app_theme.dart';
import '../../../theme/glass.dart';
import '../../../widgets/common.dart';
import '../widgets/news_widgets.dart';

/// The news-first home. Breaking ticker, featured slider, latest list, per-
/// service section rails, multimedia teasers, latest issue card, prices strip
/// and a live indicator — all from [homeProvider].
class HomeScreen extends ConsumerWidget {
  const HomeScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final AppLocalizations t = AppLocalizations.of(context);
    final AsyncValue<HomePayload> async = ref.watch(homeProvider);

    return GlassScaffold(
      appBar: GlassAppBar(
        title: t.appName,
        subtitle: t.appTagline,
        automaticallyImplyLeading: false,
        actions: <Widget>[
          IconButton(
            tooltip: t.searchTitle,
            onPressed: () => context.push('/search'),
            icon: const Icon(Icons.search_rounded),
          ),
          IconButton(
            tooltip: t.pubTitle,
            onPressed: () => context.push('/publications'),
            icon: const Icon(Icons.menu_book_rounded),
          ),
          const SizedBox(width: 4),
        ],
      ),
      body: async.when(
        loading: () => const NewsShimmerList(),
        error: (Object e, _) => ErrorRetry(
          message: e.toString(),
          onRetry: () => ref.invalidate(homeProvider),
        ),
        data: (HomePayload d) => RefreshIndicator(
          onRefresh: () async => ref.invalidate(homeProvider),
          child: ListView(
            padding: const EdgeInsets.fromLTRB(16, 8, 16, 104),
            children: <Widget>[
              if (d.live.isNotEmpty) _liveBar(context, t, d.live.first),
              if (d.breaking.isNotEmpty) ...<Widget>[
                _BreakingTicker(items: d.breaking),
                const SizedBox(height: 16),
              ],
              if (d.slider.isNotEmpty) ...<Widget>[
                _FeaturedSlider(items: d.slider),
                const SizedBox(height: 20),
              ],
              if (d.prices.isNotEmpty) ...<Widget>[
                _PricesStrip(prices: d.prices),
                const SizedBox(height: 20),
              ],
              if (d.latest.isNotEmpty) ...<Widget>[
                NewsSectionHeader(t.homeLatest,
                    seeAllLabel: t.commonSeeAll,
                    onSeeAll: () => context.push('/content/news')),
                for (final NewsCard n in d.latest.take(5))
                  Padding(
                    padding: const EdgeInsets.only(bottom: 12),
                    child: NewsListTile(card: n),
                  ),
                const SizedBox(height: 8),
              ],
              for (final HomeBox box in d.boxes)
                if (box.items.isNotEmpty) _serviceBox(context, t, box),
              if (d.videos.isNotEmpty)
                _rail(context, t, t.homeVideos, d.videos,
                    () => context.push('/content/video')),
              if (d.podcasts.isNotEmpty)
                _rail(context, t, t.homePodcasts, d.podcasts,
                    () => context.push('/content/podcast')),
              if (d.galleries.isNotEmpty)
                _rail(context, t, t.homeGalleries, d.galleries,
                    () => context.push('/content/photo')),
              if (d.latestIssue != null) ...<Widget>[
                NewsSectionHeader(t.homeLatestIssue),
                _latestIssue(context, t, d.latestIssue!),
              ],
            ],
          ),
        ),
      ),
    );
  }

  Widget _liveBar(BuildContext context, AppLocalizations t, LiveStream live) {
    final AppPalette c = context.colors;
    return Padding(
      padding: const EdgeInsets.only(bottom: 14),
      child: GlassCard(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
        onTap: () {},
        child: Row(
          children: <Widget>[
            _PulsingDot(color: c.accentRed),
            const SizedBox(width: 10),
            NewsTagPill(t.homeLiveNow, color: c.accentRed),
            const SizedBox(width: 10),
            Expanded(
              child: Text(live.title,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: Theme.of(context).textTheme.titleSmall),
            ),
            Icon(Icons.play_circle_fill_rounded, color: c.accentRed, size: 28),
          ],
        ),
      ),
    );
  }

  Widget _serviceBox(BuildContext context, AppLocalizations t, HomeBox box) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: <Widget>[
        NewsSectionHeader(box.title,
            seeAllLabel: t.commonSeeAll,
            onSeeAll: () => context.push(
                '/category/${box.slug}?title=${Uri.encodeComponent(box.title)}')),
        for (final NewsCard n in box.items.take(3))
          Padding(
            padding: const EdgeInsets.only(bottom: 12),
            child: NewsListTile(card: n),
          ),
        const SizedBox(height: 8),
      ],
    );
  }

  Widget _rail(BuildContext context, AppLocalizations t, String title,
      List<NewsCard> items, VoidCallback onSeeAll) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: <Widget>[
        NewsSectionHeader(title,
            seeAllLabel: t.commonSeeAll, onSeeAll: onSeeAll),
        SizedBox(
          height: 240,
          child: ListView.separated(
            scrollDirection: Axis.horizontal,
            padding: EdgeInsets.zero,
            itemCount: items.length,
            separatorBuilder: (_, __) => const SizedBox(width: 12),
            itemBuilder: (_, int i) => NewsRailCard(card: items[i]),
          ),
        ),
        const SizedBox(height: 20),
      ],
    );
  }

  Widget _latestIssue(
      BuildContext context, AppLocalizations t, Publication issue) {
    final AppPalette c = context.colors;
    return GlassCard(
      onTap: () => context.push('/publications'),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: <Widget>[
          SizedBox(
            width: 92,
            height: 128,
            child: NewsImage(url: issue.coverUrl, height: 128, width: 92),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: <Widget>[
                NewsTagPill(issue.type, color: c.plum),
                const SizedBox(height: 8),
                Text(issue.title,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                    style: Theme.of(context).textTheme.titleMedium),
                const SizedBox(height: 6),
                Row(
                  children: <Widget>[
                    Icon(Icons.calendar_today_rounded,
                        size: 13, color: c.textFaint),
                    const SizedBox(width: 5),
                    Text(issue.dateJalali,
                        style: Theme.of(context).textTheme.bodySmall),
                  ],
                ),
                const SizedBox(height: 14),
                GradientButton(
                  label: t.homeReadIssue,
                  icon: Icons.menu_book_rounded,
                  expand: false,
                  height: 44,
                  onPressed: () => context.push('/publications'),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

// ---------------------------------------------------------------------------
// Breaking ticker — auto-advancing single headline.
// ---------------------------------------------------------------------------

class _BreakingTicker extends StatefulWidget {
  const _BreakingTicker({required this.items});
  final List<NewsCard> items;

  @override
  State<_BreakingTicker> createState() => _BreakingTickerState();
}

class _BreakingTickerState extends State<_BreakingTicker> {
  final PageController _controller = PageController();
  Timer? _timer;
  int _index = 0;

  @override
  void initState() {
    super.initState();
    if (widget.items.length > 1) {
      _timer = Timer.periodic(const Duration(seconds: 4), (_) {
        if (!mounted || !_controller.hasClients) return;
        _index = (_index + 1) % widget.items.length;
        _controller.animateToPage(
          _index,
          duration: const Duration(milliseconds: 450),
          curve: Curves.easeInOut,
        );
      });
    }
  }

  @override
  void dispose() {
    _timer?.cancel();
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final AppPalette c = context.colors;
    final AppLocalizations t = AppLocalizations.of(context);
    return GlassCard(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      child: Row(
        children: <Widget>[
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
            decoration: BoxDecoration(
              gradient: LinearGradient(
                  colors: <Color>[c.accentRed, c.brand]),
              borderRadius: BorderRadius.circular(AppRadii.pill),
            ),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: <Widget>[
                const Icon(Icons.bolt_rounded, size: 14, color: Colors.white),
                const SizedBox(width: 3),
                Text(t.homeBreaking,
                    style: const TextStyle(
                        color: Colors.white,
                        fontWeight: FontWeight.w800,
                        fontSize: 11.5,
                        fontFamily: AppTheme.fontFamily)),
              ],
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: SizedBox(
              height: 40,
              child: PageView.builder(
                controller: _controller,
                scrollDirection: Axis.vertical,
                itemCount: widget.items.length,
                onPageChanged: (int i) => _index = i,
                itemBuilder: (_, int i) {
                  final NewsCard n = widget.items[i];
                  return InkWell(
                    onTap: () => openArticle(context, n),
                    child: Align(
                      alignment: AlignmentDirectional.centerStart,
                      child: Text(
                        n.title,
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                        style: Theme.of(context)
                            .textTheme
                            .titleSmall
                            ?.copyWith(height: 1.3),
                      ),
                    ),
                  );
                },
              ),
            ),
          ),
        ],
      ),
    );
  }
}

// ---------------------------------------------------------------------------
// Featured slider — horizontal PageView with headline overlay.
// ---------------------------------------------------------------------------

class _FeaturedSlider extends StatefulWidget {
  const _FeaturedSlider({required this.items});
  final List<NewsCard> items;

  @override
  State<_FeaturedSlider> createState() => _FeaturedSliderState();
}

class _FeaturedSliderState extends State<_FeaturedSlider> {
  final PageController _controller = PageController(viewportFraction: 0.92);
  int _index = 0;

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final AppPalette c = context.colors;
    return Column(
      children: <Widget>[
        SizedBox(
          height: 230,
          child: PageView.builder(
            controller: _controller,
            itemCount: widget.items.length,
            onPageChanged: (int i) => setState(() => _index = i),
            itemBuilder: (_, int i) {
              final NewsCard n = widget.items[i];
              return Padding(
                padding: const EdgeInsets.symmetric(horizontal: 4),
                child: GestureDetector(
                  onTap: () => openArticle(context, n),
                  child: ClipRRect(
                    borderRadius: BorderRadius.circular(AppRadii.xl),
                    child: Stack(
                      fit: StackFit.expand,
                      children: <Widget>[
                        NewsImage(url: n.imageUrl, radius: AppRadii.xl),
                        Positioned.fill(
                          child: DecoratedBox(
                            decoration: BoxDecoration(
                              gradient: LinearGradient(
                                begin: Alignment.topCenter,
                                end: Alignment.bottomCenter,
                                colors: <Color>[
                                  Colors.transparent,
                                  Colors.black.withValues(alpha: 0.35),
                                  Colors.black.withValues(alpha: 0.82),
                                ],
                              ),
                            ),
                          ),
                        ),
                        Positioned(
                          left: 16,
                          right: 16,
                          bottom: 16,
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: <Widget>[
                              if (n.category != null)
                                NewsTagPill(n.category!.title,
                                    color: c.accentRed,
                                    icon: iconForType(n.type)),
                              const SizedBox(height: 8),
                              Text(
                                n.title,
                                maxLines: 2,
                                overflow: TextOverflow.ellipsis,
                                style: const TextStyle(
                                  color: Colors.white,
                                  fontSize: 17,
                                  fontWeight: FontWeight.w800,
                                  height: 1.4,
                                  fontFamily: AppTheme.fontFamily,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              );
            },
          ),
        ),
        const SizedBox(height: 10),
        Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: <Widget>[
            for (int i = 0; i < widget.items.length; i++)
              AnimatedContainer(
                duration: const Duration(milliseconds: 250),
                margin: const EdgeInsets.symmetric(horizontal: 3),
                width: i == _index ? 20 : 7,
                height: 7,
                decoration: BoxDecoration(
                  color: i == _index ? c.brand : c.borderStrong,
                  borderRadius: BorderRadius.circular(AppRadii.pill),
                ),
              ),
          ],
        ),
      ],
    );
  }
}

// ---------------------------------------------------------------------------
// Prices strip.
// ---------------------------------------------------------------------------

class _PricesStrip extends StatelessWidget {
  const _PricesStrip({required this.prices});
  final List<PriceItem> prices;

  @override
  Widget build(BuildContext context) {
    final AppPalette c = context.colors;
    return SizedBox(
      height: 70,
      child: ListView.separated(
        scrollDirection: Axis.horizontal,
        itemCount: prices.length,
        separatorBuilder: (_, __) => const SizedBox(width: 10),
        itemBuilder: (_, int i) {
          final PriceItem p = prices[i];
          final Color col = p.isUp ? c.success : c.accentRed;
          return GlassCard(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: <Widget>[
                Text(p.title, style: Theme.of(context).textTheme.bodySmall),
                const SizedBox(height: 4),
                Row(
                  mainAxisSize: MainAxisSize.min,
                  children: <Widget>[
                    Text(p.value,
                        style: Theme.of(context)
                            .textTheme
                            .titleSmall
                            ?.copyWith(fontWeight: FontWeight.w800)),
                    const SizedBox(width: 6),
                    Icon(
                        p.isUp
                            ? Icons.arrow_drop_up_rounded
                            : Icons.arrow_drop_down_rounded,
                        size: 18,
                        color: col),
                  ],
                ),
              ],
            ),
          );
        },
      ),
    );
  }
}

class _PulsingDot extends StatefulWidget {
  const _PulsingDot({required this.color});
  final Color color;

  @override
  State<_PulsingDot> createState() => _PulsingDotState();
}

class _PulsingDotState extends State<_PulsingDot>
    with SingleTickerProviderStateMixin {
  late final AnimationController _controller = AnimationController(
    vsync: this,
    duration: const Duration(milliseconds: 900),
  )..repeat(reverse: true);

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return FadeTransition(
      opacity: Tween<double>(begin: 0.35, end: 1).animate(_controller),
      child: Container(
        width: 10,
        height: 10,
        decoration: BoxDecoration(color: widget.color, shape: BoxShape.circle),
      ),
    );
  }
}
