// Typed DTOs for the Sobhe Sahel public News API (see docs/NEWS_API.md).
//
// The public/news content layer sits alongside the member layer (models.dart)
// without touching it. Every model has a tolerant, null-safe `fromJson` so a
// partial or guarded backend response never crashes the app. The coercers
// below are local copies of the ones in models.dart (which are file-private).

String _s(dynamic v, [String fallback = '']) => v?.toString() ?? fallback;

String? _sn(dynamic v) {
  if (v == null) return null;
  final String s = v.toString();
  return s.isEmpty ? null : s;
}

int _i(dynamic v, [int fallback = 0]) {
  if (v is int) return v;
  if (v is double) return v.round();
  if (v is String) return int.tryParse(v) ?? fallback;
  return fallback;
}

bool _b(dynamic v, [bool fallback = false]) {
  if (v is bool) return v;
  if (v is num) return v != 0;
  if (v is String) return v == 'true' || v == '1';
  return fallback;
}

List<Map<String, dynamic>> _maps(dynamic v) {
  if (v is List) {
    return v.whereType<Map>().map((e) => e.cast<String, dynamic>()).toList();
  }
  return const <Map<String, dynamic>>[];
}

List<String> _strings(dynamic v) {
  if (v is List) {
    return v
        .where((e) => e != null)
        .map((e) => e.toString())
        .where((e) => e.isNotEmpty)
        .toList();
  }
  return const <String>[];
}

Map<String, dynamic> _map(dynamic v) =>
    (v as Map?)?.cast<String, dynamic>() ?? const <String, dynamic>{};

/// The five public content types the site exposes.
enum ContentType { news, note, video, podcast, photo }

ContentType contentTypeFromString(String raw) {
  switch (raw.toLowerCase()) {
    case 'note':
      return ContentType.note;
    case 'video':
      return ContentType.video;
    case 'podcast':
      return ContentType.podcast;
    case 'photo':
    case 'gallery':
      return ContentType.photo;
    default:
      return ContentType.news;
  }
}

extension ContentTypeApi on ContentType {
  String get apiValue {
    switch (this) {
      case ContentType.note:
        return 'note';
      case ContentType.video:
        return 'video';
      case ContentType.podcast:
        return 'podcast';
      case ContentType.photo:
        return 'photo';
      case ContentType.news:
        return 'news';
    }
  }
}

/// A lightweight category reference embedded in cards/details.
class CategoryRef {
  const CategoryRef({required this.title, required this.slug});

  final String title;
  final String slug;

  factory CategoryRef.fromJson(Map<String, dynamic> j) => CategoryRef(
        title: _s(j['title']),
        slug: _s(j['slug']),
      );
}

// ---------------------------------------------------------------------------
// Menu / services (Category tree)
// ---------------------------------------------------------------------------

class MenuItem {
  const MenuItem({
    required this.id,
    required this.title,
    required this.slug,
    required this.type,
    this.children = const <MenuItem>[],
  });

  final int id;
  final String title;
  final String slug;

  /// Either a content type (`news`, `video`, …) or `category`.
  final String type;
  final List<MenuItem> children;

  bool get hasChildren => children.isNotEmpty;

  factory MenuItem.fromJson(Map<String, dynamic> j) => MenuItem(
        id: _i(j['id']),
        title: _s(j['title']),
        slug: _s(j['slug']),
        type: _s(j['type'], 'category'),
        children: _maps(j['children']).map(MenuItem.fromJson).toList(),
      );
}

// ---------------------------------------------------------------------------
// Card (list item)
// ---------------------------------------------------------------------------

class NewsCard {
  const NewsCard({
    required this.id,
    required this.code,
    required this.type,
    required this.title,
    required this.lead,
    this.imageUrl,
    this.category,
    this.author,
    required this.publishedAtJalali,
    this.url = '',
    this.visits = 0,
    this.bookmarked = false,
    this.duration,
  });

