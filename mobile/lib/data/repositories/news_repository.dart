// The public News/content repository interface (see docs/NEWS_API.md).
//
// A Dio-backed implementation lives in api_news_repository.dart and a rich
// Persian mock in ../mock/mock_news_repository.dart. The provider in
// ../providers.dart selects one based on AppConfig.useMock.

import '../models/news_models.dart';

/// Sort order accepted by list endpoints.
enum NewsSort { newest, oldest }

extension NewsSortApi on NewsSort {
  String get apiValue => this == NewsSort.oldest ? 'oldest' : 'newest';
}

abstract class NewsRepository {
  /// GET /home — the aggregated home payload (every section guarded/optional).
  Future<HomePayload> home();

  /// GET /menu — the services/category navigation tree.
  Future<List<MenuItem>> menu();

  /// GET /content/{type}?page=&category=&tag=&sort=.
  Future<Paged<NewsCard>> contentList({
    required String type,
    String? category,
    String? tag,
    NewsSort sort = NewsSort.newest,
    int page = 1,
  });

  /// GET /content/{type}/{code} — full article/media detail.
  Future<ArticleDetail> articleDetail({
    required String type,
    required String code,
  });

  /// GET /category/{slug}?page=.
  Future<Paged<NewsCard>> category(String slug, {int page = 1});

  /// GET /tag/{name}?page=.
  Future<Paged<NewsCard>> tag(String name, {int page = 1});

  /// GET /search?q=&type=&category=&from=&to=&author=&sort=.
  Future<SearchResults> search({
    required String q,
    String? type,
    String? category,
    String? from,
    String? to,
    String? author,
    NewsSort sort = NewsSort.newest,
  });

  /// GET /videos?page=.
  Future<Paged<NewsCard>> videos({int page = 1});

  /// GET /podcasts?page=.
  Future<Paged<NewsCard>> podcasts({int page = 1});

  /// GET /galleries?page=.
  Future<Paged<NewsCard>> galleries({int page = 1});

  /// GET /publications?year=.
  Future<List<Publication>> publications({int? year});

  /// GET /prices.
  Future<List<PriceItem>> prices();

  /// GET /live.
  Future<List<LiveStream>> live();

  /// POST /content/{type}/{code}/comment — guest or member.
  Future<CommentItem> postComment({
    required String type,
    required String code,
    required String name,
    required String body,
    String? email,
  });

  /// GET /content/{type}/{code}/comments — approved comments.
  Future<List<CommentItem>> comments({
    required String type,
    required String code,
  });

  /// GET /authors/{user_type}/{id} — profile + first page of content.
  Future<AuthorProfile> author({
    required String userType,
    required int id,
    int page = 1,
  });
}
