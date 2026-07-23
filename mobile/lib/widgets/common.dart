import 'dart:math' as math;

import 'package:flutter/material.dart';

import '../l10n/app_localizations.dart';
import '../theme/app_colors.dart';
import '../theme/app_theme.dart';

/// A pill-shaped button filled with the brand gradient. The primary CTA style
/// across the app (mirrors `.btn-plum` / `.btn-brand`).
class GradientButton extends StatelessWidget {
  const GradientButton({
    super.key,
    required this.label,
    this.onPressed,
    this.icon,
    this.expand = true,
    this.loading = false,
    this.height = 52,
  });

  final String label;
  final VoidCallback? onPressed;
  final IconData? icon;
  final bool expand;
  final bool loading;
  final double height;

  @override
  Widget build(BuildContext context) {
    final AppPalette c = context.colors;
    final bool enabled = onPressed != null && !loading;
    final Widget content = loading
        ? const SizedBox(
            width: 22,
            height: 22,
            child: CircularProgressIndicator(
              strokeWidth: 2.4,
              valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
            ),
          )
        : Row(
            mainAxisSize: MainAxisSize.min,
            mainAxisAlignment: MainAxisAlignment.center,
            children: <Widget>[
              if (icon != null) ...<Widget>[
                Icon(icon, size: 19, color: Colors.white),
                const SizedBox(width: 8),
              ],
              Flexible(
                child: Text(
                  label,
                  overflow: TextOverflow.ellipsis,
                  style: const TextStyle(
                    color: Colors.white,
                    fontWeight: FontWeight.w700,
                    fontSize: 15,
                    fontFamily: AppTheme.fontFamily,
                  ),
                ),
              ),
            ],
          );

    return Opacity(
      opacity: enabled ? 1 : 0.55,
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          borderRadius: BorderRadius.circular(AppRadii.pill),
          onTap: enabled ? onPressed : null,
          child: Ink(
            height: height,
            width: expand ? double.infinity : null,
            decoration: BoxDecoration(
              gradient: c.brandGradient,
              borderRadius: BorderRadius.circular(AppRadii.pill),
              boxShadow: <BoxShadow>[
                BoxShadow(
                  color: c.brand.withValues(alpha: 0.32),
                  blurRadius: 18,
                  offset: const Offset(0, 8),
                ),
              ],
            ),
            child: Padding(
              padding: const EdgeInsets.symmetric(horizontal: 22),
              child: Center(child: content),
            ),
          ),
        ),
      ),
    );
  }
}

/// Section header with the site's signature accent bar (`.sec-title::before`).
class SectionTitle extends StatelessWidget {
  const SectionTitle(this.title, {super.key, this.trailing});

  final String title;
  final Widget? trailing;

  @override
  Widget build(BuildContext context) {
    final AppPalette c = context.colors;
    return Padding(
      padding: const EdgeInsets.only(bottom: 14),
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
            child: Text(
              title,
              style: Theme.of(context).textTheme.titleLarge,
            ),
          ),
          if (trailing != null) trailing!,
        ],
      ),
    );
  }
}

/// A small rounded icon chip, used inside stat tiles and list rows.
class IconChip extends StatelessWidget {
  const IconChip({
    super.key,
    required this.icon,
    required this.color,
    this.size = 40,
    this.radius = 12,
  });

  final IconData icon;
  final Color color;
  final double size;
  final double radius;

  @override
  Widget build(BuildContext context) {
    return Container(
      width: size,
      height: size,
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.16),
        borderRadius: BorderRadius.circular(radius),
      ),
      child: Icon(icon, color: color, size: size * 0.5),
    );
  }
}

/// A coloured status pill (`.pill`).
class StatusPill extends StatelessWidget {
  const StatusPill(
    this.label, {
    super.key,
    required this.color,
    this.icon,
  });

  final String label;
  final Color color;
  final IconData? icon;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 11, vertical: 4),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.15),
        borderRadius: BorderRadius.circular(AppRadii.pill),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: <Widget>[
          if (icon != null) ...<Widget>[
            Icon(icon, size: 13, color: color),
            const SizedBox(width: 4),
          ],
          Text(
            label,
            style: TextStyle(
              color: color,
              fontWeight: FontWeight.w700,
              fontSize: 11.5,
              fontFamily: AppTheme.fontFamily,
            ),
          ),
        ],
      ),
    );
  }
}

/// Circular progress ring with a centred value, matching the dashboard `.ring`.
class ProgressRing extends StatelessWidget {
  const ProgressRing({
    super.key,
    required this.percent,
    required this.center,
    this.size = 96,
    this.stroke = 9,
    this.color,
  });

  /// 0..100.
  final double percent;
  final Widget center;
  final double size;
  final double stroke;
  final Color? color;

  @override
  Widget build(BuildContext context) {
    final AppPalette c = context.colors;
    return SizedBox(
      width: size,
      height: size,
      child: Stack(
        alignment: Alignment.center,
        children: <Widget>[
          CustomPaint(
            size: Size.square(size),
            painter: _RingPainter(
              percent: percent.clamp(0, 100) / 100,
              track: c.surface2,
              progress: color ?? c.brand,
              stroke: stroke,
            ),
          ),
          center,
        ],
      ),
    );
  }
}

