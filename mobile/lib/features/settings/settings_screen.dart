import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../core/providers.dart';
import '../../data/models/models.dart';
import '../../data/providers.dart';
import '../../l10n/app_localizations.dart';
import '../../theme/app_colors.dart';
import '../../theme/app_theme.dart';
import '../../theme/glass.dart';
import '../../widgets/common.dart';
import '../auth/auth_controller.dart';

/// Account settings: profile edit, security (password), appearance (theme +
/// language) and logout. Bound to [SettingsRepository] and the theme/locale
/// providers.
class SettingsScreen extends ConsumerStatefulWidget {
  const SettingsScreen({super.key});

  @override
  ConsumerState<SettingsScreen> createState() => _SettingsScreenState();
}

class _SettingsScreenState extends ConsumerState<SettingsScreen> {
  final TextEditingController _first = TextEditingController();
  final TextEditingController _last = TextEditingController();
  final TextEditingController _email = TextEditingController();
  final TextEditingController _city = TextEditingController();
  final TextEditingController _birth = TextEditingController();
  final TextEditingController _bio = TextEditingController();

  final TextEditingController _currentPw = TextEditingController();
  final TextEditingController _newPw = TextEditingController();
  final TextEditingController _confirmPw = TextEditingController();

  bool _savingProfile = false;
  bool _savingPassword = false;
  bool _prefilled = false;

  @override
  void dispose() {
    _first.dispose();
    _last.dispose();
    _email.dispose();
    _city.dispose();
    _birth.dispose();
    _bio.dispose();
    _currentPw.dispose();
    _newPw.dispose();
    _confirmPw.dispose();
    super.dispose();
  }

  void _prefill(Member m) {
    if (_prefilled) return;
    _first.text = m.firstName;
    _last.text = m.lastName;
    _email.text = m.email ?? '';
    _city.text = m.city ?? '';
    _birth.text = m.birthDate ?? '';
    _bio.text = m.bio ?? '';
    _prefilled = true;
  }

  void _snack(String message) {
    ScaffoldMessenger.of(context)
      ..hideCurrentSnackBar()
      ..showSnackBar(SnackBar(content: Text(message)));
  }

  Future<void> _saveProfile() async {
    final AppLocalizations t = AppLocalizations.of(context);
    setState(() => _savingProfile = true);
    try {
      final Member updated =
          await ref.read(settingsRepositoryProvider).updateProfile(
                firstName: _first.text.trim(),
                lastName: _last.text.trim(),
                city: _city.text.trim(),
                birthDate: _birth.text.trim(),
                bio: _bio.text.trim(),
                email: _email.text.trim(),
              );
      ref.read(authControllerProvider.notifier).setMember(updated);
      if (mounted) _snack(t.commonSavedSuccessfully);
    } catch (e) {
      if (mounted) _snack(t.commonError);
    } finally {
      if (mounted) setState(() => _savingProfile = false);
    }
  }

  Future<void> _savePassword() async {
    final AppLocalizations t = AppLocalizations.of(context);
    if (_newPw.text.length < 8) {
      _snack(t.settingsPasswordTooShort);
      return;
    }
    if (_newPw.text != _confirmPw.text) {
      _snack(t.settingsPasswordsDontMatch);
      return;
    }
    setState(() => _savingPassword = true);
    try {
      await ref.read(settingsRepositoryProvider).updatePassword(
            currentPassword:
                _currentPw.text.isEmpty ? null : _currentPw.text,
            password: _newPw.text,
            passwordConfirmation: _confirmPw.text,
          );
      _currentPw.clear();
      _newPw.clear();
      _confirmPw.clear();
      if (mounted) _snack(t.settingsPasswordChanged);
    } catch (e) {
      if (mounted) _snack(t.commonError);
    } finally {
      if (mounted) setState(() => _savingPassword = false);
    }
  }

