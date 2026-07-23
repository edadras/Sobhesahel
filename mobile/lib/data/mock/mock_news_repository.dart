// In-memory mock implementation of NewsRepository, returning the Persian
// sample data from mock_news_data.dart after a short simulated network delay.

import '../models/news_models.dart';
import '../repositories/news_repository.dart';
import 'mock_news_data.dart';

Future<T> _delayed<T>(T value, [int ms = 420]) =>
    Future<T>.delayed(Duration(milliseconds: ms), () => value);

class MockNewsRepository implements NewsRepository {
  @override
  Future<HomePayload> home() => _delayed(MockNewsData.home(), 550);

  @override
  Future<List<MenuItem>> menu() => _delayed(MockNewsData.menu());

  @override
  Future<Paged<NewsCard>> contentList({
    required String type,
    String? category,
    String? tag,
    NewsSort sort = NewsSort.newest,
    int page = 1,
  }) {
    Paged<NewsCard> paged =
        MockNewsData.typeList(type, category: category, page: page);
    if (sort == NewsSort.oldest) {
      paged = Paged<NewsCard>(
        items: paged.items.reversed.toList(),
        currentPage: paged.currentPage,
        lastPage: paged.lastPage,
      );
    }
    return _delayed(paged);
  }

  @override
  Future<ArticleDetail> articleDetail({
    required String type,
    required String code,
  }) =>
      _delayed(MockNewsData.articleDetail(type, code));

  @override
  Future<Paged<NewsCard>> category(String slug, {int page = 1}) =>
      _delayed(MockNewsData.categoryList(slug, page: page));

  @override
  Future<Paged<NewsCard>> tag(String name, {int page = 1}) => _delayed(
        Paged<NewsCard>(
          items: MockNewsData.allNews().take(8).toList(),
          currentPage: page,
          lastPage: 1,
        ),
      );

  @override
  Future<SearchResults> search({
    required String q,
    String? type,
    String? category,
    String? from,
    String? to,
    String? author,
    NewsSort sort = NewsSort.newest,
  }) =>
      _delayed(MockNewsData.search(q, type: type, category: category), 500);

  @override
  Future<Paged<NewsCard>> videos({int page = 1}) => _delayed(
        Paged<NewsCard>(
            items: MockNewsData.videos(), currentPage: page, lastPage: 1),
      );

  @override
  Future<Paged<NewsCard>> podcasts({int page = 1}) => _delayed(
        Paged<NewsCard>(
            items: MockNewsData.podcasts(), currentPage: page, lastPage: 1),
      );

  @override
  Future<Paged<NewsCard>> galleries({int page = 1}) => _delayed(
        Paged<NewsCard>(
            items: MockNewsData.galleries(), currentPage: page, lastPage: 1),
      );

  @override
  Future<List<Publication>> publications({int? year}) =>
      _delayed(MockNewsData.publications(year: year));

  @override
  Future<List<PriceItem>> prices() => _delayed(MockNewsData.prices());

  @override
  Future<List<LiveStream>> live() => _delayed(MockNewsData.live());

  @override
  Future<CommentItem> postComment({
    required String type,
    required String code,
    required String name,
    required String body,
    String? email,
  }) =>
      _delayed(
        CommentItem(
          id: DateTime.now().millisecondsSinceEpoch % 100000,
          name: name.isEmpty ? 'کاربر مهمان' : name,
          body: body,
          createdAtJalali: 'همین حالا',
        ),
        600,
      );

  @override
  Future<List<CommentItem>> comments({
    required String type,
    required String code,
  }) =>
      _delayed(MockNewsData.comments());

  @override
  Future<AuthorProfile> author({
    required String userType,
    required int id,
    int page = 1,
  }) =>
      _delayed(MockNewsData.authorProfile(userType, id));
}
