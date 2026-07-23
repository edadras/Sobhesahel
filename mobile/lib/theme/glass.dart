import 'dart:ui';

import 'package:flutter/material.dart';

import 'app_colors.dart';
import 'app_theme.dart';

/// The foundational glass surface: a rounded, blurred, translucent panel with
/// a soft gradient fill, a hairline light border and a diffuse shadow. Every
/// other glass widget builds on this.
class GlassContainer extends StatelessWidget {
  const GlassContainer({
    super.key,
    required this.child,
    this.padding = const EdgeInsets.all(20),
    this.margin,
    this.borderRadius,
    this.blur = 18,
    this.width,
    this.height,
    this.strong = false,
    this.gradient,
    this.borderColor,
    this.onTap,
    this.alignment,
  });

  final Widget child;
  final EdgeInsetsGeometry padding;
  final EdgeInsetsGeometry? margin;
  final BorderRadius? borderRadius;
  final double blur;
  final double? width;
  final double? height;

  /// Use the stronger, more opaque fill (for prominent surfaces).
  final bool strong;

  /// Optional custom fill gradient (e.g. the brand gradient on hero cards).
  final Gradient? gradient;
  final Color? borderColor;
  final VoidCallback? onTap;
  final AlignmentGeometry? alignment;

  @override
  Widget build(BuildContext context) {
    final AppPalette c = context.colors;
    final BorderRadius radius =
        borderRadius ?? BorderRadius.circular(AppRadii.xl);
    final Color fill = strong ? c.glassFillStrong : c.glassFill;

    Widget content = ClipRRect(
      borderRadius: radius,
      child: BackdropFilter(
        filter: ImageFilter.blur(sigmaX: blur, sigmaY: blur),
        child: Container(
          padding: padding,
          alignment: alignment,
          decoration: BoxDecoration(
            gradient: gradient ??
                LinearGradient(
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                  colors: <Color>[
                    fill,
                    fill.withValues(alpha: fill.a * 0.6),
                  ],
                ),
            borderRadius: radius,
            border: Border.all(
              color: borderColor ?? c.glassBorder,
              width: 1.1,
            ),
          ),
          child: child,
        ),
      ),
    );

    content = DecoratedBox(
      decoration: BoxDecoration(
        borderRadius: radius,
        boxShadow: <BoxShadow>[
          BoxShadow(
            color: c.isDark
                ? Colors.black.withValues(alpha: 0.45)
                : c.plum.withValues(alpha: 0.10),
            blurRadius: 28,
            offset: const Offset(0, 14),
          ),
        ],
      ),
      child: content,
    );

    if (onTap != null) {
      content = Material(
        color: Colors.transparent,
        borderRadius: radius,
        child: InkWell(
          borderRadius: radius,
          onTap: onTap,
          child: content,
        ),
      );
    }

    return SizedBox(
      width: width,
      height: height,
      child: Padding(
        padding: margin ?? EdgeInsets.zero,
        child: content,
      ),
    );
  }
}

/// A ready-to-use content card. Thin wrapper over [GlassContainer] with the
/// card's default 20px padding and 20px radius.
class GlassCard extends StatelessWidget {
  const GlassCard({
    super.key,
    required this.child,
    this.padding = const EdgeInsets.all(18),
    this.onTap,
    this.strong = false,
    this.gradient,
  });

  final Widget child;
  final EdgeInsetsGeometry padding;
  final VoidCallback? onTap;
  final bool strong;
  final Gradient? gradient;

  @override
  Widget build(BuildContext context) {
    return GlassContainer(
      padding: padding,
      borderRadius: BorderRadius.circular(AppRadii.lg),
      strong: strong,
      gradient: gradient,
      onTap: onTap,
      child: child,
    );
  }
}

/// A hero card painted with the brand plum→crimson gradient and the site's
/// signature diagonal hatch overlay. Used for dashboard/points headers.
class BrandHeroCard extends StatelessWidget {
  const BrandHeroCard({
    super.key,
    required this.child,
    this.padding = const EdgeInsets.all(24),
    this.deep = false,
  });

  final Widget child;
  final EdgeInsetsGeometry padding;
  final bool deep;