  Future<void> _confirmLogout() async {
    final AppLocalizations t = AppLocalizations.of(context);
    final bool? ok = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: Text(t.settingsLogoutConfirmTitle),
        content: Text(t.settingsLogoutConfirmMessage),
        actions: <Widget>[
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: Text(t.commonCancel),
          ),
          TextButton(
            onPressed: () => Navigator.pop(ctx, true),
            child: Text(t.commonLogout),
          ),
        ],
      ),
    );
    if (ok == true) {
      await ref.read(authControllerProvider.notifier).logout();
    }
  }

  @override
  Widget build(BuildContext context) {
    final AppLocalizations t = AppLocalizations.of(context);
    final AppPalette c = context.colors;
    final Member? member = ref.watch(authControllerProvider).member;
    if (member != null) _prefill(member);

    return GlassScaffold(
      appBar: GlassAppBar(
        title: t.settingsTitle,
        subtitle: t.settingsSubtitle,
        automaticallyImplyLeading: false,
      ),
      body: ListView(
        padding: const EdgeInsets.fromLTRB(16, 8, 16, 104),
        children: <Widget>[
          _profilePhoto(t, c, member),
          const SizedBox(height: 16),
          _personalInfo(t),
          const SizedBox(height: 16),
          _appearance(t, c),
          const SizedBox(height: 16),
          _security(t),
          const SizedBox(height: 16),
          _account(t, c),
          const SizedBox(height: 16),
          _dangerZone(t, c),
        ],
      ),
    );
  }

  Widget _sectionCard(String title, List<Widget> children) {
    return GlassCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: <Widget>[
          Text(title, style: Theme.of(context).textTheme.titleMedium),
          const SizedBox(height: 14),
          ...children,
        ],
      ),
    );
  }

  Widget _field(String label, TextEditingController controller,
      {TextInputType? keyboard,
      int maxLines = 1,
      TextDirection? direction}) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 14),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: <Widget>[
          Text(label, style: Theme.of(context).textTheme.titleSmall),
          const SizedBox(height: 7),
          TextField(
            controller: controller,
            keyboardType: keyboard,
            maxLines: maxLines,
            textDirection: direction,
            textAlign: direction == TextDirection.ltr
                ? TextAlign.right
                : TextAlign.start,
          ),
        ],
      ),
    );
  }

  Widget _profilePhoto(AppLocalizations t, AppPalette c, Member? member) {
    return _sectionCard(t.settingsProfilePhoto, <Widget>[
      Row(
        children: <Widget>[
          Container(
            width: 72,
            height: 72,
            decoration: BoxDecoration(
              gradient: c.brandGradient,
              shape: BoxShape.circle,
            ),
            alignment: Alignment.center,
            child: Text(
              member?.initials ?? '؟',
              style: const TextStyle(
                color: Colors.white,
                fontSize: 26,
                fontWeight: FontWeight.w700,
                fontFamily: AppTheme.fontFamily,
              ),
            ),
          ),
          const SizedBox(width: 16),
          Expanded(
            child: Wrap(
              spacing: 8,
              runSpacing: 8,
              children: <Widget>[
                ElevatedButton(
                  onPressed: () => _snack(t.commonComingSoon),
                  child: Text(t.settingsChangePhoto),
                ),
                OutlinedButton(
                  onPressed: () => _snack(t.commonComingSoon),
                  child: Text(t.settingsRemovePhoto),
                ),
              ],
            ),
          ),
        ],
      ),
    ]);
  }

  Widget _personalInfo(AppLocalizations t) {
    return _sectionCard(t.settingsPersonalInfo, <Widget>[
      Row(
        children: <Widget>[
          Expanded(child: _field(t.settingsFirstName, _first)),
          const SizedBox(width: 12),
          Expanded(child: _field(t.settingsLastName, _last)),
        ],
      ),
      _field(t.settingsEmail, _email,
          keyboard: TextInputType.emailAddress,
          direction: TextDirection.ltr),
      _field(t.settingsCity, _city),
      _field(t.settingsBirthDate, _birth, direction: TextDirection.ltr),
      _field(t.settingsBio, _bio, maxLines: 3),
      GradientButton(
        label: t.settingsSaveChanges,
        loading: _savingProfile,
        onPressed: _savingProfile ? null : _saveProfile,
      ),
    ]);
  }

  Widget _appearance(AppLocalizations t, AppPalette c) {
    final ThemeMode mode = ref.watch(themeModeProvider);
    final Locale locale = ref.watch(localeProvider);
    return _sectionCard(t.settingsAppearance, <Widget>[
      Text(t.settingsDisplayMode,
          style: Theme.of(context).textTheme.titleSmall),
      const SizedBox(height: 10),
      Row(
        children: <Widget>[
          _themeChip(t.settingsThemeLight, Icons.light_mode_rounded,
              ThemeMode.light, mode, c),
          const SizedBox(width: 8),
          _themeChip(t.settingsThemeDark, Icons.dark_mode_rounded,
              ThemeMode.dark, mode, c),
          const SizedBox(width: 8),
          _themeChip(t.settingsThemeSystem, Icons.smartphone_rounded,
              ThemeMode.system, mode, c),
        ],
      ),
      const Divider(height: 28),
      Text(t.settingsLanguage,
          style: Theme.of(context).textTheme.titleSmall),
      const SizedBox(height: 10),
      Row(
        children: <Widget>[
          _langChip('فارسی', 'fa', locale, c),
          const SizedBox(width: 8),
          _langChip('English', 'en', locale, c),
        ],
      ),
    ]);
  }

  Widget _themeChip(String label, IconData icon, ThemeMode value,
      ThemeMode current, AppPalette c) {
    final bool sel = value == current;
    return Expanded(
      child: GestureDetector(
        onTap: () => ref.read(themeModeProvider.notifier).set(value),
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 14),
          decoration: BoxDecoration(
            color: sel ? c.brandSoft : Colors.transparent,
            borderRadius: BorderRadius.circular(AppRadii.md),
            border: Border.all(
                color: sel ? c.brand : c.border, width: sel ? 1.6 : 1),
          ),
          child: Column(
            children: <Widget>[
              Icon(icon, color: sel ? c.brand : c.textMuted, size: 22),
              const SizedBox(height: 6),
              Text(
                label,
                style: Theme.of(context).textTheme.labelMedium?.copyWith(
                      color: sel ? c.brand : c.textMuted,
                      fontWeight: FontWeight.w700,
                    ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _langChip(String label, String code, Locale current, AppPalette c) {
    final bool sel = current.languageCode == code;
    return Expanded(
      child: GestureDetector(
        onTap: () => ref.read(localeProvider.notifier).set(Locale(code)),
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 14),
          alignment: Alignment.center,
          decoration: BoxDecoration(
            color: sel ? c.brandSoft : Colors.transparent,
            borderRadius: BorderRadius.circular(AppRadii.md),
            border: Border.all(
                color: sel ? c.brand : c.border, width: sel ? 1.6 : 1),
          ),
          child: Text(
            label,
            style: Theme.of(context).textTheme.titleSmall?.copyWith(
                  color: sel ? c.brand : c.textMuted,
                  fontWeight: FontWeight.w700,
                ),
          ),
        ),
      ),
    );
  }

  Widget _security(AppLocalizations t) {
    return _sectionCard(t.settingsChangePassword, <Widget>[
      _field(t.settingsCurrentPassword, _currentPw,
          direction: TextDirection.ltr),
      _field(t.settingsNewPassword, _newPw, direction: TextDirection.ltr),
      _field(t.settingsConfirmPassword, _confirmPw,
          direction: TextDirection.ltr),
      GradientButton(
        label: t.settingsUpdatePassword,
        loading: _savingPassword,
        onPressed: _savingPassword ? null : _savePassword,
      ),
    ]);
  }

  Widget _account(AppLocalizations t, AppPalette c) {
    return GlassCard(
      onTap: _confirmLogout,
      child: Row(
        children: <Widget>[
          IconChip(icon: Icons.logout_rounded, color: c.brand, size: 40),
          const SizedBox(width: 14),
          Expanded(
            child: Text(t.commonLogout,
                style: Theme.of(context).textTheme.titleMedium),
          ),
          Icon(Icons.chevron_left_rounded, color: c.textFaint),
        ],
      ),
    );
  }

  Widget _dangerZone(AppLocalizations t, AppPalette c) {
    return GlassCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: <Widget>[
          Text(
            t.settingsDangerZone,
            style: Theme.of(context)
                .textTheme
                .titleMedium
                ?.copyWith(color: c.accentRed),
          ),
          const SizedBox(height: 8),
          Text(t.settingsDeleteAccountDesc,
              style: Theme.of(context).textTheme.bodySmall),
          const SizedBox(height: 14),
          OutlinedButton.icon(
            onPressed: () => _snack(t.commonComingSoon),
            style: OutlinedButton.styleFrom(
              foregroundColor: c.accentRed,
              side: BorderSide(color: c.accentRed.withValues(alpha: 0.5)),
            ),
            icon: const Icon(Icons.delete_outline_rounded, size: 18),
            label: Text(t.settingsDeleteAccount),
          ),
        ],
      ),
    );
  }
}
