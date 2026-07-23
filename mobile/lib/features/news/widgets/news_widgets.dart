import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:shimmer/shimmer.dart';

import '../../../core/persian.dart';
import '../../../data/models/news_models.dart';
import '../../../l10n/app_localizations.dart';
import '../../../theme/app_colors.dart';
import '../../../theme/app_theme.dart';
import '../../../theme/glass.dart';

/// Push the article detail route for a card.
void openArticle(BuildContext context, NewsCard card) {
  context.push('/article/${card.type}/${card.code}');
}

/// Localised label for a content type.
String typeLabel(AppLocalizations t, String type) {
  switch (contentTypeFromString(type)) {
    case ContentType.video:
      return t.typeVideo;
    case ContentType.podcast:
      return t.typePodcast;
    case ContentType.photo:
      return t.typePhoto;
    case ContentType.note:
      return t.typeNote;
    case ContentType.news:
      return t.typeNews;
  }
}

/// Icon for a given content type.
IconData iconForType(String type) {
  switch (contentTypeFromString(type)) {
    case ContentType.video:
      return Icons.play_circle_outline_rounded;
    case ContentType.podcast:
      return Icons.podcasts_rounded;
    case ContentType.photo:
      return Icons.photo_library_outlined;
    case ContentType.note:
      return Icons.edit_note_rounded;
    case ContentType.news:
      return Icons.article_outlined;
  }
}

/// A network image with a shimmer placeholder and a branded error fallback.
/// Robust when offline (mock mode) — never throws, always fills its box.
class NewsImage extends StatelessWidget {
  const NewsImage({
    super.key,
    this.url,
    this.width,
    this.height,
    this.radius = AppRadii.md,
    this.fit = BoxFit.cover,
    this.overlayType,
  });

  final String? url;
  final double? width;
  final double? height;
  final double radius;
  final BoxFit fit;

  /// When set, a type badge (play/podcast/gallery) is overlaid.
  final String? overlayType;

  @override
  Widget build(BuildContext context) {
    final AppPalette c = context.colors;
    Widget placeholder() => Container(
          width: width,
          height: height,
          color: c.surface2,
          alignment: Alignment.center,
          child: Icon(Icons.image_outlined, color: c.textFaint, size: 30),
        );

    Widget image;
    if (url == null || url!.isEmpty) {
      image = placeholder();
    } else {
      image = CachedNetworkImage(
        imageUrl: url!,
        width: width,
        height: height,
        fit: fit,
        placeholder: (_, __) => Shimmer.fromColors(
          baseColor: c.surface2,
          highlightColor: c.isDark
              ? Colors.white.withValues(alpha: 0.06)
              : Colors.white.withValues(alpha: 0.6),
          child: Container(width: width, height: height, color: c.surface2),
        ),
        errorWidget: (_, __, ___) => placeholder(),
      );
    }

    Widget result = ClipRRect(
      borderRadius: BorderRadius.circular(radius),
      child: image,
    );

    final String? ot = overlayType;
    if (ot != null) {
      final ContentType t = contentTypeFromString(ot);
      if (t == ContentType.video ||
          t == ContentType.podcast ||
          t == ContentType.photo) {
        result = Stack(
          alignment: Alignment.center,
          children: <Widget>[
            result,
            Container(
              width: 46,
              height: 46,
              decoration: BoxDecoration(
                color: Colors.black.withValues(alpha: 0.45),
                shape: BoxShape.circle,
                border: Border.all(color: Colors.white70, width: 1.4),
              ),
              child: Icon(iconForType(ot), color: Colors.white, size: 26),
            ),
          ],
        );
      }
    }
    return result;
  }
}

/// A small pill labelling the category / content type.
class NewsTagPill extends StatelessWidget {
  const NewsTagPill(this.label, {super.key, this.color, this.icon});

  final String label;
  final Color? color;
  final IconData? icon;

  @override
  Widget build(BuildContext context) {
    final AppPalette c = context.colors;
    final Color col = color ?? c.brand;
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 3),
      decoration: BoxDecoration(
        color: col.withValues(alpha: 0.14),
        borderRadius: BorderRadius.circular(AppRadii.pill),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: <Widget>[
          if (icon != null) ...<Widget>[
            Icon(icon, size: 12, color: col),
            const SizedBox(width: 4),
          ],
          Flexible(
            child: Text(
              label,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              style: TextStyle(
                color: col,
                fontSize: 11,
                fontWeight: FontWeight.w700,
                fontFamily: AppTheme.fontFamily,
              ),
            ),
          ),
        ],
      ),
    );
  }
}

/// A standard list row: thumbnail + title + meta. Used in vertical lists.
class NewsListTile extends StatelessWidget {
  const NewsListTile({super.key, required this.card, this.onTap});

  final NewsCard card;
  final VoidCallback? onTap;

