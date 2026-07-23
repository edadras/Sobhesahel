import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../data/models/news_models.dart';
import '../../../data/providers.dart';
import '../../../l10n/app_localizations.dart';
import '../../../theme/app_colors.dart';
import '../../../theme/glass.dart';
import '../../../widgets/common.dart';
import '../widgets/news_widgets.dart';

/// The services / categories tab. Renders the menu tree from [menuProvider];
/// tapping a leaf opens the matching content list or category feed.
class CategoriesScreen extends ConsumerWidget {
  const CategoriesScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final AppLocalizations t = AppLocalizations.of(context);
    final AsyncValue<List<MenuItem>> async = ref.watch(menuProvider);

    return GlassScaffold(
      appBar: GlassAppBar(
        title: t.catTitle,
        subtitle: t.catSubtitle,
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
          onRetry: () => ref.invalidate(menuProvider),
        ),
        data: (List<MenuItem> menu) {
          if (menu.isEmpty) return const EmptyState();
          return ListView(
            padding: const EdgeInsets.fromLTRB(16, 12, 16, 104),
            children: <Widget>[
              for (final MenuItem item in menu) _menuTile(context, t, item),
            ],
          );
        },
      ),
    );
  }

  void _openMenu(BuildContext context, MenuItem item) {
    final String title = Uri.encodeComponent(item.title);
    if (item.type == 'category') {
      context.push('/category/${item.slug}?title=$title');
    } else {
      context.push('/content/${item.type}?title=$title');
    }
  }

  Widget _menuTile(BuildContext context, AppLocalizations t, MenuItem item) {
    final AppPalette c = context.colors;
    if (!item.hasChildren) {
      return Padding(
        padding: const EdgeInsets.only(bottom: 12),
        child: GlassCard(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
          onTap: () => _openMenu(context, item),
          child: Row(
            children: <Widget>[
              Container(
                width: 40,
                height: 40,
                decoration: BoxDecoration(
                  color: c.brand.withValues(alpha: 0.14),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Icon(iconForType(item.type), color: c.brand, size: 20),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Text(item.title,
                    style: Theme.of(context).textTheme.titleMedium),
              ),
              Icon(Icons.chevron_left_rounded, color: c.textFaint),
            ],
          ),
        ),
      );
    }

    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: GlassCard(
        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
        child: Theme(
          data: Theme.of(context)
              .copyWith(dividerColor: Colors.transparent),
          child: ExpansionTile(
            shape: const Border(),
            collapsedShape: const Border(),
            tilePadding: const EdgeInsets.symmetric(horizontal: 10),
            childrenPadding: const EdgeInsets.only(bottom: 8),
            leading: Container(
              width: 40,
              height: 40,
              decoration: BoxDecoration(
                color: c.plum.withValues(alpha: 0.16),
                borderRadius: BorderRadius.circular(12),
              ),
              child: Icon(Icons.folder_rounded, color: c.plum, size: 20),
            ),
            title: Text(item.title,
                style: Theme.of(context).textTheme.titleMedium),
            children: <Widget>[
              ListTile(
                dense: true,
                contentPadding:
                    const EdgeInsets.only(left: 16, right: 64),
                leading:
                    Icon(Icons.list_alt_rounded, size: 18, color: c.brand),
                title: Text(t.catAllInService,
                    style: Theme.of(context).textTheme.titleSmall),
                onTap: () => _openMenu(context, item),
              ),
              for (final MenuItem child in item.children)
                ListTile(
                  dense: true,
                  contentPadding:
                      const EdgeInsets.only(left: 16, right: 64),
                  leading: Icon(Icons.subdirectory_arrow_left_rounded,
                      size: 18, color: c.textFaint),
                  title: Text(child.title,
                      style: Theme.of(context).textTheme.bodyLarge),
                  onTap: () => _openMenu(context, child),
                ),
            ],
          ),
        ),
      ),
    );
  }
}