  @override
  Widget build(BuildContext context) {
    final AppPalette c = context.colors;
    final BorderRadius radius = BorderRadius.circular(AppRadii.xl);
    return DecoratedBox(
      decoration: BoxDecoration(
        borderRadius: radius,
        boxShadow: <BoxShadow>[
          BoxShadow(
            color: c.brand.withValues(alpha: 0.28),
            blurRadius: 30,
            offset: const Offset(0, 16),
          ),
        ],
      ),
      child: ClipRRect(
        borderRadius: radius,
        child: Stack(
          children: <Widget>[
            Positioned.fill(
              child: DecoratedBox(
                decoration: BoxDecoration(
                  gradient: deep ? c.brandGradientDeep : c.brandGradient,
                ),
              ),
            ),
            const Positioned.fill(child: _HatchOverlay()),
            Padding(padding: padding, child: child),
          ],
        ),
      ),
    );
  }
}

/// The subtle 135° repeating hatch that appears on every branded surface of
/// the site (`repeating-linear-gradient(135deg, …)`).
class _HatchOverlay extends StatelessWidget {
  const _HatchOverlay({super.key});

  @override
  Widget build(BuildContext context) {
    return CustomPaint(painter: _HatchPainter());
  }
}

class _HatchPainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final Paint paint = Paint()
      ..color = Colors.white.withValues(alpha: 0.06)
      ..strokeWidth = 2;
    const double gap = 11;
    for (double x = -size.height; x < size.width; x += gap) {
      canvas.drawLine(
        Offset(x, 0),
        Offset(x + size.height, size.height),
        paint,
      );
    }
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}

/// A blurred, translucent [AppBar] replacement. Implements
/// [PreferredSizeWidget] so it can be passed straight to `Scaffold.appBar`.
class GlassAppBar extends StatelessWidget implements PreferredSizeWidget {
  const GlassAppBar({
    super.key,
    required this.title,
    this.subtitle,
    this.actions,
    this.leading,
    this.automaticallyImplyLeading = true,
    this.centerTitle = false,
  });

  final String title;
  final String? subtitle;
  final List<Widget>? actions;
  final Widget? leading;
  final bool automaticallyImplyLeading;
  final bool centerTitle;

  @override
  Size get preferredSize => Size.fromHeight(subtitle == null ? 60 : 74);

