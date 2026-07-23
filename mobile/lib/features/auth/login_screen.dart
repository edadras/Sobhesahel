import 'dart:async';

import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../config.dart';
import '../../core/persian.dart';
import '../../core/providers.dart';
import '../../l10n/app_localizations.dart';
import '../../theme/app_colors.dart';
import '../../theme/app_theme.dart';
import '../../theme/glass.dart';
import '../../widgets/common.dart';
import 'auth_controller.dart';

enum _AuthTab { phone, email }

/// Full login/registration screen. OTP-first with an email+password tab,
/// matching login.html: glass card, brand hero, disabled "coming soon" socials.
class LoginScreen extends ConsumerStatefulWidget {
  const LoginScreen({super.key});

  @override
  ConsumerState<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends ConsumerState<LoginScreen> {
  _AuthTab _tab = _AuthTab.phone;
  bool _otpSent = false;
  bool _busy = false;

  final TextEditingController _mobile = TextEditingController();
  final TextEditingController _email = TextEditingController();
  final TextEditingController _password = TextEditingController();

  late final List<TextEditingController> _codeCtrls =
      List<TextEditingController>.generate(
          AppConfig.otpLength, (_) => TextEditingController());
  late final List<FocusNode> _codeNodes =
      List<FocusNode>.generate(AppConfig.otpLength, (_) => FocusNode());

  Timer? _timer;
  int _remaining = 0;

  @override
  void dispose() {
    _timer?.cancel();
    _mobile.dispose();
    _email.dispose();
    _password.dispose();
    for (final TextEditingController c in _codeCtrls) {
      c.dispose();
    }
    for (final FocusNode n in _codeNodes) {
      n.dispose();
    }
    super.dispose();
  }

  String get _code => _codeCtrls.map((c) => c.text).join();

  void _startTimer() {
    _timer?.cancel();
    setState(() => _remaining = AppConfig.otpResendSeconds);
    _timer = Timer.periodic(const Duration(seconds: 1), (t) {
      if (_remaining <= 1) {
        t.cancel();
        setState(() => _remaining = 0);
      } else {
        setState(() => _remaining--);
      }
    });
  }

  String get _formattedRemaining {
    final int m = _remaining ~/ 60;
    final int s = _remaining % 60;
    return '${m.toString().padLeft(2, '0')}:${s.toString().padLeft(2, '0')}';
  }

  void _snack(String message) {
    ScaffoldMessenger.of(context)
      ..hideCurrentSnackBar()
      ..showSnackBar(SnackBar(content: Text(message)));
  }

  Future<void> _sendOtp() async {
    final AppLocalizations t = AppLocalizations.of(context);
    if (_mobile.text.trim().isEmpty) {
      _snack(t.commonInvalidMobile);
      return;
    }
    setState(() => _busy = true);
    try {
      await ref.read(authControllerProvider.notifier).requestOtp(_mobile.text.trim());
      if (!mounted) return;
      setState(() => _otpSent = true);
      _startTimer();
      _snack(t.authCodeSentToast);
      _codeNodes.first.requestFocus();
    } catch (e) {
      if (mounted) _snack(t.commonError);
    } finally {
      if (mounted) setState(() => _busy = false);
    }
  }

  Future<void> _verify() async {
    final AppLocalizations t = AppLocalizations.of(context);
    if (_code.length < AppConfig.otpLength) {
      _snack(t.authInvalidCode);
      return;
    }
    setState(() => _busy = true);
    try {
      await ref
          .read(authControllerProvider.notifier)
          .verifyOtp(mobile: _mobile.text.trim(), code: _code);
      // Router redirect handles navigation once authenticated.
    } catch (e) {
      if (mounted) _snack(t.authInvalidCode);
    } finally {
      if (mounted) setState(() => _busy = false);
    }
  }

  Future<void> _passwordLogin() async {
    final AppLocalizations t = AppLocalizations.of(context);
    setState(() => _busy = true);
    try {
      await ref.read(authControllerProvider.notifier).passwordLogin(
            email: _email.text.trim(),
            password: _password.text,
          );
    } catch (e) {
      if (mounted) _snack(t.authInvalidCredentials);
    } finally {
      if (mounted) setState(() => _busy = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final AppLocalizations t = AppLocalizations.of(context);
    final AppPalette c = context.colors;
    return GlassScaffold(
      extendBodyBehindAppBar: false,
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.fromLTRB(20, 12, 20, 32),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: <Widget>[
              _topBar(c),
              const SizedBox(height: 12),
              _brandHero(t, c),
              const SizedBox(height: 20),
              GlassCard(
                padding: const EdgeInsets.all(22),
                strong: true,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: <Widget>[
                    Text(t.authLoginTitle,
                        style: Theme.of(context).textTheme.headlineSmall),
                    const SizedBox(height: 6),
                    Text(t.authLoginSubtitle,
                        style: Theme.of(context).textTheme.bodyMedium),
                    const SizedBox(height: 20),
                    _tabs(t, c),
                    const SizedBox(height: 22),
                    if (_tab == _AuthTab.phone)
                      _phonePane(t, c)
                    else
                      _emailPane(t),
                    const SizedBox(height: 22),
                    _divider(t, c),
                    const SizedBox(height: 16),
                    _socials(t, c),
                    const SizedBox(height: 18),
                    Text(
                      t.authTermsNotice,
                      textAlign: TextAlign.center,
                      style: Theme.of(context)
                          .textTheme
                          .bodySmall
                          ?.copyWith(color: c.textMuted),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _topBar(AppPalette c) {
    final ThemeMode mode = ref.watch(themeModeProvider);
    final bool isDark = mode == ThemeMode.dark ||
        (mode == ThemeMode.system &&
            MediaQuery.platformBrightnessOf(context) == Brightness.dark);
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: <Widget>[
        const _LanguageButton(),
        IconButton(
          onPressed: () =>
              ref.read(themeModeProvider.notifier).toggle(),
          icon: Icon(
            isDark ? Icons.light_mode_rounded : Icons.dark_mode_rounded,
            color: c.textMuted,
          ),
          tooltip: 'Theme',
        ),
      ],
    );
  }

  Widget _brandHero(AppLocalizations t, AppPalette c) {
    return BrandHeroCard(
      deep: true,
      padding: const EdgeInsets.all(24),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: <Widget>[
          Text(
            t.appName,
            style: const TextStyle(
              color: Colors.white,
              fontSize: 34,
              fontWeight: FontWeight.w900,
              fontFamily: AppTheme.fontFamily,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            t.appTagline,
            style: TextStyle(color: Colors.white.withValues(alpha: 0.85)),
          ),
          const SizedBox(height: 18),
          Text(
            t.authBrandHeadline,
            style: const TextStyle(
              color: Colors.white,
              fontSize: 20,
              height: 1.5,
              fontWeight: FontWeight.w800,
              fontFamily: AppTheme.fontFamily,
            ),
          ),
          const SizedBox(height: 14),
          _benefit(Icons.workspace_premium_rounded, t.authBenefitNews),
          _benefit(Icons.stars_rounded, t.authBenefitPoints),
          _benefit(Icons.card_giftcard_rounded, t.authBenefitClub),
          _benefit(Icons.place_rounded, t.authBenefitTourism),
        ],
      ),
    );
  }

  Widget _benefit(IconData icon, String label) {
    return Padding(
      padding: const EdgeInsets.only(top: 12),
      child: Row(
        children: <Widget>[
          Container(
            width: 36,
            height: 36,
            decoration: BoxDecoration(
              color: Colors.white.withValues(alpha: 0.16),
              borderRadius: BorderRadius.circular(11),
            ),
            child: Icon(icon, color: Colors.white, size: 19),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Text(
              label,
              style: const TextStyle(
                color: Colors.white,
                fontWeight: FontWeight.w600,
                fontFamily: AppTheme.fontFamily,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _tabs(AppLocalizations t, AppPalette c) {
    Widget tab(_AuthTab value, IconData icon, String label) {
      final bool active = _tab == value;
      return Expanded(
        child: GestureDetector(
          onTap: () => setState(() => _tab = value),
          child: AnimatedContainer(
            duration: const Duration(milliseconds: 180),
            padding: const EdgeInsets.symmetric(vertical: 11),
            decoration: BoxDecoration(
              color: active ? c.surface : Colors.transparent,
              borderRadius: BorderRadius.circular(AppRadii.pill),
              boxShadow: active
                  ? <BoxShadow>[
                      BoxShadow(
                        color: Colors.black.withValues(alpha: 0.06),
                        blurRadius: 8,
                        offset: const Offset(0, 2),
                      ),
                    ]
                  : null,
            ),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: <Widget>[
                Icon(icon,
                    size: 17, color: active ? c.brand : c.textMuted),
                const SizedBox(width: 7),
                Text(
                  label,
                  style: TextStyle(
                    color: active ? c.brand : c.textMuted,
                    fontWeight: FontWeight.w700,
                    fontFamily: AppTheme.fontFamily,
                  ),
                ),
              ],
            ),
          ),
        ),
      );
    }

    return Container(
      padding: const EdgeInsets.all(5),
      decoration: BoxDecoration(
        color: c.surface2,
        borderRadius: BorderRadius.circular(AppRadii.pill),
      ),
      child: Row(
        children: <Widget>[
          tab(_AuthTab.phone, Icons.smartphone_rounded, t.authTabPhone),
          tab(_AuthTab.email, Icons.mail_outline_rounded, t.authTabEmail),
        ],
      ),
    );
  }

  Widget _phonePane(AppLocalizations t, AppPalette c) {
    if (!_otpSent) {
      return Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: <Widget>[
          _fieldLabel(t.authMobileLabel),
          TextField(
            controller: _mobile,
            keyboardType: TextInputType.phone,
            textDirection: TextDirection.ltr,
            textAlign: TextAlign.right,
            decoration: InputDecoration(hintText: t.authMobileHint),
          ),
          const SizedBox(height: 18),
          GradientButton(
            label: t.authSendCode,
            loading: _busy,
            onPressed: _busy ? null : _sendOtp,
          ),
        ],
      );
    }
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: <Widget>[
        Text(
          t.authOtpSentTo(_mobile.text.trim()),
          textAlign: TextAlign.center,
          style: Theme.of(context).textTheme.bodyMedium,
        ),
        const SizedBox(height: 16),
        _codeRow(c),
        const SizedBox(height: 14),
        Center(
          child: _remaining > 0
              ? Text(
                  t.authResendIn(context.faDigits(_formattedRemaining)),
                  style: Theme.of(context).textTheme.bodySmall,
                )
              : TextButton(
                  onPressed: _sendOtp,
                  child: Text(t.authResendCode),
                ),
        ),
        const SizedBox(height: 8),
        GradientButton(
          label: t.authVerifyAndLogin,
          loading: _busy,
          onPressed: _busy ? null : _verify,
        ),
        const SizedBox(height: 6),
        Center(
          child: TextButton(
            onPressed: () => setState(() {
              _otpSent = false;
              for (final TextEditingController c in _codeCtrls) {
                c.clear();
              }
            }),
            child: Text(t.authChangeMobile),
          ),
        ),
      ],
    );
  }

  Widget _codeRow(AppPalette c) {
    return Directionality(
      textDirection: TextDirection.ltr,
      child: Row(
        mainAxisAlignment: MainAxisAlignment.center,
        children: <Widget>[
          for (int i = 0; i < AppConfig.otpLength; i++)
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 5),
              child: SizedBox(
                width: 48,
                height: 58,
                child: TextField(
                  controller: _codeCtrls[i],
                  focusNode: _codeNodes[i],
                  autofocus: i == 0,
                  textAlign: TextAlign.center,
                  keyboardType: TextInputType.number,
                  maxLength: 1,
                  style: Theme.of(context).textTheme.headlineSmall,
                  inputFormatters: <TextInputFormatter>[
                    FilteringTextInputFormatter.digitsOnly,
                  ],
                  decoration: const InputDecoration(counterText: ''),
                  onChanged: (v) {
                    if (v.isNotEmpty && i < AppConfig.otpLength - 1) {
                      _codeNodes[i + 1].requestFocus();
                    } else if (v.isEmpty && i > 0) {
                      _codeNodes[i - 1].requestFocus();
                    }
                    if (i == AppConfig.otpLength - 1 &&
                        _code.length == AppConfig.otpLength) {
                      FocusScope.of(context).unfocus();
                    }
                    setState(() {});
                  },
                ),
              ),
            ),
        ],
      ),
    );
  }

  Widget _emailPane(AppLocalizations t) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: <Widget>[
        _fieldLabel(t.authEmailLabel),
        TextField(
          controller: _email,
          keyboardType: TextInputType.emailAddress,
          textDirection: TextDirection.ltr,
          textAlign: TextAlign.right,
          decoration: InputDecoration(hintText: t.authEmailHint),
        ),
        const SizedBox(height: 16),
        _fieldLabel(t.authPasswordLabel),
        TextField(
          controller: _password,
          obscureText: true,
          textDirection: TextDirection.ltr,
          textAlign: TextAlign.right,
          decoration: InputDecoration(hintText: t.authPasswordHint),
        ),
        const SizedBox(height: 18),
        GradientButton(
          label: t.authLoginButton,
          loading: _busy,
          onPressed: _busy ? null : _passwordLogin,
        ),
      ],
    );
  }

  Widget _fieldLabel(String label) => Padding(
        padding: const EdgeInsets.only(bottom: 7),
        child: Text(label, style: Theme.of(context).textTheme.titleSmall),
      );

  Widget _divider(AppLocalizations t, AppPalette c) {
    return Row(
      children: <Widget>[
        Expanded(child: Divider(color: c.border)),
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 12),
          child: Text(t.authOrContinueWith,
              style: Theme.of(context).textTheme.bodySmall),
        ),
        Expanded(child: Divider(color: c.border)),
      ],
    );
  }

  Widget _socials(AppLocalizations t, AppPalette c) {
    return Column(
      children: <Widget>[
        _socialButton(Icons.g_mobiledata_rounded, t.authContinueWithGoogle, c),
        const SizedBox(height: 10),
        _socialButton(Icons.apple_rounded, t.authContinueWithApple, c),
        const SizedBox(height: 10),
        _socialButton(Icons.public_rounded, t.authContinueWithSocial, c),
      ],
    );
  }

  Widget _socialButton(IconData icon, String label, AppPalette c) {
    final AppLocalizations t = AppLocalizations.of(context);
    // Disabled by design — social auth is not available yet ("coming soon").
    return Opacity(
      opacity: 0.6,
      child: Container(
        height: 50,
        padding: const EdgeInsets.symmetric(horizontal: 16),
        decoration: BoxDecoration(
          color: c.surface.withValues(alpha: 0.5),
          borderRadius: BorderRadius.circular(AppRadii.md),
          border: Border.all(color: c.border),
        ),
        child: Row(
          children: <Widget>[
            Icon(icon, size: 22, color: c.textMuted),
            const SizedBox(width: 10),
            Expanded(
              child: Text(
                label,
                style: Theme.of(context)
                    .textTheme
                    .titleSmall
                    ?.copyWith(color: c.textMuted),
              ),
            ),
            StatusPill(t.commonComingSoon, color: c.gold),
          ],
        ),
      ),
    );
  }
}

/// Compact language toggle (fa ⇄ en) shown on the login top bar.
class _LanguageButton extends ConsumerWidget {
  const _LanguageButton({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final AppPalette c = context.colors;
    final Locale locale = ref.watch(localeProvider);
    return TextButton.icon(
      onPressed: () => ref.read(localeProvider.notifier).toggle(),
      icon: Icon(Icons.language_rounded, size: 18, color: c.textMuted),
      label: Text(
        locale.languageCode == 'fa' ? 'EN' : 'فا',
        style: TextStyle(color: c.textMuted, fontWeight: FontWeight.w700),
      ),
    );
  }
}
