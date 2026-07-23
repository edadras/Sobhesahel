import 'package:dio/dio.dart';

import '../config.dart';
import 'token_storage.dart';

/// A friendly, typed failure surfaced by [ApiClient]. Screens show
/// [message] directly (the API already localizes it via `Accept-Language`).
class ApiException implements Exception {
  const ApiException(this.message, {this.statusCode, this.errors});

  final String message;
  final int? statusCode;
  final Map<String, dynamic>? errors;

  bool get isUnauthorized => statusCode == 401;
  bool get isValidation => statusCode == 422;
  bool get isRateLimited => statusCode == 429;

  @override
  String toString() => 'ApiException($statusCode): $message';
}

/// Wraps Dio with the member-API base URL, a bearer-token interceptor and an
/// `Accept-Language` header driven by the active locale. Every response is
/// unwrapped from its `{ "data": ... }` envelope.
class ApiClient {
  ApiClient({required TokenStorage tokenStorage, Dio? dio})
      : _tokenStorage = tokenStorage,
        _dio = dio ?? Dio() {
    _dio.options
      ..baseUrl = '${AppConfig.baseUrl}/api/member'
      ..connectTimeout = AppConfig.connectTimeout
      ..receiveTimeout = AppConfig.receiveTimeout
      ..headers['Accept'] = 'application/json'
      ..headers['Content-Type'] = 'application/json';

    _dio.interceptors.add(
      InterceptorsWrapper(
        onRequest: (options, handler) async {
          final String? token = _tokenStorage.cachedToken ??
              await _tokenStorage.read();
          if (token != null && token.isNotEmpty) {
            options.headers['Authorization'] = 'Bearer $token';
          }
          options.headers['Accept-Language'] = _localeCode;
          handler.next(options);
        },
      ),
    );
  }

  final Dio _dio;
  final TokenStorage _tokenStorage;

  String _localeCode = 'fa';

  /// Called by the app whenever the locale changes so subsequent requests
  /// carry the right `Accept-Language`.
  set localeCode(String code) => _localeCode = code;

  Dio get raw => _dio;

  // --- Verb helpers that unwrap `{data: ...}` ---

  Future<dynamic> getData(
    String path, {
    Map<String, dynamic>? query,
  }) async {
    return _run(() => _dio.get(path, queryParameters: query));
  }

  Future<dynamic> postData(
    String path, {
    Object? body,
  }) async {
    return _run(() => _dio.post(path, data: body));
  }

  Future<dynamic> putData(
    String path, {
    Object? body,
  }) async {
    return _run(() => _dio.put(path, data: body));
  }

  /// Returns the whole `{data, meta, ...}` map (for paginated endpoints).
  Future<Map<String, dynamic>> getEnvelope(
    String path, {
    Map<String, dynamic>? query,
  }) async {
    try {
      final Response<dynamic> res =
          await _dio.get(path, queryParameters: query);
      return (res.data as Map).cast<String, dynamic>();
    } on DioException catch (e) {
      throw _map(e);
    }
  }

  Future<dynamic> _run(Future<Response<dynamic>> Function() request) async {
    try {
      final Response<dynamic> res = await request();
      final dynamic data = res.data;
      if (data is Map && data.containsKey('data')) return data['data'];
      return data;
    } on DioException catch (e) {
      throw _map(e);
    }
  }

  ApiException _map(DioException e) {
    final dynamic data = e.response?.data;
    String message = 'خطای ارتباط با سرور';
    Map<String, dynamic>? errors;
    if (data is Map) {
      if (data['message'] != null) message = data['message'].toString();
      if (data['errors'] is Map) {
        errors = (data['errors'] as Map).cast<String, dynamic>();
      }
    } else if (e.type == DioExceptionType.connectionError ||
        e.type == DioExceptionType.connectionTimeout) {
      message = 'اتصال اینترنت برقرار نیست';
    }
    return ApiException(
      message,
      statusCode: e.response?.statusCode,
      errors: errors,
    );
  }
}
