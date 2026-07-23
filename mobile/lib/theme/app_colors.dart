import 'package:flutter/material.dart';

/// Design tokens lifted directly from the site's `styles.css` (`:root` for
/// light, `[data-theme="dark"]` for dark). Kept as a single immutable palette
/// object so both [ThemeData]s and the glass widget kit read from one source.
@immutable
class AppPalette {
  const AppPalette({
    required this.brand,
    required this.brandDeep,
    required this.brandSoft,
    required this.plum,
    required this.plumDeep,
    required this.accentRed,
    required this.gold,
    required this.goldSoft,
    required this.bg,
    required this.canvas,
    required this.surface,
    required this.surface2,
    required this.headerBg,
    required this.text,
    required this.textMuted,
    required this.textFaint,
    required this.border,
    required this.borderStrong,
    required this.tierBronze,
    required this.tierSilver,
    required this.tierGold,
    required this.tierPlatinum,
    required this.success,
    required this.brightness,
  });

  final Color brand;
  final Color brandDeep;
  final Color brandSoft;
  final Color plum;
  final Color plumDeep;
  final Color accentRed;
  final Color gold;
  final Color goldSoft;

  final Color bg;
  final Color canvas;
  final Color surface;
  final Color surface2;
  final Color headerBg;

  final Color text;
  final Color textMuted;
  final Color textFaint;
  final Color border;
  final Color borderStrong;

  final Color tierBronze;
  final Color tierSilver;
  final Color tierGold;
  final Color tierPlatinum;

  final Color success;
  final Brightness brightness;

  bool get isDark => brightness == Brightness.dark;

  /// The signature plum → brand gradient used on hero cards and primary CTAs.
  LinearGradient get brandGradient => LinearGradient(
        begin: Alignment.topRight,
        end: Alignment.bottomLeft,
        colors: <Color>[plum, brand],
      );

  /// Deeper variant used on the login brand panel.
  LinearGradient get brandGradientDeep => LinearGradient(
        begin: Alignment.topRight,
        end: Alignment.bottomLeft,
        colors: <Color>[plumDeep, brandDeep],
      );

  /// Full-screen scaffold background gradient, differing per brightness.
  LinearGradient get scaffoldGradient => isDark
      ? const LinearGradient(
          begin: Alignment.topCenter,
          end: Alignment.bottomCenter,
          colors: <Color>[Color(0xFF15121C), Color(0xFF0E0C13)],
        )
      : const LinearGradient(
          begin: Alignment.topCenter,
          end: Alignment.bottomCenter,
          colors: <Color>[Color(0xFFF7F4F6), Color(0xFFEDEBEF)],
        );

  /// Translucent fill for glass surfaces.
  Color get glassFill => isDark
      ? Colors.white.withValues(alpha: 0.06)
      : Colors.white.withValues(alpha: 0.55);

  /// Secondary translucent fill (slightly stronger).
  Color get glassFillStrong => isDark
      ? Colors.white.withValues(alpha: 0.10)
      : Colors.white.withValues(alpha: 0.72);

  /// Hairline gradient border for glass surfaces.
  Color get glassBorder => isDark
      ? Colors.white.withValues(alpha: 0.14)
      : Colors.white.withValues(alpha: 0.85);

  Color get glassBorderFaint => isDark
      ? Colors.white.withValues(alpha: 0.06)
      : Colors.black.withValues(alpha: 0.05);

  static const AppPalette light = AppPalette(
    brand: Color(0xFFB81D47),
    brandDeep: Color(0xFF8E1638),
    brandSoft: Color(0xFFFBE7EE),
    plum: Color(0xFF6D4C5E),
    plumDeep: Color(0xFF553A49),
    accentRed: Color(0xFFD92632),
    gold: Color(0xFFC79A32),
    goldSoft: Color(0xFFF7ECCB),
    bg: Color(0xFFFFFFFF),
    canvas: Color(0xFFF3F3F4),
    surface: Color(0xFFFFFFFF),
    surface2: Color(0xFFF6F6F7),
    headerBg: Color(0xFFF1F1F2),
    text: Color(0xFF1E1E24),
    textMuted: Color(0xFF6C6C77),
    textFaint: Color(0xFF9A9AA4),
    border: Color(0xFFE4E4E8),
    borderStrong: Color(0xFFD3D3DA),
    tierBronze: Color(0xFFB06B3A),
    tierSilver: Color(0xFF8F97A3),
    tierGold: Color(0xFFC79A32),
    tierPlatinum: Color(0xFF4F9BAD),
    success: Color(0xFF1F9D57),
    brightness: Brightness.light,
  );

  static const AppPalette dark = AppPalette(
    brand: Color(0xFFEF5F87),
    brandDeep: Color(0xFFCF486F),
    brandSoft: Color(0xFF3A1F2A),
    plum: Color(0xFFB388A0),
    plumDeep: Color(0xFF8D6178),
    accentRed: Color(0xFFF0606A),
    gold: Color(0xFFE3BD5B),
    goldSoft: Color(0xFF3A3320),
    bg: Color(0xFF16131B),
    canvas: Color(0xFF110F16),
    surface: Color(0xFF1E1A25),
    surface2: Color(0xFF262030),
    headerBg: Color(0xFF1A1722),
    text: Color(0xFFEFECF3),
    textMuted: Color(0xFFA79FB2),
    textFaint: Color(0xFF756D80),
    border: Color(0xFF2C2736),
    borderStrong: Color(0xFF3A3346),
    tierBronze: Color(0xFFCD884F),
    tierSilver: Color(0xFFAEB6C2),
    tierGold: Color(0xFFE3BD5B),
    tierPlatinum: Color(0xFF69BDD0),
    success: Color(0xFF46C483),
    brightness: Brightness.dark,
  );
}

/// Makes the active [AppPalette] reachable from any widget via
/// `Theme.of(context).extension<AppPalette>()` — exposed through the
/// [AppColorsX] extension below for brevity.
extension AppColorsX on BuildContext {
  AppPalette get colors =>
      Theme.of(this).extension<_AppPaletteExtension>()?.palette ??
      AppPalette.light;
}

/// [ThemeExtension] wrapper so the immutable [AppPalette] can live inside
/// [ThemeData.extensions] and animate/lerp with theme transitions.
@immutable
class _AppPaletteExtension extends ThemeExtension<_AppPaletteExtension> {
  const _AppPaletteExtension(this.palette);

  final AppPalette palette;

  @override
  ThemeExtension<_AppPaletteExtension> copyWith() => this;

  @override
  ThemeExtension<_AppPaletteExtension> lerp(
    covariant ThemeExtension<_AppPaletteExtension>? other,
    double t,
  ) {
    // Palettes are discrete (light/dark); no interpolation needed.
    return t < 0.5 ? this : (other ?? this);
  }
}

/// Internal helper used by [AppTheme] to attach a palette to a [ThemeData].
ThemeExtension<dynamic> appPaletteExtension(AppPalette palette) =>
    _AppPaletteExtension(palette);