  @override
  Widget build(BuildContext context) {
    final AppPalette c = context.colors;
    return ClipRect(
      child: BackdropFilter(
        filter: ImageFilter.blur(sigmaX: 20, sigmaY: 20),
        child: DecoratedBox(
          decoration: BoxDecoration(
            color: c.headerBg.withValues(alpha: c.isDark ? 0.55 : 0.65),
            border: Border(
              bottom: BorderSide(color: c.glassBorderFaint),
            ),
          ),
          child: AppBar(
            backgroundColor: Colors.transparent,
            automaticallyImplyLeading: automaticallyImplyLeading,
            leading: leading,
            centerTitle: centerTitle,
            actions: actions,
            toolbarHeight: preferredSize.height,
            titleSpacing: 20,
            title: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: <Widget>[
                Text(title, style: Theme.of(context).textTheme.titleLarge),
                if (subtitle != null)
                  Text(
                    subtitle!,
                    style: Theme.of(context)
                        .textTheme
                        .bodySmall
                        ?.copyWith(color: c.textMuted),
                  ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

/// A full-screen scaffold whose background is the brightness-aware gradient,
/// with an optional glass [appBar]. Use everywhere instead of [Scaffold] to
/// get a consistent backdrop for the frosted surfaces.
class GlassScaffold extends StatelessWidget {
  const GlassScaffold({
    super.key,
    required this.body,
    this.appBar,
    this.bottomNavigationBar,
    this.floatingActionButton,
    this.extendBody = true,
    this.extendBodyBehindAppBar = false,
    this.resizeToAvoidBottomInset,
  });

  final Widget body;
  final PreferredSizeWidget? appBar;
  final Widget? bottomNavigationBar;
  final Widget? floatingActionButton;
  final bool extendBody;
  final bool extendBodyBehindAppBar;
  final bool? resizeToAvoidBottomInset;

  @override
  Widget build(BuildContext context) {
    final AppPalette c = context.colors;
    return DecoratedBox(
      decoration: BoxDecoration(gradient: c.scaffoldGradient),
      child: Stack(
        children: <Widget>[
          // Soft brand glow blobs behind the glass for depth.
          Positioned(
            top: -120,
            right: -80,
            child: _GlowBlob(color: c.brand.withValues(alpha: 0.20)),
          ),
          Positioned(
            bottom: -140,
            left: -100,
            child: _GlowBlob(color: c.plum.withValues(alpha: 0.18)),
          ),
          Scaffold(
            backgroundColor: Colors.transparent,
            appBar: appBar,
            body: body,
            bottomNavigationBar: bottomNavigationBar,
            floatingActionButton: floatingActionButton,
            extendBody: extendBody,
            extendBodyBehindAppBar: extendBodyBehindAppBar,
            resizeToAvoidBottomInset: resizeToAvoidBottomInset,
          ),
        ],
      ),
    );
  }
}

class _GlowBlob extends StatelessWidget {
  const _GlowBlob({super.key, required this.color});
  final Color color;

  @override
  Widget build(BuildContext context) {
    return IgnorePointer(
      child: Container(
        width: 300,
        height: 300,
        decoration: BoxDecoration(
          shape: BoxShape.circle,
          gradient: RadialGradient(
            colors: <Color>[color, color.withValues(alpha: 0)],
          ),
        ),
      ),
    );
  }
}

/// Data for a single [GlassBottomNav] destination.
class GlassNavItem {
  const GlassNavItem({
    required this.icon,
    required this.activeIcon,
    required this.label,
  });

  final IconData icon;
  final IconData activeIcon;
  final String label;
}

/// A frosted bottom navigation bar with a pill-highlighted active tab.
class GlassBottomNav extends StatelessWidget {
  const GlassBottomNav({
    super.key,
    required this.items,
    required this.currentIndex,
    required this.onTap,
  });

  final List<GlassNavItem> items;
  final int currentIndex;
  final ValueChanged<int> onTap;

  @override
  Widget build(BuildContext context) {
    final AppPalette c = context.colors;
    return SafeArea(
      top: false,
      child: Padding(
        padding: const EdgeInsets.fromLTRB(14, 0, 14, 12),
        child: ClipRRect(
          borderRadius: BorderRadius.circular(AppRadii.xl),
          child: BackdropFilter(
            filter: ImageFilter.blur(sigmaX: 22, sigmaY: 22),
            child: Container(
              height: 66,
              decoration: BoxDecoration(
                color: c.surface.withValues(alpha: c.isDark ? 0.62 : 0.72),
                borderRadius: BorderRadius.circular(AppRadii.xl),
                border: Border.all(color: c.glassBorder, width: 1.1),
                boxShadow: <BoxShadow>[
                  BoxShadow(
                    color: c.isDark
                        ? Colors.black.withValues(alpha: 0.5)
                        : c.plum.withValues(alpha: 0.16),
                    blurRadius: 24,
                    offset: const Offset(0, 10),
                  ),
                ],
              ),
              child: Row(
                children: <Widget>[
                  for (int i = 0; i < items.length; i++)
                    Expanded(
                      child: _NavTab(
                        item: items[i],
                        selected: i == currentIndex,
                        onTap: () => onTap(i),
                      ),
                    ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}

class _NavTab extends StatelessWidget {
  const _NavTab({
    super.key,
    required this.item,
    required this.selected,
    required this.onTap,
  });

  final GlassNavItem item;
  final bool selected;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    final AppPalette c = context.colors;
    final Color activeColor = c.brand;
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(AppRadii.lg),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 220),
        curve: Curves.easeOut,
        margin: const EdgeInsets.symmetric(horizontal: 6, vertical: 10),
        decoration: BoxDecoration(
          color: selected ? c.brandSoft : Colors.transparent,
          borderRadius: BorderRadius.circular(AppRadii.md),
        ),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: <Widget>[
            Icon(
              selected ? item.activeIcon : item.icon,
              size: 22,
              color: selected ? activeColor : c.textMuted,
            ),
            const SizedBox(height: 3),
            Text(
              item.label,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              style: Theme.of(context).textTheme.labelSmall?.copyWith(
                    color: selected ? activeColor : c.textMuted,
                    fontWeight: selected ? FontWeight.w700 : FontWeight.w600,
                    fontSize: 10.5,
                  ),
            ),
          ],
        ),
      ),
    );
  }
}
