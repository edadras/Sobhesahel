// Rich Persian sample data for the public news layer, used when
// AppConfig.useMock is true so the whole newspaper app is demoable offline.

import '../models/news_models.dart';

/// Central store of realistic Persian news content for Hormozgan province.
class MockNewsData {
  MockNewsData._();

  static String _img(int seed) => 'https://picsum.photos/seed/ss$seed/800/500';
  static String _avatar(int seed) =>
      'https://i.pravatar.cc/160?img=${(seed % 70) + 1}';

  // --- Categories / services -----------------------------------------------

  static const List<CategoryRef> categories = <CategoryRef>[
    CategoryRef(title: 'سیاسی', slug: 'politics'),
    CategoryRef(title: 'اقتصاد', slug: 'economy'),
    CategoryRef(title: 'اجتماعی', slug: 'social'),
    CategoryRef(title: 'ورزشی', slug: 'sports'),
    CategoryRef(title: 'فرهنگی و هنری', slug: 'culture'),
    CategoryRef(title: 'شهرستان‌ها', slug: 'cities'),
    CategoryRef(title: 'خلیج فارس', slug: 'persian-gulf'),
  ];

  static CategoryRef _cat(String slug) =>
      categories.firstWhere((c) => c.slug == slug, orElse: () => categories[0]);

  static const List<String> _authors = <String>[
    'مریم رئیسی',
    'علی بهادری',
    'زهرا موحد',
    'کامران دریانورد',
    'نگار حسینی',
    'رضا خلیجی',
  ];

  static const List<String> _dates = <String>[
    '۱ مرداد ۱۴۰۴',
    '۳۱ تیر ۱۴۰۴',
    '۳۰ تیر ۱۴۰۴',
    '۲۹ تیر ۱۴۰۴',
    '۲۸ تیر ۱۴۰۴',
    '۲۷ تیر ۱۴۰۴',
  ];

  static NewsCard _card({
    required int id,
    required String type,
    required String title,
    required String lead,
    required String slug,
    String? duration,
  }) {
    return NewsCard(
      id: id,
      code: '${100000 + id}',
      type: type,
      title: title,
      lead: lead,
      imageUrl: _img(id),
      category: _cat(slug),
      author: _authors[id % _authors.length],
      publishedAtJalali: _dates[id % _dates.length],
      url: '/$type/${100000 + id}',
      visits: 200 + (id * 137) % 5400,
      bookmarked: id % 5 == 0,
      duration: duration,
    );
  }

  // --- Menu tree -----------------------------------------------------------

  static List<MenuItem> menu() => <MenuItem>[
        MenuItem(id: 1, title: 'سیاسی', slug: 'politics', type: 'category',
            children: <MenuItem>[
              MenuItem(id: 11, title: 'استان هرمزگان', slug: 'politics-hormozgan', type: 'category'),
              MenuItem(id: 12, title: 'ملی', slug: 'politics-national', type: 'category'),
              MenuItem(id: 13, title: 'بین‌الملل', slug: 'politics-world', type: 'category'),
            ]),
        MenuItem(id: 2, title: 'اقتصاد', slug: 'economy', type: 'category',
            children: <MenuItem>[
              MenuItem(id: 21, title: 'بازار و بورس', slug: 'economy-market', type: 'category'),
              MenuItem(id: 22, title: 'صنعت و معدن', slug: 'economy-industry', type: 'category'),
              MenuItem(id: 23, title: 'بندر و دریا', slug: 'economy-port', type: 'category'),
            ]),
        MenuItem(id: 3, title: 'اجتماعی', slug: 'social', type: 'category',
            children: <MenuItem>[
              MenuItem(id: 31, title: 'سلامت', slug: 'social-health', type: 'category'),
              MenuItem(id: 32, title: 'محیط زیست', slug: 'social-environment', type: 'category'),
            ]),
        MenuItem(id: 4, title: 'ورزشی', slug: 'sports', type: 'category'),
        MenuItem(id: 5, title: 'فرهنگی و هنری', slug: 'culture', type: 'category',
            children: <MenuItem>[
              MenuItem(id: 51, title: 'کتاب و ادبیات', slug: 'culture-books', type: 'category'),
              MenuItem(id: 52, title: 'سینما و تئاتر', slug: 'culture-cinema', type: 'category'),
            ]),
        MenuItem(id: 6, title: 'شهرستان‌ها', slug: 'cities', type: 'category',
            children: <MenuItem>[
              MenuItem(id: 61, title: 'بندرعباس', slug: 'cities-bandarabbas', type: 'category'),
              MenuItem(id: 62, title: 'قشم', slug: 'cities-qeshm', type: 'category'),
              MenuItem(id: 63, title: 'کیش', slug: 'cities-kish', type: 'category'),
              MenuItem(id: 64, title: 'میناب', slug: 'cities-minab', type: 'category'),
            ]),
        MenuItem(id: 7, title: 'خلیج فارس', slug: 'persian-gulf', type: 'category'),
        MenuItem(id: 8, title: 'یادداشت‌ها', slug: 'notes', type: 'note'),
        MenuItem(id: 9, title: 'ویدئو', slug: 'videos', type: 'video'),
        MenuItem(id: 10, title: 'پادکست', slug: 'podcasts', type: 'podcast'),
        MenuItem(id: 14, title: 'عکس', slug: 'galleries', type: 'photo'),
      ];

