// Dio-backed implementation of NewsRepository. Used when AppConfig.useMock is
// false. Each method maps directly to an endpoint in docs/NEWS_API.md.

import '../models/news_models.dart';
import '../news_api_client.dart';
import 'news_repository.dart';

Map<String, dynamic> _asMap(dynamic v) =>
    (v as Map?)?.cast<String, dynamic>() ?? const <String, dynamic>{};

List<Map<String, dynamic>> _asList(dynamic v) => (v is List)
    ? v.whereType<Map>().map((e) => e.cast<String, dynamic>()).toList()
    : const <Map<String, dynamic>>[];

Paged<T> _paged<T>(
  Map<String, dynamic> env,
  T Function(Map<String, dynamic>) parse,
) {
  final Map<String, dynamic> meta = _asMap(env['meta']);
  final Map<String, dynamic> pagination =
      meta['pagination'] is Map ? _asMap(meta['pagination']) : meta;
  return Paged<T>(
    items: _asList(env['data']).map(parse).toList(),
    currentPage: (pagination['current_page'] as num?)?.toInt() ?? 1,
    lastPage: (pagination['last_page'] as num?)?.toInt() ?? 1,
  );
}

class ApiNewsRepository implements NewsRepository {
  ApiNewsRepository(this._api);
  final NewsApiClient _api;

  @override
  Future<HomePayload> home() async {
    final dynamic data = await _api.getData('/home');
    return HomePayload.fromJson(_asMap(data));
  }

  @override
  Future<List<MenuItem>> menu() async {
    final dynamic data = await _api.getData('/menu');
    return _asList(data).map(MenuItem.fromJson).toList();
  }

  @override
  Future<Paged<NewsCard>> contentList({
    required String type,
    String? category,
    String? tag,
    NewsSort sort = NewsSort.newest,
    int page = 1,
  }) async {
    final Map<String, dynamic> env =
        await _api.getEnvelope('/content/$type', query: <String, dynamic>{
      'page': page,
      'sort': sort.apiValue,
      if (category != null) 'category': category,
      if (tag != null) 'tag': tag,
    });
    return _paged(env, NewsCard.fromJson);
  }

  @override
  Future<ArticleDetail> articleDetail({
    required String type,
    required String code,
  }) async {
    final dynamic data = await _api.getData('/content/$type/$code');
    return ArticleDetail.fromJson(_asMap(data));
  }

  @override
  Future<Paged<NewsCard>> category(String slug, {int page = 1}) async {
    final Map<String, dynamic> env = await _api
        .getEnvelope('/category/$slug', query: <String, dynamic>{'page': page});
    return _paged(env, NewsCard.fromJson);
  }

  @override
  Future<Paged<NewsCard>> tag(String name, {int page = 1}) async {
    final Map<String, dynamic> env = await _api
        .getEnvelope('/tag/$name', query: <String, dynamic>{'page': page});
    return _paged(env, NewsCard.fromJson);
  }

  @override
  Future<SearchResults> search({
    required String q,
    String? type,
    String? category,
    String? from,
    String? to,
    String? author,
    NewsSort sort = NewsSort.newest,
  }) async {
    final dynamic data = await _api.getData('/search', query: <String, dynamic>{
      'q': q,
      'sort': sort.apiValue,
      if (type != null) 'type': type,
      if (category != null) 'category': category,
      if (from != null) 'from': from,
      if (to != null) 'to': to,
      if (author != null) 'author': author,
    });
    return SearchResults.fromJson(_asMap(data));
  }

  @override
  Future<Paged<NewsCard>> videos({int page = 1}) async {
    final Map<String, dynamic> env =
        await _api.getEnvelope('/videos', query: <String, dynamic>{'page': page});
    return _paged(env, NewsCard.fromJson);
  }

  @override
  Future<Paged<NewsCard>> podcasts({int page = 1}) async {
    final Map<String, dynamic> env = await _api
        .getEnvelope('/podcasts', query: <String, dynamic>{'page': page});
    return _paged(env, NewsCard.fromJson);
  }

  @override
  Future<Paged<NewsCard>> galleries({int page = 1}) async {
    final Map<String, dynamic> env = await _api
        .getEnvelope('/galleries', query: <String, dynamic>{'page': page});
    return _paged(env, NewsCard.fromJson);
  }

  @override
  Future<List<Publication>> publications({int? year}) async {
    final dynamic data = await _api.getData('/publications',
        query: year == null ? null : <String, dynamic>{'year': year});
    return _asList(data).map(Publication.fromJson).toList();
  }

  @override
  Future<List<PriceItem>> prices() async {
    final dynamic data = await _api.getData('/prices');
    return _asList(data).map(PriceItem.fromJson).toList();
  }

  @override
  Future<List<LiveStream>> live() async {
    final dynamic data = await _api.getData('/live');
    return _asList(data).map(LiveStream.fromJson).toList();
  }

  @override
  Future<CommentItem> postComment({
    required String type,
    required String code,
    required String name,
    required String body,
    String? email,
  }) async {
    final dynamic data =
        await _api.postData('/content/$type/$code/comment', body: {
      'name': name,
      'body': body,
      if (email != null) 'email': email,
    });
    return CommentItem.fromJson(_asMap(data));
  }

  @override
  Future<List<CommentItem>> comments({
    required String type,
    required String code,
  }) async {
    final dynamic data = await _api.getData('/content/$type/$code/comments');
    return _asList(data).map(CommentItem.fromJson).toList();
  }

  @override
  Future<AuthorProfile> author({
    required String userType,
    required int id,
    int page = 1,
  }) async {
    final Map<String, dynamic> env = await _api.getEnvelope(
      '/authors/$userType/$id',
      query: <String, dynamic>{'page': page},
    );
    final Map<String, dynamic> data = _asMap(env['data']);
    final AuthorCard card = AuthorCard.fromJson(
        data['author'] is Map ? _asMap(data['author']) : data);
    final Paged<NewsCard> posts = data['posts'] is List
        ? Paged<NewsCard>(
            items: _asList(data['posts']).map(NewsCard.fromJson).toList())
        : _paged(<String, dynamic>{'data': data['posts'], 'meta': env['meta']},
            NewsCard.fromJson);
    return AuthorProfile(author: card, posts: posts);
  }
}
