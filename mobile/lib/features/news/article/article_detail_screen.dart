import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/persian.dart';
import '../../../data/models/news_models.dart';
import '../../../data/providers.dart';
import '../../../features/auth/auth_controller.dart';
import '../../../l10n/app_localizations.dart';
import '../../../theme/app_colors.dart';
import '../../../theme/app_theme.dart';
import '../../../theme/glass.dart';
import '../../../widgets/common.dart';
import '../widgets/news_widgets.dart';

/// Full article / media detail. Handles news, notes, video, podcast and photo
/// types, renders the HTML body via [SimpleHtml], and shows related items,
/// tags, an author chip, comments (list + guest-friendly submit form) and a
/// bookmark action for logged-in members.
class ArticleDetailScreen extends ConsumerStatefulWidget {
  const ArticleDetailScreen({super.key, required this.type, required this.code});

  final String type;
  final String code;

  @override
  ConsumerState<ArticleDetailScreen> createState() =>
      _ArticleDetailScreenState();
}

class _ArticleDetailScreenState extends ConsumerState<ArticleDetailScreen> {
  bool? _bookmarkedOverride;
  bool _audioPlaying = false;

  @override
  Widget build(BuildContext context) {
    final AppLocalizations t = AppLocalizations.of(context);
    final AsyncValue<ArticleDetail> async = ref.watch(
        articleProvider(ArticleRef(type: widget.type, code: widget.code)));

    return GlassScaffold(
      appBar: GlassAppBar(
        title: t.appName,
        actions: <Widget>[
          IconButton(
            tooltip: t.searchTitle,
            onPressed: () => context.push('/search'),
            icon: const Icon(Icons.search_rounded),
          ),
          const SizedBox(width: 4),
        ],
      ),
      body: async.when(
        loading: () => const LoadingView(),
        error: (Object e, _) => ErrorRetry(
          message: e.toString(),
          onRetry: () => ref.invalidate(articleProvider(
              ArticleRef(type: widget.type, code: widget.code))),
        ),
        data: (ArticleDetail a) => _content(context, t, a),
      ),
    );
  }