  // --- Titles pools by category --------------------------------------------

  static const Map<String, List<String>> _titlesBySlug =
      <String, List<String>>{
    'politics': <String>[
      'استاندار هرمزگان: توسعه سواحل مکران اولویت دولت است',
      'نشست شورای اداری استان با محوریت اشتغال جوانان برگزار شد',
      'نماینده بندرعباس از تصویب طرح توسعه بندر شهید رجایی خبر داد',
    ],
    'economy': <String>[
      'صادرات از بندر شهید رجایی رکورد جدیدی ثبت کرد',
      'رشد ۱۸ درصدی تولید در منطقه ویژه اقتصادی خلیج فارس',
      'قیمت میگو در بازار بندرعباس به ثبات رسید',
    ],
    'social': <String>[
      'طرح پایش سلامت رایگان در مناطق کم‌برخوردار هرمزگان آغاز شد',
      'کمپین پاک‌سازی سواحل قشم با مشارکت مردمی برگزار شد',
      'افتتاح مرکز جامع سلامت در روستاهای شرق استان',
    ],
    'sports': <String>[
      'تیم فوتبال ساحلی هرمزگان قهرمان مسابقات کشوری شد',
      'درخشش شناگران بندرعباسی در رقابت‌های آب‌های آزاد',
      'استعدادیابی فوتبال پایه در شهرستان میناب کلید خورد',
    ],
    'culture': <String>[
      'جشنواره موسیقی بومی خلیج فارس در بندرعباس برپا شد',
      'نمایشگاه صنایع دستی هرمزگان میزبان هنرمندان جنوب کشور',
      'اکران فیلم مستند «جزیره هرمز» با استقبال مخاطبان',
    ],
    'cities': <String>[
      'پروژه آب‌رسانی به روستاهای میناب وارد فاز پایانی شد',
      'بازسازی بافت تاریخی بندرلنگه با اعتبار ملی',
      'افتتاح پل جدید ارتباطی در جزیره قشم',
    ],
    'persian-gulf': <String>[
      'همایش بین‌المللی خلیج فارس با حضور کارشناسان دریایی برگزار شد',
      'رونمایی از سند توسعه گردشگری دریایی خلیج فارس',
      'مرجان‌های خلیج فارس؛ گنجینه‌ای که باید حفظ شود',
    ],
  };

  static const String _leadSample =
      'به گزارش خبرنگار صبح ساحل، این رویداد با حضور مسئولان استانی و جمعی از فعالان حوزه برگزار شد و بر لزوم توجه بیشتر به ظرفیت‌های بومی استان هرمزگان تأکید شد.';

