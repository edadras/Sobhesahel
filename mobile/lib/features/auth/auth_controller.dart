import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../data/models/models.dart';
import '../../data/providers.dart';
import '../../data/token_storage.dart';

enum AuthStatus { unknown, authenticated, unauthenticated }

class AuthState {
  const AuthState({required this.status, this.member});

  final AuthStatus status;
  final Member? member;

  bool get isAuthenticated => status == AuthStatus.authenticated;
  bool get isKnown => status != AuthStatus.unknown;

  AuthState copyWith({AuthStatus? status, Member? member}) => AuthState(
        status: status ?? this.status,
        member: member ?? this.member,
      );

  static const AuthState unknown = AuthState(status: AuthStatus.unknown);
}

/// Owns the session: bootstraps from stored token, performs OTP / password
/// login, and clears state on logout. The router listens to this to gate
/// routes.
class AuthController extends StateNotifier<AuthState> {
  AuthController(this._ref) : super(AuthState.unknown) {
    _bootstrap();
  }

  final Ref _ref;

  TokenStorage get _tokens => _ref.read(tokenStorageProvider);

  Future<void> _bootstrap() async {
    final String? token = await _tokens.read();
    if (token == null || token.isEmpty) {
      state = const AuthState(status: AuthStatus.unauthenticated);
      return;
    }
    try {
      final Member member = await _ref.read(authRepositoryProvider).me();
      state = AuthState(status: AuthStatus.authenticated, member: member);
    } catch (_) {
      await _tokens.clear();
      state = const AuthState(status: AuthStatus.unauthenticated);
    }
  }

  /// Request an OTP code. Returns the seconds until it expires.
  Future<int> requestOtp(String mobile) {
    return _ref.read(authRepositoryProvider).requestOtp(mobile);
  }

  Future<void> verifyOtp({required String mobile, required String code}) async {
    final AuthResult result = await _ref
        .read(authRepositoryProvider)
        .verifyOtp(mobile: mobile, code: code);
    state = AuthState(
      status: AuthStatus.authenticated,
      member: result.member,
    );
  }

  Future<void> passwordLogin({
    required String email,
    required String password,
  }) async {
    final AuthResult result = await _ref
        .read(authRepositoryProvider)
        .passwordLogin(email: email, password: password);
    state = AuthState(
      status: AuthStatus.authenticated,
      member: result.member,
    );
  }

  /// Update the in-memory member (e.g. after editing the profile).
  void setMember(Member member) {
    state = state.copyWith(member: member);
  }

  Future<void> logout() async {
    try {
      await _ref.read(authRepositoryProvider).logout();
    } catch (_) {
      // Ignore network errors on logout; token is cleared regardless.
    }
    state = const AuthState(status: AuthStatus.unauthenticated);
  }
}

final authControllerProvider =
    StateNotifierProvider<AuthController, AuthState>((ref) {
  return AuthController(ref);
});