  Widget _content(BuildContext context, AppLocalizations t, ArticleDetail a) {
    final AppPalette c = context.colors;
    return ListView(
      padding: const EdgeInsets.fromLTRB(16, 8, 16, 108),
      children: <Widget>[
        if (a.rotitr != null && a.rotitr!.isNotEmpty)
          Text(a.rotitr!,
              style: Theme.of(context)
                  .textTheme
                  .titleSmall
                  ?.copyWith(color: c.accentRed)),
        const SizedBox(height: 6),
        Text(a.title, style: Theme.of(context).textTheme.headlineSmall),
        const SizedBox(height: 12),
        _metaRow(context, t, a),
        const SizedBox(height: 14),
        if (a.contentType == ContentType.video)
          _videoBlock(context, t, a)
        else if (a.contentType == ContentType.photo && a.gallery.isNotEmpty)
          _galleryBlock(context, t, a)
        else if (a.imageUrl != null)
          NewsImage(url: a.imageUrl, radius: AppRadii.lg),
        if (a.contentType == ContentType.podcast) ...<Widget>[
          const SizedBox(height: 14),
          _audioBlock(context, t, a),
        ],
        const SizedBox(height: 16),
        if (a.lead.isNotEmpty)
          Container(
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: c.surface2.withValues(alpha: c.isDark ? 0.5 : 0.7),
              borderRadius: BorderRadius.circular(AppRadii.md),
            ),
            child: Text(a.lead,
                style: Theme.of(context)
                    .textTheme
                    .bodyLarge
                    ?.copyWith(fontWeight: FontWeight.w700, height: 1.8)),
          ),
        const SizedBox(height: 16),
        SimpleHtml(a.bodyHtml),
        if (a.subtitles.isNotEmpty) ...<Widget>[
          const SizedBox(height: 8),
          for (final String s in a.subtitles) _subtitle(context, s),
        ],
        const SizedBox(height: 18),
        _actionsRow(context, t, a),
        if (a.tags.isNotEmpty) ...<Widget>[
          const SizedBox(height: 20),
          _tags(context, a),
        ],
        if (a.author != null) ...<Widget>[
          const SizedBox(height: 20),
          _authorCard(context, t, a.author!),
        ],
        if (a.related.isNotEmpty) ...<Widget>[
          const SizedBox(height: 22),
          NewsSectionHeader(t.articleRelated),
          for (final NewsCard n in a.related)
            Padding(
              padding: const EdgeInsets.only(bottom: 12),
              child: NewsListTile(card: n),
            ),
        ],
        if (a.showComments) ...<Widget>[
          const SizedBox(height: 12),
          _CommentsSection(type: widget.type, code: widget.code),
        ],
      ],
    );
  }

  Widget _metaRow(BuildContext context, AppLocalizations t, ArticleDetail a) {
    final AppPalette c = context.colors;
    return Wrap(
      spacing: 12,
      runSpacing: 6,
      crossAxisAlignment: WrapCrossAlignment.center,
      children: <Widget>[
        if (a.category != null)
          GestureDetector(
            onTap: () => context.push(
                '/category/${a.category!.slug}?title=${Uri.encodeComponent(a.category!.title)}'),
            child: NewsTagPill(a.category!.title,
                icon: iconForType(a.type), color: c.brand),
          ),
        Row(
          mainAxisSize: MainAxisSize.min,
          children: <Widget>[
            Icon(Icons.calendar_today_rounded, size: 13, color: c.textFaint),
            const SizedBox(width: 4),
            Text(a.publishedAtJalali,
                style: Theme.of(context).textTheme.bodySmall),
          ],
        ),
        Row(
          mainAxisSize: MainAxisSize.min,
          children: <Widget>[
            Icon(Icons.remove_red_eye_outlined, size: 13, color: c.textFaint),
            const SizedBox(width: 4),
            Text(t.articleVisits(context.faNum(a.visits)),
                style: Theme.of(context).textTheme.bodySmall),
          ],
        ),
      ],
    );
  }

  Widget _subtitle(BuildContext context, String s) {
    return Padding(
      padding: const EdgeInsets.only(top: 12, bottom: 2),
      child: Text(s,
          style: Theme.of(context)
              .textTheme
              .titleMedium
              ?.copyWith(fontWeight: FontWeight.w800)),
    );
  }

  Widget _videoBlock(BuildContext context, AppLocalizations t, ArticleDetail a) {
    final AppPalette c = context.colors;
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: <Widget>[
        AspectRatio(
          aspectRatio: 16 / 9,
          child: NewsImage(
              url: a.imageUrl, radius: AppRadii.lg, overlayType: 'video'),
        ),
        const SizedBox(height: 10),
        if (a.videoEmbed != null || a.mediaUrl != null)
          Row(
            children: <Widget>[
              Icon(Icons.info_outline_rounded, size: 15, color: c.textMuted),
              const SizedBox(width: 6),
              Expanded(
                child: Text(t.articleVideoNote,
                    style: Theme.of(context).textTheme.bodySmall),
              ),
            ],
          ),
        const SizedBox(height: 8),
        GradientButton(
          label: t.articleOpenVideo,
          icon: Icons.play_arrow_rounded,
          expand: false,
          height: 44,
          onPressed: () => _showLinkDialog(
              context, t.articleOpenVideo, a.videoEmbed ?? a.mediaUrl ?? a.url),
        ),
      ],
    );
  }

  Widget _audioBlock(BuildContext context, AppLocalizations t, ArticleDetail a) {
    final AppPalette c = context.colors;
    return GlassCard(
      child: Row(
        children: <Widget>[
          GestureDetector(
            onTap: () => setState(() => _audioPlaying = !_audioPlaying),
            child: Container(
              width: 54,
              height: 54,
              decoration: BoxDecoration(
                gradient: c.brandGradient,
                shape: BoxShape.circle,
              ),
              child: Icon(
                  _audioPlaying
                      ? Icons.pause_rounded
                      : Icons.play_arrow_rounded,
                  color: Colors.white,
                  size: 30),
            ),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: <Widget>[
                Text(
                  _audioPlaying ? t.articlePlaying : t.articleOpenAudio,
                  style: Theme.of(context).textTheme.titleSmall,
                ),
                const SizedBox(height: 8),
                ClipRRect(
                  borderRadius: BorderRadius.circular(999),
                  child: LinearProgressIndicator(
                    value: _audioPlaying ? null : 0,
                    minHeight: 5,
                    backgroundColor: c.surface2,
                  ),
                ),
                const SizedBox(height: 6),
                Text(t.articleAudioNote,
                    style: Theme.of(context).textTheme.bodySmall),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _galleryBlock(
      BuildContext context, AppLocalizations t, ArticleDetail a) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: <Widget>[
        NewsSectionHeader(t.articleGallery),
        GridView.count(
          crossAxisCount: 2,
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          mainAxisSpacing: 10,
          crossAxisSpacing: 10,
          childAspectRatio: 1.3,
          children: <Widget>[
            for (final String img in a.gallery)
              GestureDetector(
                onTap: () => _showImage(context, img),
                child: NewsImage(url: img, radius: AppRadii.md),
              ),
          ],
        ),
      ],
    );
  }

  Widget _actionsRow(BuildContext context, AppLocalizations t, ArticleDetail a) {
    final AppPalette c = context.colors;
    final bool bookmarked = _bookmarkedOverride ?? a.bookmarked;
    return Row(
      children: <Widget>[
        Expanded(
          child: OutlinedButton.icon(
            onPressed: () => _toggleBookmark(context, t, a),
            icon: Icon(
              bookmarked
                  ? Icons.bookmark_rounded
                  : Icons.bookmark_border_rounded,
              size: 19,
              color: bookmarked ? c.brand : null,
            ),
            label: Text(bookmarked ? t.articleBookmarked : t.articleBookmark),
          ),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: OutlinedButton.icon(
            onPressed: () => _share(context, t, a),
            icon: const Icon(Icons.share_rounded, size: 18),
            label: Text(t.articleShare),
          ),
        ),
      ],
    );
  }

  Widget _tags(BuildContext context, ArticleDetail a) {
    final AppLocalizations t = AppLocalizations.of(context);
    final AppPalette c = context.colors;
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: <Widget>[
        Text(t.articleTags, style: Theme.of(context).textTheme.titleSmall),
        const SizedBox(height: 10),
        Wrap(
          spacing: 8,
          runSpacing: 8,
          children: <Widget>[
            for (final String tag in a.tags)
              GestureDetector(
                onTap: () => context.push('/tag/${Uri.encodeComponent(tag)}'),
                child: Container(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                  decoration: BoxDecoration(
                    color: c.surface2,
                    borderRadius: BorderRadius.circular(999),
                    border: Border.all(color: c.border),
                  ),
                  child: Text('#$tag',
                      style: Theme.of(context).textTheme.labelMedium),
                ),
              ),
          ],
        ),
      ],
    );
  }

  Widget _authorCard(BuildContext context, AppLocalizations t, AuthorCard au) {
    final AppPalette c = context.colors;
    return GlassCard(
      onTap: () => context.push('/author/${au.userType}/${au.id}'),
      child: Row(
        children: <Widget>[
          CircleAvatar(
            radius: 28,
            backgroundColor: c.brandSoft,
            foregroundImage: (au.avatarUrl != null && au.avatarUrl!.isNotEmpty)
                ? NetworkImage(au.avatarUrl!)
                : null,
            child: Text(au.initials,
                style: TextStyle(
                    color: c.brand,
                    fontWeight: FontWeight.w800,
                    fontFamily: AppTheme.fontFamily)),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: <Widget>[
                Text(t.articleByAuthor(au.name),
                    style: Theme.of(context).textTheme.titleSmall),
                if (au.bio != null) ...<Widget>[
                  const SizedBox(height: 4),
                  Text(au.bio!,
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                      style: Theme.of(context).textTheme.bodySmall),
                ],
              ],
            ),
          ),
          Icon(Icons.chevron_left_rounded, color: c.textFaint),
        ],
      ),
    );
  }

  Future<void> _toggleBookmark(
      BuildContext context, AppLocalizations t, ArticleDetail a) async {
    final AuthState auth = ref.read(authControllerProvider);
    final ScaffoldMessengerState messenger = ScaffoldMessenger.of(context);
    if (!auth.isAuthenticated) {
      messenger.showSnackBar(
        SnackBar(
          content: Text(t.articleLoginToBookmark),
          action: SnackBarAction(
            label: t.accountLogin,
            onPressed: () => context.push('/login'),
          ),
        ),
      );
      return;
    }
    final bool now = !(_bookmarkedOverride ?? a.bookmarked);
    setState(() => _bookmarkedOverride = now);
    try {
      await ref
          .read(libraryRepositoryProvider)
          .toggleBookmark(type: a.type, id: a.id);
    } catch (_) {
      if (mounted) setState(() => _bookmarkedOverride = !now);
    }
    messenger.showSnackBar(SnackBar(
      content: Text(now ? t.articleBookmarked : t.articleBookmark),
    ));
  }

  void _share(BuildContext context, AppLocalizations t, ArticleDetail a) {
    Clipboard.setData(ClipboardData(text: '${t.appName} — ${a.title}'));
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(t.commonCopied)),
    );
  }

  void _showLinkDialog(BuildContext context, String title, String url) {
    final AppLocalizations t = AppLocalizations.of(context);
    showDialog<void>(
      context: context,
      builder: (BuildContext ctx) => AlertDialog(
        title: Text(title),
        content: SelectableText(url),
        actions: <Widget>[
          TextButton(
            onPressed: () {
              Clipboard.setData(ClipboardData(text: url));
              Navigator.of(ctx).pop();
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(content: Text(t.commonCopied)),
              );
            },
            child: Text(t.commonCopy),
          ),
          TextButton(
            onPressed: () => Navigator.of(ctx).pop(),
            child: Text(t.commonClose),
          ),
        ],
      ),
    );
  }

  void _showImage(BuildContext context, String url) {
    showDialog<void>(
      context: context,
      builder: (BuildContext ctx) => Dialog(
        backgroundColor: Colors.transparent,
        insetPadding: const EdgeInsets.all(16),
        child: GestureDetector(
          onTap: () => Navigator.of(ctx).pop(),
          child: InteractiveViewer(
            child: NewsImage(url: url, radius: AppRadii.lg),
          ),
        ),
      ),
    );
  }
}