  static List<NewsCard> _cardsForCategory(String slug, {int startId = 1}) {
    final List<String> titles =
        _titlesBySlug[slug] ?? const <String>['خبری از استان هرمزگان'];
    return <NewsCard>[
      for (int k = 0; k < titles.length; k++)
        _card(
          id: startId + k,
          type: 'news',
          title: titles[k],
          lead: _leadSample,
          slug: slug,
        ),
    ];
  }

  static List<NewsCard> allNews() {
    final List<NewsCard> out = <NewsCard>[];
    int id = 1;
    for (final CategoryRef c in categories) {
      out.addAll(_cardsForCategory(c.slug, startId: id));
      id += 10;
    }
    return out;
  }

  static List<NewsCard> notes() => <NewsCard>[
        _card(
            id: 401,
            type: 'note',
            title: 'یادداشت: آینده گردشگری دریایی در گرو حفظ محیط زیست است',
            lead: 'نگاهی تحلیلی به فرصت‌ها و تهدیدهای پیش روی سواحل هرمزگان.',
            slug: 'persian-gulf'),
        _card(
            id: 402,
            type: 'note',
            title: 'یادداشت: بندر، موتور محرک اقتصاد جنوب',
            lead: 'چرا سرمایه‌گذاری در زیرساخت بندری برای استان حیاتی است؟',
            slug: 'economy'),
        _card(
            id: 403,
            type: 'note',
            title: 'یادداشت: میراث ناملموس هرمزگان را جدی بگیریم',
            lead: 'موسیقی، آیین و زبان بومی؛ سرمایه‌ای که در حال فراموشی است.',
            slug: 'culture'),
      ];

  static List<NewsCard> videos() => <NewsCard>[
        _card(id: 501, type: 'video', title: 'گزارش تصویری از جزیره هرمز؛ سرزمین رنگین‌کمان', lead: 'سفری به جزیره خاک‌های رنگی خلیج فارس.', slug: 'culture', duration: '۰۶:۱۲'),
        _card(id: 502, type: 'video', title: 'مستند کوتاه بندر شهید رجایی', lead: 'پشت صحنه بزرگ‌ترین بندر تجاری کشور.', slug: 'economy', duration: '۰۸:۴۵'),
        _card(id: 503, type: 'video', title: 'فینال فوتبال ساحلی هرمزگان', lead: 'خلاصه بازی قهرمانی.', slug: 'sports', duration: '۰۴:۳۰'),
        _card(id: 504, type: 'video', title: 'صید سنتی در سواحل قشم', lead: 'روایتی از زندگی صیادان جنوب.', slug: 'cities', duration: '۰۵:۲۰'),
        _card(id: 505, type: 'video', title: 'حیات وحش مانگروها', lead: 'جنگل‌های حرا و پرندگان مهاجر.', slug: 'social', duration: '۰۷:۰۰'),
        _card(id: 506, type: 'video', title: 'نمایشگاه صنایع دستی بندرعباس', lead: 'هنر دست هنرمندان جنوب.', slug: 'culture', duration: '۰۳:۴۸'),
      ];

  static List<NewsCard> podcasts() => <NewsCard>[
        _card(id: 601, type: 'podcast', title: 'پادکست ساحل | قسمت اول: اقتصاد بندر', lead: 'گفت‌وگو با کارشناسان حوزه بندر و دریا.', slug: 'economy', duration: '۳۲:۱۰'),
        _card(id: 602, type: 'podcast', title: 'پادکست ساحل | قسمت دوم: میراث خلیج فارس', lead: 'روایت‌هایی از فرهنگ بومی جنوب.', slug: 'persian-gulf', duration: '۲۸:۵۵'),
        _card(id: 603, type: 'podcast', title: 'پادکست ساحل | قسمت سوم: ورزش جنوب', lead: 'قهرمانان گمنام ورزش هرمزگان.', slug: 'sports', duration: '۲۴:۰۰'),
        _card(id: 604, type: 'podcast', title: 'پادکست ساحل | قسمت چهارم: سلامت', lead: 'نگاهی به وضعیت بهداشت در مناطق دورافتاده.', slug: 'social', duration: '۳۰:۱۵'),
        _card(id: 605, type: 'podcast', title: 'پادکست ساحل | قسمت پنجم: هنر و موسیقی', lead: 'موسیقی بندری و ریشه‌های آن.', slug: 'culture', duration: '۳۵:۴۰'),
      ];

