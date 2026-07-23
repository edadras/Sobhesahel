import 'package:flutter/material.dart';

import 'app_colors.dart';

/// Radii used across the widget kit (mirrors the CSS `--r-*` tokens, nudged
/// up to the 20–24px range the brief asks for on cards).
class AppRadii {
  const AppRadii._();
  static const double sm = 12;
  static const double md = 16;
  static const double lg = 20;
  static const double xl = 24;
  static const double pill = 999;
}

/// Builds the light and dark [ThemeData] for the app. Vazirmatn is the default
/// font family; the site palette is attached as a [ThemeExtension] so widgets
/// can read `context.colors`.
class AppTheme {
  const AppTheme._();

  static const String fontFamily = 'Vazirmatn';

  static ThemeData light() => _build(AppPalette.light);
  static ThemeData dark() => _build(AppPalette.dark);

  static ThemeData _build(AppPalette p) {
    final ColorScheme scheme = ColorScheme(
      brightness: p.brightness,
      primary: p.brand,
      onPrimary: Colors.white,
      primaryContainer: p.brandSoft,
      onPrimaryContainer: p.brand,
      secondary: p.plum,
      onSecondary: Colors.white,
      secondaryContainer: p.plum.withValues(alpha: 0.18),
      onSecondaryContainer: p.plum,
      tertiary: p.gold,
      onTertiary: const Color(0xFF2A2208),
      tertiaryContainer: p.goldSoft,
      onTertiaryContainer: p.gold,
      error: p.accentRed,
      onError: Colors.white,
      errorContainer: p.accentRed.withValues(alpha: 0.14),
      onErrorContainer: p.accentRed,
      surface: p.surface,
      onSurface: p.text,
      surfaceContainerHighest: p.surface2,
      onSurfaceVariant: p.textMuted,
      outline: p.border,
      outlineVariant: p.borderStrong,
      shadow: Colors.black,
      scrim: Colors.black,
      inverseSurface: p.text,
      onInverseSurface: p.bg,
      inversePrimary: p.brandSoft,
      surfaceTint: Colors.transparent,
    );

    final TextTheme textTheme = _textTheme(p);

    return ThemeData(
      useMaterial3: true,
      brightness: p.brightness,
      fontFamily: fontFamily,
      colorScheme: scheme,
      scaffoldBackgroundColor: p.canvas,
      canvasColor: p.canvas,
      textTheme: textTheme,
      splashFactory: InkRipple.splashFactory,
      extensions: <ThemeExtension<dynamic>>[appPaletteExtension(p)],

      appBarTheme: AppBarTheme(
        backgroundColor: Colors.transparent,
        surfaceTintColor: Colors.transparent,
        elevation: 0,
        scrolledUnderElevation: 0,
        centerTitle: false,
        foregroundColor: p.text,
        titleTextStyle: textTheme.titleLarge,
        iconTheme: IconThemeData(color: p.text),
      ),

      dividerTheme: DividerThemeData(
        color: p.border,
        thickness: 1,
        space: 1,
      ),

      chipTheme: ChipThemeData(
        backgroundColor: p.surface2,
        side: BorderSide(color: p.border),
        labelStyle: textTheme.labelLarge,
        shape: const StadiumBorder(),
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
      ),

      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: p.isDark
            ? Colors.white.withValues(alpha: 0.04)
            : Colors.white.withValues(alpha: 0.65),
        hintStyle: TextStyle(color: p.textFaint),
        labelStyle: TextStyle(color: p.textMuted),
        contentPadding:
            const EdgeInsets.symmetric(horizontal: 16, vertical: 15),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(AppRadii.md),
          borderSide: BorderSide(color: p.borderStrong),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(AppRadii.md),
          borderSide: BorderSide(color: p.border),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(AppRadii.md),
          borderSide: BorderSide(color: p.brand, width: 1.6),
        ),
        errorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(AppRadii.md),
          borderSide: BorderSide(color: p.accentRed),
        ),
        focusedErrorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(AppRadii.md),
          borderSide: BorderSide(color: p.accentRed, width: 1.6),
        ),
      ),

      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ElevatedButton.styleFrom(
          backgroundColor: p.plum,
          foregroundColor: Colors.white,
          disabledBackgroundColor: p.borderStrong,
          disabledForegroundColor: p.textFaint,
          elevation: 0,
          minimumSize: const Size(0, 52),
          padding: const EdgeInsets.symmetric(horizontal: 22),
          textStyle: textTheme.labelLarge?.copyWith(
            fontWeight: FontWeight.w700,
            fontSize: 15,
          ),
          shape: const StadiumBorder(),
        ),
      ),

      outlinedButtonTheme: OutlinedButtonThemeData(
        style: OutlinedButton.styleFrom(
          foregroundColor: p.text,
          side: BorderSide(color: p.borderStrong),
          minimumSize: const Size(0, 52),
          padding: const EdgeInsets.symmetric(horizontal: 22),
          textStyle: textTheme.labelLarge?.copyWith(fontWeight: FontWeight.w700),
          shape: const StadiumBorder(),
        ),
      ),

      textButtonTheme: TextButtonThemeData(
        style: TextButton.styleFrom(
          foregroundColor: p.brand,
          textStyle: textTheme.labelLarge?.copyWith(fontWeight: FontWeight.w700),
        ),
      ),

      switchTheme: SwitchThemeData(
        thumbColor: WidgetStateProperty.resolveWith(
          (states) => states.contains(WidgetState.selected)
              ? Colors.white
              : p.isDark
                  ? p.textMuted
                  : Colors.white,
        ),
        trackColor: WidgetStateProperty.resolveWith(
          (states) => states.contains(WidgetState.selected)
              ? p.brand
              : p.borderStrong,
        ),
        trackOutlineColor:
            WidgetStateProperty.all(Colors.transparent),
      ),

      snackBarTheme: SnackBarThemeData(
        behavior: SnackBarBehavior.floating,
        backgroundColor: p.text,
        contentTextStyle: TextStyle(
          color: p.bg,
          fontFamily: fontFamily,
          fontWeight: FontWeight.w600,
        ),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(AppRadii.md),
        ),
      ),

      progressIndicatorTheme: ProgressIndicatorThemeData(
        color: p.brand,
        linearTrackColor: p.surface2,
        circularTrackColor: p.surface2,
      ),

      bottomSheetTheme: BottomSheetThemeData(
        backgroundColor: p.surface,
        surfaceTintColor: Colors.transparent,
        shape: const RoundedRectangleBorder(
          borderRadius: BorderRadius.vertical(top: Radius.circular(AppRadii.xl)),
        ),
      ),

      iconTheme: IconThemeData(color: p.textMuted),
    );
  }

  static TextTheme _textTheme(AppPalette p) {
    const String f = fontFamily;
    TextStyle base(double size, FontWeight weight,
            {Color? color, double? height}) =>
        TextStyle(
          fontFamily: f,
          fontSize: size,
          fontWeight: weight,
          color: color ?? p.text,
          height: height,
        );

    return TextTheme(
      displayLarge: base(40, FontWeight.w900, height: 1.1),
      displayMedium: base(32, FontWeight.w800, height: 1.15),
      displaySmall: base(28, FontWeight.w800, height: 1.2),
      headlineLarge: base(26, FontWeight.w800, height: 1.25),
      headlineMedium: base(23, FontWeight.w700, height: 1.3),
      headlineSmall: base(20, FontWeight.w700, height: 1.3),
      titleLarge: base(18, FontWeight.w700, height: 1.35),
      titleMedium: base(16, FontWeight.w700, height: 1.4),
      titleSmall: base(14.5, FontWeight.w600, height: 1.4),
      bodyLarge: base(15.5, FontWeight.w500, color: p.text, height: 1.6),
      bodyMedium: base(14, FontWeight.w500, color: p.textMuted, height: 1.6),
      bodySmall: base(12.5, FontWeight.w500, color: p.textFaint, height: 1.5),
      labelLarge: base(14.5, FontWeight.w600),
      labelMedium: base(13, FontWeight.w600, color: p.textMuted),
      labelSmall: base(11.5, FontWeight.w600, color: p.textFaint),
    );
  }
}
