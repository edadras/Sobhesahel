import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:shared_preferences/shared_preferences.dart';

/// Bound to a concrete [SharedPreferences] instance in `main()` via
/// [ProviderScope.overrides]. Reading it before that override throws.
final sharedPreferencesProvider = Provider<SharedPreferences>((ref) {
  throw UnimplementedError(
    'sharedPreferencesProvider must be overridden in main().',
  );
});

// ---------------------------------------------------------------------------
// Theme mode (light / dark / system), persisted.
// ---------------------------------------------------------------------------

const String _themeModeKey = 'ss_theme_mode';

class ThemeModeNotifier extends StateNotifier<ThemeMode> {
  ThemeModeNotifier(this._prefs) : super(_read(_prefs));

  final SharedPreferences _prefs;

  static ThemeMode _read(SharedPreferences prefs) {
    switch (prefs.getString(_themeModeKey)) {
      case 'light':
        return ThemeMode.light;
      case 'dark':
        return ThemeMode.dark;
      default:
        return ThemeMode.system;
    }
  }

  Future<void> set(ThemeMode mode) async {
    state = mode;
    await _prefs.setString(_themeModeKey, mode.name);
  }

  /// Toggle between light and dark (treating system as light → dark).
  Future<void> toggle() async {
    await set(state == ThemeMode.dark ? ThemeMode.light : ThemeMode.dark);
  }
}

final themeModeProvider =
    StateNotifierProvider<ThemeModeNotifier, ThemeMode>((ref) {
  return ThemeModeNotifier(ref.watch(sharedPreferencesProvider));
});

// ---------------------------------------------------------------------------
// Locale (fa default / en), persisted. Drives Directionality.
// ---------------------------------------------------------------------------

const String _localeKey = 'ss_locale';

/// Locales the app ships translations for. `fa` is first → the default.
const List<Locale> kSupportedLocales = <Locale>[
  Locale('fa'),
  Locale('en'),
];

class LocaleNotifier extends StateNotifier<Locale> {
  LocaleNotifier(this._prefs) : super(_read(_prefs));

  final SharedPreferences _prefs;

  static Locale _read(SharedPreferences prefs) {
    final String? code = prefs.getString(_localeKey);
    if (code == 'en') return const Locale('en');
    return const Locale('fa');
  }

  Future<void> set(Locale locale) async {
    state = locale;
    await _prefs.setString(_localeKey, locale.languageCode);
  }

  Future<void> toggle() async {
    await set(state.languageCode == 'fa'
        ? const Locale('en')
        : const Locale('fa'));
  }
}

final localeProvider = StateNotifierProvider<LocaleNotifier, Locale>((ref) {
  return LocaleNotifier(ref.watch(sharedPreferencesProvider));
});