  static List<NewsCard> galleries() => <NewsCard>[
        _card(id: 701, type: 'photo', title: 'گالری: غروب خلیج فارس', lead: '۱۲ قاب از زیبایی سواحل هرمزگان.', slug: 'persian-gulf'),
        _card(id: 702, type: 'photo', title: 'گالری: بازار محلی بندرعباس', lead: 'روزمرگی مردم جنوب در قاب تصویر.', slug: 'cities'),
        _card(id: 703, type: 'photo', title: 'گالری: جزیره هرمز', lead: 'رنگ‌های خاک و دریا.', slug: 'culture'),
        _card(id: 704, type: 'photo', title: 'گالری: جنگل‌های حرا', lead: 'طبیعت بکر قشم.', slug: 'social'),
      ];

  // --- Home payload --------------------------------------------------------

  static HomePayload home() {
    final List<NewsCard> news = allNews();
    return HomePayload(
      breaking: <NewsCard>[
        _card(id: 900, type: 'news', title: 'فوری | افتتاح فاز جدید بندر شهید رجایی با حضور وزیر', lead: '', slug: 'economy'),
        _card(id: 901, type: 'news', title: 'فوری | هشدار هواشناسی درباره وزش باد شدید در سواحل هرمزگان', lead: '', slug: 'social'),
        _card(id: 902, type: 'news', title: 'فوری | صعود تیم هرمزگان به فینال مسابقات کشوری', lead: '', slug: 'sports'),
      ],
      slider: news.take(5).toList(),
      latest: news.reversed.take(8).toList(),
      boxes: <HomeBox>[
        for (final CategoryRef c in categories.take(6))
          HomeBox(
            title: c.title,
            slug: c.slug,
            items: news.where((n) => n.category?.slug == c.slug).toList(),
          ),
      ],
      videos: videos(),
      podcasts: podcasts(),
      galleries: galleries(),
      latestIssue: publications().first,
      prices: prices(),
      live: live(),
    );
  }

  // --- Publications (newspaper archive) ------------------------------------

  static List<Publication> publications({int? year}) => <Publication>[
        for (int k = 0; k < 12; k++)
          Publication(
            id: 5000 + k,
            title: 'روزنامه صبح ساحل — شماره ${4210 - k}',
            coverUrl: _img(3000 + k),
            dateJalali: _dates[k % _dates.length],
            type: 'روزنامه',
            pdfUrl: 'https://sobhesahel.ir/pdf/${4210 - k}.pdf',
            number: '${4210 - k}',
          ),
      ];

  // --- Prices --------------------------------------------------------------

  static List<PriceItem> prices() => const <PriceItem>[
        PriceItem(title: 'دلار', value: '۵۸٬۷۰۰', change: 0.4, unit: 'تومان'),
        PriceItem(title: 'یورو', value: '۶۳٬۲۰۰', change: -0.2, unit: 'تومان'),
        PriceItem(title: 'سکه امامی', value: '۴۱٬۲۵۰٬۰۰۰', change: 1.1, unit: 'تومان'),
        PriceItem(title: 'طلای ۱۸ عیار', value: '۳٬۴۸۰٬۰۰۰', change: 0.8, unit: 'تومان'),
        PriceItem(title: 'میگو (کیلو)', value: '۴۸۰٬۰۰۰', change: -0.5, unit: 'تومان'),
      ];

  // --- Live ----------------------------------------------------------------

  static List<LiveStream> live() => const <LiveStream>[
        LiveStream(
          id: 1,
          title: 'پخش زنده شورای شهر بندرعباس',
          url: 'https://sobhesahel.ir/live/council',
          isLive: true,
        ),
      ];

  // --- Article detail ------------------------------------------------------

