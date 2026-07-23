/// App-wide compile-time configuration for the Sobhe Sahel member app.
///
/// Feature agents: read [AppConfig.useMock] to decide whether repositories
/// return live API data or the bundled Persian mock data.
class AppConfig {
  const AppConfig._();

  /// Origin of the live Sobhe Sahel backend. Public news requests are prefixed
  /// with `/api/v1` (see [NewsApiClient]) and member requests with `/api/member`
  /// (see [ApiClient]). Points at the production site by default.
  static const String baseUrl = 'https://sobhesahel.com';

  /// When `false` (the default) the app reads REAL data from the live API at
  /// [baseUrl] (`/api/v1` for public news, `/api/member` for the member area).
  ///
  /// DEV TOGGLE: flip to `true` to run fully offline against the bundled
  /// realistic Persian demo data in `lib/data/mock/` (no backend needed; any OTP
  /// code and any email/password are accepted). Intended only for local demos.
  static const bool useMock = false;

  /// Number of digits in the OTP code. Matches the site's login template
  /// (5-box entry, demo code `12345`). In mock mode any code is accepted.
  static const int otpLength = 5;

  /// Seconds before the OTP resend button becomes available again
  /// (the API rate-limits OTP requests to 1 per 2 minutes → 119s countdown).
  static const int otpResendSeconds = 119;

  /// Network timeouts.
  static const Duration connectTimeout = Duration(seconds: 20);
  static const Duration receiveTimeout = Duration(seconds: 20);

  /// Secure-storage key under which the bearer token is persisted.
  static const String tokenStorageKey = 'ss_member_token';
}