class _RingPainter extends CustomPainter {
  _RingPainter({
    required this.percent,
    required this.track,
    required this.progress,
    required this.stroke,
  });

  final double percent;
  final Color track;
  final Color progress;
  final double stroke;

  @override
  void paint(Canvas canvas, Size size) {
    final Offset center = size.center(Offset.zero);
    final double radius = (size.width - stroke) / 2;
    final Paint trackPaint = Paint()
      ..color = track
      ..style = PaintingStyle.stroke
      ..strokeWidth = stroke;
    final Paint progressPaint = Paint()
      ..color = progress
      ..style = PaintingStyle.stroke
      ..strokeCap = StrokeCap.round
      ..strokeWidth = stroke;

    canvas.drawCircle(center, radius, trackPaint);
    canvas.drawArc(
      Rect.fromCircle(center: center, radius: radius),
      -math.pi / 2,
      2 * math.pi * percent,
      false,
      progressPaint,
    );
  }

  @override
  bool shouldRepaint(covariant _RingPainter old) =>
      old.percent != percent ||
      old.progress != progress ||
      old.track != track;
}

/// A thin rounded progress bar (`.progress`).
class GradientProgressBar extends StatelessWidget {
  const GradientProgressBar({super.key, required this.percent, this.height = 8});

  /// 0..100.
  final double percent;
  final double height;

  @override
  Widget build(BuildContext context) {
    final AppPalette c = context.colors;
    return ClipRRect(
      borderRadius: BorderRadius.circular(AppRadii.pill),
      child: Stack(
        children: <Widget>[
          Container(height: height, color: c.surface2),
          FractionallySizedBox(
            widthFactor: (percent.clamp(0, 100)) / 100,
            child: Container(
              height: height,
              decoration: BoxDecoration(gradient: c.brandGradient),
            ),
          ),
        ],
      ),
    );
  }
}

/// Centred empty-state widget.
class EmptyState extends StatelessWidget {
  const EmptyState({super.key, this.message, this.icon = Icons.inbox_outlined});

  final String? message;
  final IconData icon;

  @override
  Widget build(BuildContext context) {
    final AppPalette c = context.colors;
    return Center(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: <Widget>[
          Icon(icon, size: 54, color: c.textFaint),
          const SizedBox(height: 14),
          Text(
            message ?? AppLocalizations.of(context).commonEmpty,
            style: Theme.of(context).textTheme.bodyMedium,
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }
}

/// Centred error state with a retry button.
class ErrorRetry extends StatelessWidget {
  const ErrorRetry({super.key, this.message, this.onRetry});

  final String? message;
  final VoidCallback? onRetry;

  @override
  Widget build(BuildContext context) {
    final AppPalette c = context.colors;
    final AppLocalizations t = AppLocalizations.of(context);
    return Center(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: <Widget>[
          Icon(Icons.error_outline_rounded, size: 54, color: c.accentRed),
          const SizedBox(height: 12),
          Text(
            message ?? t.commonError,
            style: Theme.of(context).textTheme.bodyMedium,
            textAlign: TextAlign.center,
          ),
          if (onRetry != null) ...<Widget>[
            const SizedBox(height: 16),
            OutlinedButton.icon(
              onPressed: onRetry,
              icon: const Icon(Icons.refresh_rounded, size: 18),
              label: Text(t.commonRetry),
            ),
          ],
        ],
      ),
    );
  }
}

/// Simple centred loading spinner.
class LoadingView extends StatelessWidget {
  const LoadingView({super.key});

  @override
  Widget build(BuildContext context) {
    return const Center(child: CircularProgressIndicator());
  }
}

/// Placeholder body used by stub feature screens until a feature agent fills
/// them in. Shows the localized page name and a friendly "coming soon" note.
class ComingSoonPlaceholder extends StatelessWidget {
  const ComingSoonPlaceholder({
    super.key,
    required this.title,
    this.icon = Icons.auto_awesome_rounded,
  });

  final String title;
  final IconData icon;

  @override
  Widget build(BuildContext context) {
    final AppPalette c = context.colors;
    final AppLocalizations t = AppLocalizations.of(context);
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(28),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: <Widget>[
            Container(
              width: 88,
              height: 88,
              decoration: BoxDecoration(
                gradient: c.brandGradient,
                shape: BoxShape.circle,
                boxShadow: <BoxShadow>[
                  BoxShadow(
                    color: c.brand.withValues(alpha: 0.35),
                    blurRadius: 24,
                    offset: const Offset(0, 12),
                  ),
                ],
              ),
              child: Icon(icon, size: 40, color: Colors.white),
            ),
            const SizedBox(height: 20),
            Text(title, style: Theme.of(context).textTheme.headlineSmall),
            const SizedBox(height: 8),
            Text(
              t.commonComingSoon,
              style: Theme.of(context)
                  .textTheme
                  .bodyMedium
                  ?.copyWith(color: c.textMuted),
            ),
          ],
        ),
      ),
    );
  }
}