  static const String _bodyHtml = '''
<p>به گزارش خبرنگار پایگاه خبری صبح ساحل، این رویداد صبح امروز با حضور مسئولان ارشد استانی، فعالان اقتصادی و جمعی از اهالی رسانه در محل استانداری هرمزگان برگزار شد.</p>
<p>در این نشست، بر ضرورت بهره‌گیری از ظرفیت‌های بی‌نظیر سواحل مکران و خلیج فارس تأکید و مقرر شد کارگروهی ویژه برای پیگیری مطالبات مردم منطقه تشکیل شود.</p>
<h2>مهم‌ترین محورهای مطرح‌شده</h2>
<p>توسعه زیرساخت‌های بندری، حمایت از تولید داخلی و ایجاد اشتغال پایدار برای جوانان از جمله موضوعاتی بود که مورد بحث و بررسی قرار گرفت.</p>
<blockquote>استان هرمزگان با داشتن طولانی‌ترین مرز آبی کشور، دروازه اقتصاد دریامحور ایران است.</blockquote>
<img src="https://picsum.photos/seed/ssbody/800/450" alt="نمایی از بندر" />
<p>در پایان این مراسم، از تعدادی از فعالان برتر حوزه دریایی استان با اهدای لوح تقدیر قدردانی شد و نقشه راه توسعه یک‌ساله منطقه رونمایی گردید.</p>
''';

  static ArticleDetail articleDetail(String type, String code) {
    final List<NewsCard> pool = <NewsCard>[
      ...allNews(),
      ...notes(),
      ...videos(),
      ...podcasts(),
      ...galleries(),
      ...home().breaking,
    ];
    final NewsCard card = pool.firstWhere(
      (c) => c.code == code && c.type == type,
      orElse: () => pool.firstWhere((c) => c.code == code,
          orElse: () => allNews().first),
    );
    final String slug = card.category?.slug ?? 'politics';
    final bool isVideo = type == 'video';
    final bool isPodcast = type == 'podcast';
    final bool isPhoto = type == 'photo';
    return ArticleDetail(
      id: card.id,
      code: card.code,
      type: type,
      rotitr: type == 'news' ? 'اختصاصی صبح ساحل' : null,
      title: card.title,
      lead: card.lead.isEmpty
          ? _leadSample
          : card.lead,
      bodyHtml: _bodyHtml,
      subtitles: type == 'news'
          ? const <String>[
              'کارگروه ویژه توسعه سواحل تشکیل می‌شود',
              'تأکید بر حمایت از تولید داخلی',
            ]
          : const <String>[],
      imageUrl: card.imageUrl,
      images: <String>[_img(card.id + 1), _img(card.id + 2)],
      mediaUrl: isVideo
          ? 'https://sobhesahel.ir/video/${card.code}.mp4'
          : isPodcast
              ? 'https://sobhesahel.ir/audio/${card.code}.mp3'
              : null,
      category: card.category,
      tags: <String>['هرمزگان', slug == 'economy' ? 'اقتصاد' : 'خلیج فارس', 'صبح ساحل'],
      author: AuthorCard(
        id: 10 + (card.id % 6),
        userType: 'author',
        name: card.author ?? _authors[0],
        avatarUrl: _avatar(card.id),
        bio: 'خبرنگار و تحلیلگر حوزه ${card.category?.title ?? 'خبر'} در پایگاه خبری صبح ساحل.',
        url: '/author/author/${10 + (card.id % 6)}',
        postsCount: 42,
      ),
      publishedAtJalali: card.publishedAtJalali,
      visits: card.visits,
      related: pool
          .where((c) => c.category?.slug == slug && c.code != code)
          .take(4)
          .toList(),
      gallery: isPhoto
          ? <String>[
              _img(card.id + 10),
              _img(card.id + 11),
              _img(card.id + 12),
              _img(card.id + 13),
              _img(card.id + 14),
              _img(card.id + 15),
            ]
          : const <String>[],
      videoEmbed: isVideo ? 'https://sobhesahel.ir/embed/${card.code}' : null,
      audioUrl:
          isPodcast ? 'https://sobhesahel.ir/audio/${card.code}.mp3' : null,
      showComments: true,
      commentsCount: 3,
      bookmarked: card.bookmarked,
    );
  }