  final int id;
  final String code;
  final String type;
  final String title;
  final String lead;
  final String? imageUrl;
  final CategoryRef? category;
  final String? author;
  final String publishedAtJalali;
  final String url;
  final int visits;
  final bool bookmarked;

  /// Optional media duration label (e.g. `۰۴:۳۲`) for video/podcast cards.
  final String? duration;

  ContentType get contentType => contentTypeFromString(type);

  factory NewsCard.fromJson(Map<String, dynamic> j) {
    final dynamic author = j['author'];
    final String? authorName =
        author is Map ? _sn(author['name']) : _sn(author);
    return NewsCard(
      id: _i(j['id']),
      code: _s(j['code']),
      type: _s(j['type'], 'news'),
      title: _s(j['title']),
      lead: _s(j['lead']),
      imageUrl: _sn(j['image_url']),
      category: j['category'] is Map
          ? CategoryRef.fromJson(_map(j['category']))
          : null,
      author: authorName,
      publishedAtJalali: _s(j['published_at_jalali']),
      url: _s(j['url']),
      visits: _i(j['visits']),
      bookmarked: _b(j['bookmarked']),
      duration: _sn(j['duration']),
    );
  }
}

// ---------------------------------------------------------------------------
// Author
// ---------------------------------------------------------------------------

class AuthorCard {
  const AuthorCard({
    required this.id,
    required this.userType,
    required this.name,
    this.avatarUrl,
    this.bio,
    this.url = '',
    this.postsCount = 0,
  });

  final int id;
  final String userType;
  final String name;
  final String? avatarUrl;
  final String? bio;
  final String url;
  final int postsCount;

  String get initials => name.trim().isNotEmpty ? name.trim()[0] : '?';

  factory AuthorCard.fromJson(Map<String, dynamic> j) => AuthorCard(
        id: _i(j['id']),
        userType: _s(j['user_type'], 'author'),
        name: _s(j['name']),
        avatarUrl: _sn(j['avatar_url']),
        bio: _sn(j['bio']),
        url: _s(j['url']),
        postsCount: _i(j['posts_count']),
      );
}

/// An author profile together with a first page of their content.
class AuthorProfile {
  const AuthorProfile({required this.author, required this.posts});

  final AuthorCard author;
  final Paged<NewsCard> posts;
}

// ---------------------------------------------------------------------------
// Article detail
// ---------------------------------------------------------------------------

class ArticleDetail {
  const ArticleDetail({
    required this.id,
    required this.code,
    required this.type,
    this.rotitr,
    required this.title,
    required this.lead,
    required this.bodyHtml,
    this.subtitles = const <String>[],
    this.imageUrl,
    this.images = const <String>[],
    this.mediaUrl,
    this.category,
    this.tags = const <String>[],
    this.author,
    required this.publishedAtJalali,
    this.visits = 0,
    this.related = const <NewsCard>[],
    this.gallery = const <String>[],
    this.videoEmbed,
    this.audioUrl,
    this.showComments = true,
    this.commentsCount = 0,
    this.bookmarked = false,
  });

  final int id;
  final String code;
  final String type;
  final String? rotitr;
  final String title;
  final String lead;
  final String bodyHtml;
  final List<String> subtitles;
  final String? imageUrl;
  final List<String> images;
  final String? mediaUrl;
  final CategoryRef? category;
  final List<String> tags;
  final AuthorCard? author;
  final String publishedAtJalali;
  final int visits;
  final List<NewsCard> related;
  final List<String> gallery;
  final String? videoEmbed;
  final String? audioUrl;
  final bool showComments;
  final int commentsCount;
  final bool bookmarked;

  ContentType get contentType => contentTypeFromString(type);

  ArticleDetail copyWith({bool? bookmarked}) => ArticleDetail(
        id: id,
        code: code,
        type: type,
        rotitr: rotitr,
        title: title,
        lead: lead,
        bodyHtml: bodyHtml,
        subtitles: subtitles,
        imageUrl: imageUrl,
        images: images,
        mediaUrl: mediaUrl,
        category: category,
        tags: tags,
        author: author,
        publishedAtJalali: publishedAtJalali,
        visits: visits,
        related: related,
        gallery: gallery,
        videoEmbed: videoEmbed,
        audioUrl: audioUrl,
        showComments: showComments,
        commentsCount: commentsCount,
        bookmarked: bookmarked ?? this.bookmarked,
      );

