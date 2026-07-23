import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/persian.dart';
import '../../../data/models/news_models.dart';
import '../../../data/providers.dart';
import '../../../data/repositories/news_repository.dart';
import '../../../l10n/app_localizations.dart';
import '../../../theme/app_colors.dart';
import '../../../theme/app_theme.dart';
import '../../../theme/glass.dart';
import '../../../widgets/common.dart';
import '../widgets/news_widgets.dart';

/// Advanced search: a query field plus expandable filters (type, category,
/// from/to date, author, sort). Results are split into posts and authors,
/// matching the site's search feature.
class SearchScreen extends ConsumerStatefulWidget {
  const SearchScreen({super.key});

  @override
  ConsumerState<SearchScreen> createState() => _SearchScreenState();
}

class _SearchScreenState extends ConsumerState<SearchScreen> {
  final TextEditingController _query = TextEditingController();
  final TextEditingController _from = TextEditingController();
  final TextEditingController _to = TextEditingController();
  final TextEditingController _author = TextEditingController();

  bool _filtersOpen = false;
  String? _type;
  String? _category;
  NewsSort _sort = NewsSort.newest;

  SearchResults? _results;
  bool _loading = false;
  bool _searched = false;
  Object? _error;

  @override
  void dispose() {
    _query.dispose();
    _from.dispose();
    _to.dispose();
    _author.dispose();
    super.dispose();
  }

  Future<void> _run() async {
    FocusScope.of(context).unfocus();
    setState(() {
      _loading = true;
      _searched = true;
      _error = null;
    });
    try {
      final SearchResults res =
          await ref.read(newsRepositoryProvider).search(
                q: _query.text.trim(),
                type: _type,
                category: _category,
                from: _from.text.trim().isEmpty ? null : _from.text.trim(),
                to: _to.text.trim().isEmpty ? null : _to.text.trim(),
                author:
                    _author.text.trim().isEmpty ? null : _author.text.trim(),
                sort: _sort,
              );
      if (!mounted) return;
      setState(() {
        _results = res;
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
    final AppLocalizations t = AppLocalizations.of(context);
    return GlassScaffold(
      appBar: GlassAppBar(title: t.searchTitle),
      body: Column(
        children: <Widget>[
          _searchBar(context, t),
          if (_filtersOpen) _filters(context, t),
          Expanded(child: _results_body(context, t)),
        ],
      ),
    );
  }

  Widget _searchBar(BuildContext context, AppLocalizations t) {
    final AppPalette c = context.colors;
    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 10, 16, 6),
      child: Row(
        children: <Widget>[
          Expanded(
            child: TextField(
              controller: _query,
              textInputAction: TextInputAction.search,
              onSubmitted: (_) => _run(),
              decoration: InputDecoration(
                hintText: t.searchHint,
                prefixIcon: const Icon(Icons.search_rounded),
                suffixIcon: IconButton(
                  icon: Icon(
                    _filtersOpen
                        ? Icons.tune_rounded
                        : Icons.tune_outlined,
                    color: _filtersOpen ? c.brand : null,
                  ),
                  onPressed: () =>
                      setState(() => _filtersOpen = !_filtersOpen),
                ),
              ),
            ),
          ),
          const SizedBox(width: 10),
          SizedBox(
            height: 52,
            child: GradientButton(
              label: t.commonSearch,
              expand: false,
              onPressed: _run,
            ),
          ),
        ],
      ),
    );
  }

  Widget _filters(BuildContext context, AppLocalizations t) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 6, 16, 6),
      child: GlassCard(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: <Widget>[
            Text(t.searchFilters,
                style: Theme.of(context).textTheme.titleSmall),
            const SizedBox(height: 12),
            Text(t.searchType, style: Theme.of(context).textTheme.labelMedium),
            const SizedBox(height: 6),
            Wrap(
              spacing: 8,
              runSpacing: 8,
              children: <Widget>[
                _typeChip(context, t.searchAnyType, null),
                _typeChip(context, t.typeNews, 'news'),
                _typeChip(context, t.typeNote, 'note'),
                _typeChip(context, t.typeVideo, 'video'),
                _typeChip(context, t.typePodcast, 'podcast'),
                _typeChip(context, t.typePhoto, 'photo'),
              ],
            ),
            const SizedBox(height: 14),
            Row(
              children: <Widget>[
                Expanded(
                  child: TextField(
                    controller: _from,
                    decoration:
                        InputDecoration(labelText: t.searchFrom),
                  ),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: TextField(
                    controller: _to,
                    decoration: InputDecoration(labelText: t.searchTo),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 10),
            TextField(
              controller: _author,
              decoration: InputDecoration(labelText: t.searchAuthor),
            ),
            const SizedBox(height: 12),
            Row(
              children: <Widget>[
                Text(t.searchSort,
                    style: Theme.of(context).textTheme.labelMedium),
                const SizedBox(width: 12),
                _sortChip(context, t.contentSortNewest, NewsSort.newest),
                const SizedBox(width: 8),
                _sortChip(context, t.contentSortOldest, NewsSort.oldest),
              ],
            ),
            const SizedBox(height: 14),
            GradientButton(
              label: t.searchApply,
              icon: Icons.check_rounded,
              onPressed: _run,
            ),
          ],
        ),
      ),
    );
  }