  @override
  Widget build(BuildContext context) {
    final AppPalette c = context.colors;
    return GlassCard(
      padding: const EdgeInsets.all(10),
      onTap: onTap ?? () => openArticle(context, card),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: <Widget>[
          SizedBox(
            width: 108,
            height: 82,
            child: NewsImage(
              url: card.imageUrl,
              width: 108,
              height: 82,
              overlayType: card.type,
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: <Widget>[
                if (card.category != null)
                  NewsTagPill(card.category!.title,
                      icon: iconForType(card.type)),
                const SizedBox(height: 6),
                Text(
                  card.title,
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                  style: Theme.of(context)
                      .textTheme
                      .titleSmall
                      ?.copyWith(height: 1.5),
                ),
                const SizedBox(height: 6),
                Row(
                  children: <Widget>[
                    Icon(Icons.schedule_rounded, size: 12, color: c.textFaint),
                    const SizedBox(width: 3),
                    Flexible(
                      child: Text(
                        card.publishedAtJalali,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: Theme.of(context).textTheme.bodySmall,
                      ),
                    ),
                    if (card.duration != null) ...<Widget>[
                      const SizedBox(width: 8),
                      Icon(Icons.timer_outlined, size: 12, color: c.textFaint),
                      const SizedBox(width: 3),
                      Text(card.duration!,
                          style: Theme.of(context).textTheme.bodySmall),
                    ],
                  ],
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

/// A fixed-width card for horizontal rails (image on top).
class NewsRailCard extends StatelessWidget {
  const NewsRailCard({super.key, required this.card, this.width = 230});

  final NewsCard card;
  final double width;

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: width,
      child: GlassCard(
        padding: const EdgeInsets.all(10),
        onTap: () => openArticle(context, card),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: <Widget>[
            AspectRatio(
              aspectRatio: 16 / 10,
              child: NewsImage(url: card.imageUrl, overlayType: card.type),
            ),
            const SizedBox(height: 10),
            Text(
              card.title,
              maxLines: 2,
              overflow: TextOverflow.ellipsis,
              style:
                  Theme.of(context).textTheme.titleSmall?.copyWith(height: 1.5),
            ),
            const SizedBox(height: 8),
            Row(
              children: <Widget>[
                if (card.category != null)
                  Expanded(
                    child: NewsTagPill(card.category!.title,
                        icon: iconForType(card.type)),
                  )
                else
                  const Spacer(),
                Text(card.duration ?? card.publishedAtJalali,
                    style: Theme.of(context).textTheme.bodySmall),
              ],
            ),
          ],
        ),
      ),
    );
  }
}

/// Section header with an optional "see all" trailing action.
class NewsSectionHeader extends StatelessWidget {
  const NewsSectionHeader(this.title,
      {super.key, this.onSeeAll, this.seeAllLabel});

  final String title;
  final VoidCallback? onSeeAll;
  final String? seeAllLabel;

  @override
  Widget build(BuildContext context) {
    final AppPalette c = context.colors;
    return Padding(
      padding: const EdgeInsets.only(bottom: 12, top: 4),
      child: Row(
        children: <Widget>[
          Container(
            width: 5,
            height: 22,
            decoration: BoxDecoration(
              color: c.accentRed,
              borderRadius: BorderRadius.circular(3),
            ),
          ),
          const SizedBox(width: 10),
          Expanded(
            child: Text(title, style: Theme.of(context).textTheme.titleLarge),
          ),
          if (onSeeAll != null)
            TextButton(
              onPressed: onSeeAll,
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: <Widget>[
                  Text(seeAllLabel ?? ''),
                  const Icon(Icons.chevron_left_rounded, size: 18),
                ],
              ),
            ),
        ],
      ),
    );
  }
}

/// Shimmer placeholder used while lists load.
class NewsShimmerList extends StatelessWidget {
  const NewsShimmerList({super.key, this.count = 6});

  final int count;

  @override
  Widget build(BuildContext context) {
    final AppPalette c = context.colors;
    return ListView.separated(
      padding: const EdgeInsets.fromLTRB(16, 12, 16, 104),
      itemCount: count,
      separatorBuilder: (_, __) => const SizedBox(height: 12),
      itemBuilder: (_, __) => Shimmer.fromColors(
        baseColor: c.surface2,
        highlightColor: c.isDark
            ? Colors.white.withValues(alpha: 0.06)
            : Colors.white.withValues(alpha: 0.6),
        child: Container(
          height: 104,
          decoration: BoxDecoration(
            color: c.surface2,
            borderRadius: BorderRadius.circular(AppRadii.lg),
          ),
        ),
      ),
    );
  }
}

/// A minimal, dependency-free HTML renderer. Splits the markup on block-level
/// boundaries and renders a small subset (`p`, `h1`–`h6`, `blockquote`, `img`,
/// `li`, `br`), stripping everything else to plain text. Robust: unknown markup
/// degrades to justified paragraphs and never throws.
class SimpleHtml extends StatelessWidget {
  const SimpleHtml(this.html, {super.key});

  final String html;

  static String _decode(String s) => s
      .replaceAll('&nbsp;', ' ')
      .replaceAll('&zwnj;', '‌')
      .replaceAll('&amp;', '&')
      .replaceAll('&lt;', '<')
      .replaceAll('&gt;', '>')
      .replaceAll('&quot;', '"')
      .replaceAll('&#39;', "'")
      .replaceAll('&laquo;', '«')
      .replaceAll('&raquo;', '»')
      .trim();

  static String _stripTags(String s) => _decode(s
      .replaceAll(RegExp(r'<[^>]*>'), ' ')
      .replaceAll(RegExp(r'[ \t]+'), ' ')
      .replaceAll(RegExp(r' *\n *'), '\n'));

  List<Widget> _build(BuildContext context) {
    final AppPalette c = context.colors;
    final TextTheme tt = Theme.of(context).textTheme;
    final List<Widget> out = <Widget>[];

    const String sentinel = '\u0001';
    // Insert a sentinel before every block-opening tag, then split on it so
    // each segment holds exactly one block element.
    final String marked = html
        .replaceAll(RegExp(r'<br\s*/?>', caseSensitive: false), '\n')
        .replaceAllMapped(
          RegExp(r'<(p|div|h[1-6]|blockquote|ul|ol|li|img)\b',
              caseSensitive: false),
          (Match m) => '$sentinel${m.group(0)}',
        );

    for (String seg in marked.split(sentinel)) {
      seg = seg.trim();
      if (seg.isEmpty) continue;

      final RegExpMatch? img =
          RegExp(r'<img[^>]*src="([^"]+)"', caseSensitive: false)
              .firstMatch(seg);
      if (img != null) {
        out.add(Padding(
          padding: const EdgeInsets.symmetric(vertical: 10),
          child: NewsImage(url: img.group(1), radius: AppRadii.md),
        ));
        final String rest = _stripTags(
            seg.replaceAll(RegExp(r'<img[^>]*>', caseSensitive: false), ''));
        if (rest.isNotEmpty) out.add(_paragraph(context, rest));
        continue;
      }

      if (RegExp(r'^<h[1-6]', caseSensitive: false).hasMatch(seg)) {
        final String text = _stripTags(seg);
        if (text.isNotEmpty) {
          out.add(Padding(
            padding: const EdgeInsets.only(top: 16, bottom: 6),
            child: Text(text,
                style: tt.titleMedium?.copyWith(fontWeight: FontWeight.w800)),
          ));
        }
        continue;
      }

      if (RegExp(r'^<blockquote', caseSensitive: false).hasMatch(seg)) {
        final String text = _stripTags(seg);
        if (text.isNotEmpty) {
          out.add(Container(
            margin: const EdgeInsets.symmetric(vertical: 10),
            padding: const EdgeInsets.fromLTRB(14, 12, 14, 12),
            decoration: BoxDecoration(
              color: c.brandSoft.withValues(alpha: c.isDark ? 0.5 : 1),
              borderRadius: BorderRadius.circular(AppRadii.md),
              border: Border(right: BorderSide(color: c.accentRed, width: 4)),
            ),
            child: Text(text,
                style: tt.bodyLarge?.copyWith(
                    fontWeight: FontWeight.w600,
                    fontStyle: FontStyle.italic)),
          ));
        }
        continue;
      }

      if (RegExp(r'^<li', caseSensitive: false).hasMatch(seg)) {
        final String text = _stripTags(seg);
        if (text.isNotEmpty) {
          out.add(Padding(
            padding: const EdgeInsets.only(bottom: 6),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: <Widget>[
                Padding(
                  padding: const EdgeInsets.only(top: 8),
                  child: Container(
                    width: 6,
                    height: 6,
                    decoration: BoxDecoration(
                        color: c.accentRed, shape: BoxShape.circle),
                  ),
                ),
                const SizedBox(width: 8),
                Expanded(child: Text(text, style: tt.bodyLarge)),
              ],
            ),
          ));
        }
        continue;
      }

      final String text = _stripTags(seg);
      if (text.isNotEmpty) out.add(_paragraph(context, text));
    }

    if (out.isEmpty) {
      final String fallback = _stripTags(html);
      if (fallback.isNotEmpty) out.add(_paragraph(context, fallback));
    }
    return out;
  }

  Widget _paragraph(BuildContext context, String text) => Padding(
        padding: const EdgeInsets.only(bottom: 12),
        child: Text(
          text,
          textAlign: TextAlign.justify,
          style: Theme.of(context).textTheme.bodyLarge?.copyWith(height: 1.9),
        ),
      );

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: _build(context),
    );
  }
}
