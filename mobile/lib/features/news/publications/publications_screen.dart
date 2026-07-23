import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../data/models/news_models.dart';
import '../../../data/providers.dart';
import '../../../l10n/app_localizations.dart';
import '../../../theme/app_colors.dart';
import '../../../theme/glass.dart';
import '../../../widgets/common.dart';
import '../widgets/news_widgets.dart';

/// The public newspaper archive (روزنامه): a cover grid with Jalali dates. Tap
/// opens an access dialog with the PDF link (no in-app PDF viewer without a
/// plugin, matching the member archive pattern).
class PublicationsScreen extends ConsumerWidget {
  const PublicationsScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final AppLocalizations t = AppLocalizations.of(context);
    final AsyncValue<List<Publication>> async =
        ref.watch(publicationsProvider);

    return GlassScaffold(
      appBar: GlassAppBar(
        title: t.pubTitle,
        subtitle: t.pubSubtitle,
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
      body: async.when(
        loading: () => const NewsShimmerList(),
        error: (Object e, _) => ErrorRetry(
          message: e.toString(),
          onRetry: () => ref.invalidate(publicationsProvider),
        ),
        data: (List<Publication> issues) {
          if (issues.isEmpty) return const EmptyState();
          return RefreshIndicator(
            onRefresh: () async => ref.invalidate(publicationsProvider),
            child: GridView.builder(
              padding: const EdgeInsets.fromLTRB(16, 14, 16, 104),
              gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                crossAxisCount: 2,
                mainAxisSpacing: 16,
                crossAxisSpacing: 16,
                childAspectRatio: 0.6,
              ),
              itemCount: issues.length,
              itemBuilder: (_, int i) => _issueCard(context, t, issues[i]),
            ),
          );
        },
      ),
    );
  }

  Widget _issueCard(BuildContext context, AppLocalizations t, Publication p) {
    final AppPalette c = context.colors;
    return GlassCard(
      padding: const EdgeInsets.all(10),
      onTap: () => _openIssue(context, t, p),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: <Widget>[
          Expanded(
            child: ClipRRect(
              borderRadius: BorderRadius.circular(12),
              child: Stack(
                fit: StackFit.expand,
                children: <Widget>[
                  NewsImage(url: p.coverUrl, radius: 12),
                  Positioned(
                    top: 8,
                    right: 8,
                    child: NewsTagPill(
                      p.number != null ? t.pubNumber(p.number!) : p.type,
                      color: c.brand,
                    ),
                  ),
                ],
              ),
            ),
          ),
          const SizedBox(height: 10),
          Text(p.title,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              style: Theme.of(context).textTheme.titleSmall),
          const SizedBox(height: 4),
          Row(
            children: <Widget>[
              Icon(Icons.calendar_today_rounded, size: 12, color: c.textFaint),
              const SizedBox(width: 4),
              Text(p.dateJalali,
                  style: Theme.of(context).textTheme.bodySmall),
            ],
          ),
        ],
      ),
    );
  }

  void _openIssue(BuildContext context, AppLocalizations t, Publication p) {
    showModalBottomSheet<void>(
      context: context,
      showDragHandle: true,
      builder: (BuildContext ctx) {
        final AppPalette c = ctx.colors;
        return Padding(
          padding: const EdgeInsets.fromLTRB(20, 4, 20, 28),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: <Widget>[
              Text(p.title, style: Theme.of(ctx).textTheme.titleLarge),
              const SizedBox(height: 4),
              Text(p.dateJalali, style: Theme.of(ctx).textTheme.bodyMedium),
              const SizedBox(height: 16),
              Container(
                padding: const EdgeInsets.all(14),
                decoration: BoxDecoration(
                  color: c.surface2.withValues(alpha: c.isDark ? 0.5 : 0.7),
                  borderRadius: BorderRadius.circular(14),
                ),
                child: Row(
                  children: <Widget>[
                    Icon(Icons.info_outline_rounded, color: c.textMuted),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Text(t.pubAccessNote,
                          style: Theme.of(ctx).textTheme.bodySmall),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 16),
              GradientButton(
                label: t.pubOpen,
                icon: Icons.picture_as_pdf_rounded,
                onPressed: () {
                  final String url = p.pdfUrl ?? '';
                  if (url.isNotEmpty) {
                    Clipboard.setData(ClipboardData(text: url));
                  }
                  Navigator.of(ctx).pop();
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(content: Text(t.commonCopied)),
                  );
                },
              ),
            ],
          ),
        );
      },
    );
  }
}