  Widget _typeChip(BuildContext context, String label, String? value) {
    final AppPalette c = context.colors;
    final bool selected = _type == value;
    return GestureDetector(
      onTap: () => setState(() => _type = value),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 7),
        decoration: BoxDecoration(
          color: selected ? c.brandSoft : c.surface2,
          borderRadius: BorderRadius.circular(999),
          border: Border.all(color: selected ? c.brand : c.border),
        ),
        child: Text(label,
            style: Theme.of(context).textTheme.labelMedium?.copyWith(
                  color: selected ? c.brand : c.textMuted,
                  fontWeight: FontWeight.w700,
                )),
      ),
    );
  }

  Widget _sortChip(BuildContext context, String label, NewsSort sort) {
    final AppPalette c = context.colors;
    final bool selected = _sort == sort;
    return GestureDetector(
      onTap: () => setState(() => _sort = sort),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
        decoration: BoxDecoration(
          color: selected ? c.brandSoft : c.surface2,
          borderRadius: BorderRadius.circular(999),
          border: Border.all(color: selected ? c.brand : c.border),
        ),
        child: Text(label,
            style: Theme.of(context).textTheme.labelMedium?.copyWith(
                  color: selected ? c.brand : c.textMuted,
                  fontWeight: FontWeight.w700,
                )),
      ),
    );
  }

  Widget _results_body(BuildContext context, AppLocalizations t) {
    if (!_searched) {
      return EmptyState(message: t.searchStart, icon: Icons.search_rounded);
    }
    if (_loading) return const NewsShimmerList();
    if (_error != null) {
      return ErrorRetry(message: _error.toString(), onRetry: _run);
    }
    final SearchResults? r = _results;
    if (r == null || r.isEmpty) {
      return EmptyState(message: t.searchEmpty, icon: Icons.search_off_rounded);
    }
    return ListView(
      padding: const EdgeInsets.fromLTRB(16, 8, 16, 104),
      children: <Widget>[
        if (r.authors.isNotEmpty) ...<Widget>[
          NewsSectionHeader(t.searchResultsAuthors),
          for (final AuthorCard au in r.authors) _authorRow(context, au),
          const SizedBox(height: 12),
        ],
        if (r.posts.isNotEmpty) ...<Widget>[
          NewsSectionHeader(t.searchResultsPosts),
          for (final NewsCard n in r.posts)
            Padding(
              padding: const EdgeInsets.only(bottom: 12),
              child: NewsListTile(card: n),
            ),
        ],
      ],
    );
  }

  Widget _authorRow(BuildContext context, AuthorCard au) {
    final AppPalette c = context.colors;
    final AppLocalizations t = AppLocalizations.of(context);
    return Padding(
      padding: const EdgeInsets.only(bottom: 10),
      child: GlassCard(
        padding: const EdgeInsets.all(12),
        onTap: () => context.push('/author/${au.userType}/${au.id}'),
        child: Row(
          children: <Widget>[
            CircleAvatar(
              radius: 22,
              backgroundColor: c.brandSoft,
              foregroundImage:
                  (au.avatarUrl != null && au.avatarUrl!.isNotEmpty)
                      ? NetworkImage(au.avatarUrl!)
                      : null,
              child: Text(au.initials,
                  style: TextStyle(
                      color: c.brand,
                      fontWeight: FontWeight.w800,
                      fontFamily: AppTheme.fontFamily)),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: <Widget>[
                  Text(au.name,
                      style: Theme.of(context).textTheme.titleSmall),
                  const SizedBox(height: 2),
                  Text(t.authorPostsCount(context.faNum(au.postsCount)),
                      style: Theme.of(context).textTheme.bodySmall),
                ],
              ),
            ),
            Icon(Icons.chevron_left_rounded, color: c.textFaint),
          ],
        ),
      ),
    );
  }
}
