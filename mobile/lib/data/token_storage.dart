import 'package:flutter_secure_storage/flutter_secure_storage.dart';

import '../config.dart';

/// Thin wrapper around [FlutterSecureStorage] for the bearer token. A tiny
/// in-memory cache keeps synchronous reads (used by the Dio interceptor) fast
/// after the first async load.
class TokenStorage {
  TokenStorage([FlutterSecureStorage? storage])
      : _storage = storage ??
            const FlutterSecureStorage(
              aOptions: AndroidOptions(encryptedSharedPreferences: true),
            );

  final FlutterSecureStorage _storage;
  String? _cached;
  bool _loaded = false;

  String? get cachedToken => _cached;

  Future<String?> read() async {
    if (_loaded) return _cached;
    _cached = await _storage.read(key: AppConfig.tokenStorageKey);
    _loaded = true;
    return _cached;
  }

  Future<void> write(String token) async {
    _cached = token;
    _loaded = true;
    await _storage.write(key: AppConfig.tokenStorageKey, value: token);
  }

  Future<void> clear() async {
    _cached = null;
    _loaded = true;
    await _storage.delete(key: AppConfig.tokenStorageKey);
  }
}
