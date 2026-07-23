import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/persian.dart';
import '../../../data/models/news_models.dart';
import '../../../data/providers.dart';
import '../../../l10n/app_localizations.dart';
import '../../../theme/app_theme.dart';
import '../../../theme/glass.dart';
import '../../../widgets/common.dart';
import '../widgets/news_widgets.dart';

/// Author profile: avatar, name, bio, post count, and a list of their content.
class AuthorScreen extends ConsumerStatefulWidget {
  const AuthorScreen({super.key, required this.userType, required this.id});

  final String userType;
  final int id;

  @override
  ConsumerState<AuthorScreen> createState() => _AuthorScreenState();
}

class _AuthorScreenState extends ConsumerState<AuthorScreen> {
  AuthorProfile? _profile;
  bool _loading = true;
  Object? _error;

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
      final AuthorProfile res = await ref
          .read(newsRepositoryProvider)
          .author(userType: widget.userType, id: widget.id);
      if (!mounted) return;
      setState(() {
        _profile = res;
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
      appBar: GlassAppBar(
        title: t.authorTitle,
        actions: <Widget>[
          IconButton(
            tooltip: t.searchTitle,
            onPressed: () => context.push('/search'),
            icon: const Icon(Icons.search_rounded),
          ),
          const SizedBox(width: 4),
        ],
      ),
      body: _loading
          ? const LoadingView()
          : _error != null
              ? ErrorRetry(message: _error.toString(), onRetry: _load)
              : _body(context, t, _profile!),
    );
  }

  Widget _body(BuildContext context, AppLocalizations t, AuthorProfile p) {
    final AuthorCard au = p.author;
    return ListView(
      padding: const EdgeInsets.fromLTRB(16, 10, 16, 104),
      children: <Widget>[
        BrandHeroCard(
          child: Column(
            children: <Widget>[
              CircleAvatar(
                radius: 40,
                backgroundColor: Colors.white24,
                foregroundImage:
                    (au.avatarUrl != null && au.avatarUrl!.isNotEmpty)
                        ? NetworkImage(au.avatarUrl!)
                        : null,
                child: Text(au.initials,
                    style: const TextStyle(
                        color: Colors.white,
                        fontSize: 28,
                        fontWeight: FontWeight.w800,
                        fontFamily: AppTheme.fontFamily)),
              ),
              const SizedBox(height: 12),
              Text(au.name,
                  style: const TextStyle(
                      color: Colors.white,
                      fontSize: 20,
                      fontWeight: FontWeight.w800,
                      fontFamily: AppTheme.fontFamily)),
              const SizedBox(height: 6),
              Text(t.authorPostsCount(context.faNum(au.postsCount)),
                  style: TextStyle(color: Colors.white.withValues(alpha: 0.9))),
              if (au.bio != null) ...<Widget>[
                const SizedBox(height: 12),
                Text(au.bio!,
                    textAlign: TextAlign.center,
                    style: TextStyle(
                        color: Colors.white.withValues(alpha: 0.92),
                        height: 1.7)),
              ],
            ],
          ),
        ),
        const SizedBox(height: 18),
        NewsSectionHeader(t.authorPosts),
        if (p.posts.items.isEmpty)
          const EmptyState()
        else
          for (final NewsCard n in p.posts.items)
            Padding(
              padding: const EdgeInsets.only(bottom: 12),
              child: NewsListTile(card: n),
            ),
      ],
    );
  }
}