  factory ArticleDetail.fromJson(Map<String, dynamic> j) => ArticleDetail(
        id: _i(j['id']),
        code: _s(j['code']),
        type: _s(j['type'], 'news'),
        rotitr: _sn(j['rotitr']),
        title: _s(j['title']),
        lead: _s(j['lead']),
        bodyHtml: _s(j['body_html']),
        subtitles: _strings(j['subtitles']),
        imageUrl: _sn(j['image_url']),
        images: _strings(j['images']),
        mediaUrl: _sn(j['media_url']),
        category: j['category'] is Map
            ? CategoryRef.fromJson(_map(j['category']))
            : null,
        tags: _strings(j['tags']),
        author:
            j['author'] is Map ? AuthorCard.fromJson(_map(j['author'])) : null,
        publishedAtJalali: _s(j['published_at_jalali']),
        visits: _i(j['visits']),
        related: _maps(j['related']).map(NewsCard.fromJson).toList(),
        gallery: _strings(j['gallery']),
        videoEmbed: _sn(j['video_embed']),
        audioUrl: _sn(j['audio_url']),
        showComments: _b(j['show_comments'], true),
        commentsCount: _i(j['comments_count']),
        bookmarked: _b(j['bookmarked']),
      );
}

// ---------------------------------------------------------------------------
// Comments
// ---------------------------------------------------------------------------

class CommentItem {
  const CommentItem({
    required this.id,
    required this.name,
    required this.body,
    required this.createdAtJalali,
    this.replies = const <CommentItem>[],
  });

  final int id;
  final String name;
  final String body;
  final String createdAtJalali;
  final List<CommentItem> replies;

  factory CommentItem.fromJson(Map<String, dynamic> j) => CommentItem(
        id: _i(j['id']),
        name: _s(j['name'], 'کاربر مهمان'),
        body: _s(j['body']),
        createdAtJalali: _s(j['created_at_jalali']),
        replies: _maps(j['replies']).map(CommentItem.fromJson).toList(),
      );
}

// ---------------------------------------------------------------------------
// Publications (newspaper archive)
// ---------------------------------------------------------------------------

class Publication {
  const Publication({
    required this.id,
    required this.title,
    this.coverUrl,
    required this.dateJalali,
    required this.type,
    this.pdfUrl,
    this.number,
  });

  final int id;
  final String title;
  final String? coverUrl;
  final String dateJalali;
  final String type;
  final String? pdfUrl;
  final String? number;

  factory Publication.fromJson(Map<String, dynamic> j) => Publication(
        id: _i(j['id']),
        title: _s(j['title']),
        coverUrl: _sn(j['cover_url']),
        dateJalali: _s(j['date_jalali']),
        type: _s(j['type'], 'روزنامه'),
        pdfUrl: _sn(j['pdf_url']),
        number: _sn(j['number']),
      );
}

// ---------------------------------------------------------------------------
// Prices (currency / gold)
// ---------------------------------------------------------------------------

class PriceItem {
  const PriceItem({
    required this.title,
    required this.value,
    this.change = 0,
    this.unit,
  });

  final String title;
  final String value;

  /// Signed percentage change; positive = up.
  final double change;
  final String? unit;

  bool get isUp => change >= 0;

  factory PriceItem.fromJson(Map<String, dynamic> j) => PriceItem(
        title: _s(j['title']),
        value: _s(j['value'] ?? j['price']),
        change: (j['change'] is num)
            ? (j['change'] as num).toDouble()
            : double.tryParse(_s(j['change'])) ?? 0,
        unit: _sn(j['unit']),
      );
}