// ---------------------------------------------------------------------------
// Comments
// ---------------------------------------------------------------------------

class _CommentsSection extends ConsumerStatefulWidget {
  const _CommentsSection({required this.type, required this.code});
  final String type;
  final String code;

  @override
  ConsumerState<_CommentsSection> createState() => _CommentsSectionState();
}

class _CommentsSectionState extends ConsumerState<_CommentsSection> {
  final TextEditingController _name = TextEditingController();
  final TextEditingController _email = TextEditingController();
  final TextEditingController _body = TextEditingController();

  List<CommentItem> _comments = <CommentItem>[];
  bool _loading = true;
  bool _submitting = false;

  @override
  void initState() {
    super.initState();
    _loadComments();
  }

  @override
  void dispose() {
    _name.dispose();
    _email.dispose();
    _body.dispose();
    super.dispose();
  }

  Future<void> _loadComments() async {
    try {
      final List<CommentItem> res = await ref
          .read(newsRepositoryProvider)
          .comments(type: widget.type, code: widget.code);
      if (!mounted) return;
      setState(() {
        _comments = res;
        _loading = false;
      });
    } catch (_) {
      if (!mounted) return;
      setState(() => _loading = false);
    }
  }

  Future<void> _submit() async {
    final AppLocalizations t = AppLocalizations.of(context);
    if (_body.text.trim().isEmpty) return;
    setState(() => _submitting = true);
    final ScaffoldMessengerState messenger = ScaffoldMessenger.of(context);
    try {
      final CommentItem created = await ref
          .read(newsRepositoryProvider)
          .postComment(
            type: widget.type,
            code: widget.code,
            name: _name.text.trim(),
            body: _body.text.trim(),
            email: _email.text.trim().isEmpty ? null : _email.text.trim(),
          );
      if (!mounted) return;
      setState(() {
        _comments = <CommentItem>[created, ..._comments];
        _submitting = false;
        _body.clear();
      });
      messenger.showSnackBar(SnackBar(content: Text(t.articleCommentSent)));
    } catch (e) {
      if (!mounted) return;
      setState(() => _submitting = false);
      messenger.showSnackBar(SnackBar(content: Text(e.toString())));
    }
  }