  static List<CommentItem> comments() => const <CommentItem>[
        CommentItem(
          id: 1,
          name: 'محمد از بندرعباس',
          body: 'خبر خوبیه، امیدوارم این پروژه‌ها واقعاً به نفع مردم منطقه تموم بشه.',
          createdAtJalali: '۳۱ تیر ۱۴۰۴',
        ),
        CommentItem(
          id: 2,
          name: 'سمیرا',
          body: 'گزارش کاملی بود، ممنون از تیم صبح ساحل.',
          createdAtJalali: '۳۱ تیر ۱۴۰۴',
        ),
        CommentItem(
          id: 3,
          name: 'کاربر مهمان',
          body: 'کاش به وضعیت جاده‌های شرق استان هم بیشتر پرداخته بشه.',
          createdAtJalali: '۳۰ تیر ۱۴۰۴',
        ),
      ];

  // --- Authors -------------------------------------------------------------

  static AuthorProfile authorProfile(String userType, int id) {
    final String name = _authors[id % _authors.length];
    final List<NewsCard> posts =
        allNews().where((n) => n.author == name).toList();
    return AuthorProfile(
      author: AuthorCard(
        id: id,
        userType: userType,
        name: name,
        avatarUrl: _avatar(id),
        bio: 'خبرنگار پایگاه خبری صبح ساحل با تمرکز بر اخبار استان هرمزگان و خلیج فارس.',
        url: '/author/$userType/$id',
        postsCount: posts.isEmpty ? 12 : posts.length,
      ),
      posts: Paged<NewsCard>(
        items: posts.isEmpty ? allNews().take(6).toList() : posts,
        currentPage: 1,
        lastPage: 1,
      ),
    );
  }

  // --- Search --------------------------------------------------------------

  static SearchResults search(String q, {String? type, String? category}) {
    final String query = q.trim();
    Iterable<NewsCard> pool = <NewsCard>[
      ...allNews(),
      ...notes(),
      ...videos(),
      ...podcasts(),
      ...galleries(),
    ];
    if (type != null && type.isNotEmpty) {
      pool = pool.where((c) => c.type == type);
    }
    if (category != null && category.isNotEmpty) {
      pool = pool.where((c) => c.category?.slug == category);
    }
    final List<NewsCard> posts = query.isEmpty
        ? pool.take(10).toList()
        : pool
            .where((c) =>
                c.title.contains(query) ||
                c.lead.contains(query) ||
                (c.category?.title.contains(query) ?? false))
            .toList();
    final List<AuthorCard> authors = query.isEmpty
        ? const <AuthorCard>[]
        : <AuthorCard>[
            for (int k = 0; k < _authors.length; k++)
              if (_authors[k].contains(query))
                AuthorCard(
                  id: 10 + k,
                  userType: 'author',
                  name: _authors[k],
                  avatarUrl: _avatar(10 + k),
                  bio: 'خبرنگار صبح ساحل',
                  url: '/author/author/${10 + k}',
                  postsCount: 20 + k,
                ),
          ];
    return SearchResults(posts: posts, authors: authors);
  }

  static Paged<NewsCard> categoryList(String slug, {int page = 1}) {
    final List<NewsCard> base =
        allNews().where((n) => n.category?.slug == slug).toList();
    final List<NewsCard> items = base.isEmpty ? allNews().take(6).toList() : base;
    return Paged<NewsCard>(items: items, currentPage: page, lastPage: 1);
  }

  static Paged<NewsCard> typeList(String type, {String? category, int page = 1}) {
    List<NewsCard> base;
    switch (type) {
      case 'note':
        base = notes();
        break;
      case 'video':
        base = videos();
        break;
      case 'podcast':
        base = podcasts();
        break;
      case 'photo':
        base = galleries();
        break;
      default:
        base = allNews();
    }
    if (category != null && category.isNotEmpty) {
      base = base.where((c) => c.category?.slug == category).toList();
      if (base.isEmpty) base = allNews().where((c) => c.category?.slug == category).toList();
    }
    return Paged<NewsCard>(items: base, currentPage: page, lastPage: 1);
  }
}
