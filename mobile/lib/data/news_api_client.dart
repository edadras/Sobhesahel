import 'package:dio/dio.dart';

import '../config.dart';
import 'api_client.dart';
import 'token_storage.dart';

/// Wraps Dio with the public News-API base URL (`/api/v1`). Requests are
/// public: a bearer token is attached only when a member is logged in (so the
/// backend can populate `bookmarked` flags). Responses are unwrapped from
/// their `{ "data": ... }` envelope, mirroring [ApiClient].
class NewsApiClient {
  NewsApiClient({required TokenStorage tokenStorage, Dio? dio})
      : _tokenStorage = tokenStorage,
        _dio = dio ?? Dio() {
    _dio.options
      ..baseUrl = '${AppConfig.baseUrl}/api/v1'
      ..connectTimeout = AppConfig.connectTimeout
      ..receiveTimeout = AppConfig.receiveTimeout
      ..headers['Accept'] = 'application/json'
      ..headers['Content-Type'] = 'application/json';

    _dio.interceptors.add(
      InterceptorsWrapper(
        onRequest: (options, handler) async {
          final String? token =
              _tokenStorage.cachedToken ?? await _tokenStorage.read();
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

  set localeCode(String code) => _localeCode = code;

  Dio get raw => _dio;

  /// GET returning the unwrapped `data` payload.
  Future<dynamic> getData(String path, {Map<String, dynamic>? query}) {
    return _run(() => _dio.get(path, queryParameters: query));
  }

  /// POST returning the unwrapped `data` payload.
  Future<dynamic> postData(String path, {Object? body}) {
    return _run(() => _dio.post(path, data: body));
  }

  /// GET returning the whole `{data, meta, ...}` map (for paginated lists).
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