  @override
  Widget build(BuildContext context) {
    final AppLocalizations t = AppLocalizations.of(context);
    final AppPalette c = context.colors;
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: <Widget>[
        NewsSectionHeader(
            t.articleCommentsCount(context.faNum(_comments.length))),
        if (_loading)
          const Padding(
            padding: EdgeInsets.all(16),
            child: Center(child: CircularProgressIndicator()),
          )
        else if (_comments.isEmpty)
          Padding(
            padding: const EdgeInsets.symmetric(vertical: 8),
            child: Text(t.articleNoComments,
                style: Theme.of(context).textTheme.bodyMedium),
          )
        else
          for (final CommentItem cm in _comments) _commentTile(context, cm),
        const SizedBox(height: 16),
        GlassCard(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: <Widget>[
              Text(t.articleWriteComment,
                  style: Theme.of(context).textTheme.titleSmall),
              const SizedBox(height: 12),
              TextField(
                controller: _name,
                decoration: InputDecoration(labelText: t.articleName),
              ),
              const SizedBox(height: 10),
              TextField(
                controller: _email,
                keyboardType: TextInputType.emailAddress,
                decoration: InputDecoration(labelText: t.articleEmail),
              ),
              const SizedBox(height: 10),
              TextField(
                controller: _body,
                minLines: 3,
                maxLines: 5,
                decoration: InputDecoration(labelText: t.articleCommentBody),
              ),
              const SizedBox(height: 14),
              GradientButton(
                label: t.articleSubmitComment,
                icon: Icons.send_rounded,
                loading: _submitting,
                onPressed: _submitting ? null : _submit,
              ),
              const SizedBox(height: 4),
              Text(t.articleCommentGuestNote,
                  style: Theme.of(context)
                      .textTheme
                      .bodySmall
                      ?.copyWith(color: c.textFaint)),
            ],
          ),
        ),
      ],
    );
  }

  Widget _commentTile(BuildContext context, CommentItem cm) {
    final AppPalette c = context.colors;
    return Padding(
      padding: const EdgeInsets.only(bottom: 10),
      child: GlassCard(
        padding: const EdgeInsets.all(14),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: <Widget>[
            Row(
              children: <Widget>[
                CircleAvatar(
                  radius: 16,
                  backgroundColor: c.brandSoft,
                  child: Text(
                    cm.name.isNotEmpty ? cm.name[0] : '؟',
                    style: TextStyle(
                        color: c.brand,
                        fontSize: 13,
                        fontWeight: FontWeight.w800,
                        fontFamily: AppTheme.fontFamily),
                  ),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: Text(cm.name,
                      style: Theme.of(context).textTheme.titleSmall),
                ),
                Text(cm.createdAtJalali,
                    style: Theme.of(context).textTheme.bodySmall),
              ],
            ),
            const SizedBox(height: 8),
            Text(cm.body,
                style: Theme.of(context)
                    .textTheme
                    .bodyMedium
                    ?.copyWith(color: c.text, height: 1.7)),
          ],
        ),
      ),
    );
  }
}
