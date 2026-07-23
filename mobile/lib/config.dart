/// App-wide compile-time configuration for the Sobhe Sahel member app.
///
/// Feature agents: read [AppConfig.useMock] to decide whether repositories
/// return live API data or the bundled Persian mock data.
class AppConfig {
  const AppConfig._();

  /// Base URL of the member API. Every request is prefixed with `/api/member`
  /// (see [ApiClient]). Change this to point at a real backend.
  static const String baseUrl = 'https://api.sobhesahel.ir';

  /// When `true` (the default) the app runs fully offline against realistic
  /// Persian sample data, so it can be demoed without any backend.
  /// Set to `false` to hit [baseUrl].
  static const bool useMock = true;

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