// ---------------------------------------------------------------------------
// Live streams
// ---------------------------------------------------------------------------

class LiveStream {
  const LiveStream({
    required this.id,
    required this.title,
    this.url = '',
    this.thumbnailUrl,
    this.isLive = true,
  });

  final int id;
  final String title;
  final String url;
  final String? thumbnailUrl;
  final bool isLive;

  factory LiveStream.fromJson(Map<String, dynamic> j) => LiveStream(
        id: _i(j['id']),
        title: _s(j['title']),
        url: _s(j['url']),
        thumbnailUrl: _sn(j['thumbnail_url']),
        isLive: _b(j['is_live'], true),
      );
}

// ---------------------------------------------------------------------------
// Home payload
// ---------------------------------------------------------------------------

class HomeBox {
  const HomeBox({
    required this.title,
    required this.slug,
    required this.items,
  });

  final String title;
  final String slug;
  final List<NewsCard> items;

  factory HomeBox.fromJson(Map<String, dynamic> j) => HomeBox(
        title: _s(j['title']),
        slug: _s(j['slug']),
        items: _maps(j['items']).map(NewsCard.fromJson).toList(),
      );
}

class HomePayload {
  const HomePayload({
    this.breaking = const <NewsCard>[],
    this.slider = const <NewsCard>[],
    this.latest = const <NewsCard>[],
    this.boxes = const <HomeBox>[],
    this.videos = const <NewsCard>[],
    this.podcasts = const <NewsCard>[],
    this.galleries = const <NewsCard>[],
    this.latestIssue,
    this.prices = const <PriceItem>[],
    this.live = const <LiveStream>[],
  });

  final List<NewsCard> breaking;
  final List<NewsCard> slider;
  final List<NewsCard> latest;
  final List<HomeBox> boxes;
  final List<NewsCard> videos;
  final List<NewsCard> podcasts;
  final List<NewsCard> galleries;
  final Publication? latestIssue;
  final List<PriceItem> prices;
  final List<LiveStream> live;

  factory HomePayload.fromJson(Map<String, dynamic> j) => HomePayload(
        breaking: _maps(j['breaking']).map(NewsCard.fromJson).toList(),
        slider: _maps(j['slider']).map(NewsCard.fromJson).toList(),
        latest: _maps(j['latest']).map(NewsCard.fromJson).toList(),
        boxes: _maps(j['boxes']).map(HomeBox.fromJson).toList(),
        videos: _maps(j['videos']).map(NewsCard.fromJson).toList(),
        podcasts: _maps(j['podcasts']).map(NewsCard.fromJson).toList(),
        galleries: _maps(j['galleries']).map(NewsCard.fromJson).toList(),
        latestIssue: j['latest_issue'] is Map
            ? Publication.fromJson(_map(j['latest_issue']))
            : null,
        prices: _maps(j['prices']).map(PriceItem.fromJson).toList(),
        live: _maps(j['live']).map(LiveStream.fromJson).toList(),
      );
}

// ---------------------------------------------------------------------------
// Search
// ---------------------------------------------------------------------------

class SearchResults {
  const SearchResults({
    this.posts = const <NewsCard>[],
    this.authors = const <AuthorCard>[],
  });

  final List<NewsCard> posts;
  final List<AuthorCard> authors;

  bool get isEmpty => posts.isEmpty && authors.isEmpty;

  factory SearchResults.fromJson(Map<String, dynamic> j) => SearchResults(
        posts: _maps(j['posts']).map(NewsCard.fromJson).toList(),
        authors: _maps(j['authors']).map(AuthorCard.fromJson).toList(),
      );
}

// ---------------------------------------------------------------------------
// Pagination wrapper (mirrors Paged<T> in models.dart so news screens don't
// depend on the member models file).
// ---------------------------------------------------------------------------

class Paged<T> {
  const Paged({required this.items, this.currentPage = 1, this.lastPage = 1});

  final List<T> items;
  final int currentPage;
  final int lastPage;

  bool get hasMore => currentPage < lastPage;
}
