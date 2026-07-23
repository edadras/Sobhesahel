// Realistic Persian sample data powering the app in demo (useMock) mode.
// Everything here mirrors the wording of the docs/templates/user-ui pages.

import '../models/models.dart';

class MockData {
  const MockData._();

  static const Member member = Member(
    id: 1,
    firstName: 'سحر',
    lastName: 'موسوی',
    fullName: 'سحر موسوی',
    mobile: '09123456789',
    email: 'sahar@example.com',
    avatarUrl: null,
    city: 'بندرعباس',
    birthDate: '1370-03-15',
    bio: 'علاقه‌مند به اخبار اقتصادی و گردشگری هرمزگان.',
    locale: 'fa',
    isActive: true,
    createdAt: '2023-05-01T08:00:00Z',
  );

  static const MemberLevel goldLevel = MemberLevel(
    key: 'gold',
    title: 'طلایی',
    progressPercent: 72,
    nextThreshold: 10000,
  );

  static const Streak streak = Streak(
    currentDays: 12,
    longestDays: 28,
    nextMilestone: 14,
  );

  static const ActiveSubscription subscription = ActiveSubscription(
    planTitle: 'پلن طلایی ماهانه',
    endsAtJalali: '۴ تیر ۱۴۰۵',
    daysLeft: 18,
    status: 'active',
  );

  static List<Mission> missions() => const <Mission>[
        Mission(
          code: 'read_news',
          title: '۳ خبر بخوان',
          points: 6,
          progress: 3,
          goal: 3,
          completed: true,
        ),
        Mission(
          code: 'comment',
          title: '۱ نظر ثبت کن',
          points: 5,
          progress: 1,
          goal: 1,
          completed: true,
        ),
        Mission(
          code: 'share',
          title: '۱ خبر به اشتراک بگذار',
          points: 10,
          progress: 0,
          goal: 1,
          completed: false,
        ),
      ];

  static List<PointTransaction> transactions() => const <PointTransaction>[
        PointTransaction(
          id: 1,
          points: 5,
          balanceAfter: 7240,
          ruleCode: 'daily_login',
          description: 'ورود روزانه',
          createdAt: '2026-06-07T06:30:00Z',
          createdAtJalali: '۱۷ خرداد',
        ),
        PointTransaction(
          id: 2,
          points: 5,
          balanceAfter: 7235,
          ruleCode: 'read_full',
          description: 'مطالعه کامل مقاله اقتصادی',
          createdAt: '2026-06-07T07:10:00Z',
          createdAtJalali: '۱۷ خرداد',
        ),
        PointTransaction(
          id: 3,
          points: -300,
          balanceAfter: 6930,
          ruleCode: 'shop_purchase',
          description: 'خرید کتاب «تاریخ بنادر»',
          createdAt: '2026-06-05T12:00:00Z',
          createdAtJalali: '۱۵ خرداد',
        ),
        PointTransaction(
          id: 4,
          points: 10,
          balanceAfter: 6940,
          ruleCode: 'share',
          description: 'اشتراک‌گذاری خبر گردشگری',
          createdAt: '2026-06-04T09:00:00Z',
          createdAtJalali: '۱۴ خرداد',
        ),
        PointTransaction(
          id: 5,
          points: 50,
          balanceAfter: 6890,
          ruleCode: 'referral',
          description: 'دعوت دوست (مریم ر.)',
          createdAt: '2026-06-02T18:00:00Z',
          createdAtJalali: '۱۲ خرداد',
        ),
        PointTransaction(
          id: 6,
          points: -100,
          balanceAfter: 6840,
          ruleCode: 'raffle',
          description: 'شرکت در قرعه‌کشی ماهانه',
          createdAt: '2026-05-31T10:00:00Z',
          createdAtJalali: '۱۰ خرداد',
        ),
      ];

  static List<PointsRule> earnRules() => const <PointsRule>[
        PointsRule(code: 'register', title: 'ثبت‌نام', points: '50'),
        PointsRule(code: 'profile', title: 'تکمیل پروفایل', points: '50'),
        PointsRule(code: 'daily_login', title: 'ورود روزانه', points: '5'),
        PointsRule(code: 'read_news', title: 'مطالعه خبر', points: '2'),
        PointsRule(code: 'read_full', title: 'مطالعه کامل مقاله', points: '5'),
        PointsRule(code: 'share', title: 'اشتراک‌گذاری خبر', points: '10'),
        PointsRule(code: 'comment', title: 'ثبت نظر', points: '5'),
        PointsRule(code: 'subscribe', title: 'خرید اشتراک', points: '100'),
        PointsRule(code: 'referral', title: 'دعوت دوستان', points: '50'),
      ];

  static List<PointsRule> spendRules() => const <PointsRule>[
        PointsRule(code: 'sub_day', title: '۱ روز اشتراک ویژه', points: '100'),
        PointsRule(code: 'sub_week', title: '۱ هفته اشتراک', points: '500'),
        PointsRule(code: 'pdf', title: 'PDF روزنامه', points: '50'),
        PointsRule(code: 'ebook', title: 'کتاب الکترونیکی', points: '300'),
        PointsRule(code: 'coupon', title: 'کد تخفیف فروشگاه', points: '200'),
        PointsRule(code: 'raffle', title: 'شرکت در قرعه‌کشی', points: '100'),
      ];

  static const WheelInfo wheel = WheelInfo(freeAvailable: true, cost: 50);

  static List<Badge> badges() => const <Badge>[
        Badge(
          code: 'early_bird',
          title: 'سحرخیز',
          description: 'ورود در ۷ روز متوالی',
          icon: 'sun',
          earned: true,
          awardedAtJalali: '۲ خرداد ۱۴۰۵',
          conditionText: 'ورود در ۷ روز پیاپی',
        ),
        Badge(
          code: 'bookworm',
          title: 'کتاب‌خوان',
          description: 'مطالعه ۵۰ مقاله',
          icon: 'book',
          earned: true,
          awardedAtJalali: '۲۰ اردیبهشت ۱۴۰۵',
          conditionText: 'مطالعه کامل ۵۰ مقاله',
        ),
        Badge(
          code: 'influencer',
          title: 'خبررسان',
          description: 'اشتراک‌گذاری ۲۰ خبر',
          icon: 'share',
          earned: false,
          conditionText: 'اشتراک‌گذاری ۲۰ خبر',
        ),
        Badge(
          code: 'collector',
          title: 'گنجینه‌دار',
          description: 'خرید ۵ محصول از فروشگاه',
          icon: 'bag',
          earned: false,
          conditionText: 'خرید ۵ محصول از فروشگاه',
        ),
        Badge(
          code: 'patron',
          title: 'حامی',
          description: 'اشتراک فعال ۶ ماهه',
          icon: 'crown',
          earned: false,
          conditionText: 'داشتن اشتراک فعال به‌مدت ۶ ماه',
        ),
        Badge(
          code: 'explorer',
          title: 'کاوشگر هرمزگان',
          description: 'مطالعه ۱۰ مطلب گردشگری',
          icon: 'pin',
          earned: true,
          awardedAtJalali: '۱ خرداد ۱۴۰۵',
          conditionText: 'مطالعه ۱۰ مطلب گردشگری',
        ),
      ];

  static List<Product> products() => const <Product>[
        Product(
          id: 1,
          title: 'کتاب الکترونیکی: تاریخ بنادر جنوب ایران',
          slug: 'history-of-southern-ports',
          imageUrl: null,
          description:
              'مروری جامع بر تاریخ بنادر خلیج فارس و نقش آن‌ها در تجارت منطقه.',
          price: 85000,
          pointsPrice: 300,
          type: 'ebook',
          inStock: true,
        ),
        Product(
          id: 2,
          title: 'گزارش اقتصادی: چشم‌انداز تجارت هرمزگان ۱۴۰۵',
          slug: 'hormozgan-trade-outlook-1405',
          imageUrl: null,
          description: 'تحلیل داده‌محور از روند صادرات و واردات استان هرمزگان.',
          price: 120000,
          pointsPrice: null,
          type: 'report',
          inStock: true,
        ),
        Product(
          id: 3,
          title: 'آرشیو PDF روزنامه — اسفند ۱۴۰۳',
          slug: 'newspaper-archive-esfand-1403',
          imageUrl: null,
          description: 'مجموعه کامل شماره‌های اسفند ۱۴۰۳ روزنامه صبح ساحل.',
          price: 20000,
          pointsPrice: 50,
          type: 'pdf',
          inStock: true,
        ),
        Product(
          id: 4,
          title: 'راهنمای گردشگری جزیره قشم',
          slug: 'qeshm-tourism-guide',
          imageUrl: null,
          description: 'راهنمای تصویری مقاصد دیدنی و بوم‌گردی جزیره قشم.',
          price: 60000,
          pointsPrice: 220,
          type: 'ebook',
          inStock: false,
        ),
      ];

  static List<Order> orders() => const <Order>[
        Order(
          id: 101,
          productTitle: 'کتاب الکترونیکی: تاریخ بنادر جنوب ایران',
          amount: 300,
          paidWith: 'points',
          status: 'paid',
          createdAtJalali: '۱۵ خرداد ۱۴۰۵',
          downloadUrl: 'https://example.com/download/101',
        ),
        Order(
          id: 102,
          productTitle: 'آرشیو PDF روزنامه — اسفند ۱۴۰۳',
          amount: 50,
          paidWith: 'points',
          status: 'paid',
          createdAtJalali: '۱۰ خرداد ۱۴۰۵',
          downloadUrl: 'https://example.com/download/102',
        ),
        Order(
          id: 103,
          productTitle: 'گزارش اقتصادی: چشم‌انداز تجارت هرمزگان ۱۴۰۵',
          amount: 120000,
          paidWith: 'cash',
          status: 'processing',
          createdAtJalali: '۸ خرداد ۱۴۰۵',
          downloadUrl: null,
        ),
      ];

  static const ActiveSubscription currentSub = subscription;

  static List<SubscriptionPlan> plans() => const <SubscriptionPlan>[
        SubscriptionPlan(
          id: 1,
          name: 'برنزی ماهانه',
          durationDays: 30,
          price: 49000,
          pointsPrice: null,
          features: <String>[
            'دسترسی به اخبار ویژه',
            'حذف تبلیغات',
            'ذخیره نامحدود اخبار',
          ],
          badge: null,
        ),
        SubscriptionPlan(
          id: 2,
          name: 'طلایی ماهانه',
          durationDays: 30,
          price: 89000,
          pointsPrice: 4000,
          features: <String>[
            'تمام امکانات برنزی',
            'دانلود PDF روزنامه',
            'محتوای اختصاصی اعضا',
            'امتیاز ۱.۵ برابر',
          ],
          badge: 'محبوب‌ترین',
        ),
        SubscriptionPlan(
          id: 3,
          name: 'پلاتینیوم سالانه',
          durationDays: 365,
          price: 890000,
          pointsPrice: null,
          features: <String>[
            'تمام امکانات طلایی',
            'دسترسی زودهنگام به گزارش‌ها',
            'هدیه تولد',
            'پشتیبانی اختصاصی',
          ],
          badge: null,
        ),
      ];

  static List<ArchiveIssue> archive() => const <ArchiveIssue>[
        ArchiveIssue(
          id: 1,
          title: 'روزنامه صبح ساحل — ۱۷ خرداد ۱۴۰۵',
          coverUrl: null,
          dateJalali: '۱۷ خرداد ۱۴۰۵',
          type: 'daily',
          accessible: true,
          unlockCost: 50,
        ),
        ArchiveIssue(
          id: 2,
          title: 'روزنامه صبح ساحل — ۱۶ خرداد ۱۴۰۵',
          coverUrl: null,
          dateJalali: '۱۶ خرداد ۱۴۰۵',
          type: 'daily',
          accessible: true,
          unlockCost: 50,
        ),
        ArchiveIssue(
          id: 3,
          title: 'ویژه‌نامه نوروز ۱۴۰۵',
          coverUrl: null,
          dateJalali: '۱ فروردین ۱۴۰۵',
          type: 'special',
          accessible: false,
          unlockCost: 100,
        ),
        ArchiveIssue(
          id: 4,
          title: 'روزنامه صبح ساحل — ۲۹ اسفند ۱۴۰۳',
          coverUrl: null,
          dateJalali: '۲۹ اسفند ۱۴۰۳',
          type: 'daily',
          accessible: false,
          unlockCost: 50,
        ),
      ];

  static List<Bookmark> bookmarks() => const <Bookmark>[
        Bookmark(
          id: 1,
          type: 'news',
          itemId: 501,
          title: 'رشد ۲۰ درصدی صادرات از بندر شهید رجایی در بهار امسال',
          imageUrl: null,
          url: 'https://sobhesahel.ir/news/501',
          dateJalali: '۲ ساعت پیش',
        ),
        Bookmark(
          id: 2,
          type: 'news',
          itemId: 502,
          title: 'جزیره هرمز؛ مقصد نوروزی که امسال رکورد زد',
          imageUrl: null,
          url: 'https://sobhesahel.ir/news/502',
          dateJalali: '۵ ساعت پیش',
        ),
        Bookmark(
          id: 3,
          type: 'news',
          itemId: 503,
          title: 'افتتاح فاز جدید آب‌شیرین‌کن بندرعباس تا پایان تیر',
          imageUrl: null,
          url: 'https://sobhesahel.ir/news/503',
          dateJalali: 'دیروز',
        ),
      ];

  static List<Author> authors() => const <Author>[
        Author(
          id: 1,
          name: 'دکتر علی احمدی',
          type: 'نویسنده اقتصادی',
          avatarUrl: null,
          url: 'https://sobhesahel.ir/author/1',
          following: true,
        ),
        Author(
          id: 2,
          name: 'مریم رستگار',
          type: 'خبرنگار گردشگری',
          avatarUrl: null,
          url: 'https://sobhesahel.ir/author/2',
          following: true,
        ),
        Author(
          id: 3,
          name: 'حسین دریانورد',
          type: 'تحلیلگر دریایی',
          avatarUrl: null,
          url: 'https://sobhesahel.ir/author/3',
          following: true,
        ),
      ];

  static List<ReadingItem> reading() => const <ReadingItem>[
        ReadingItem(
          newsId: 601,
          title: 'پرونده ویژه: آینده گردشگری دریایی هرمزگان',
          imageUrl: null,
          progressPercent: 65,
          updatedAtJalali: 'امروز',
          url: 'https://sobhesahel.ir/news/601',
        ),
        ReadingItem(
          newsId: 602,
          title: 'گزارش میدانی از بازار ماهی‌فروشان بندرعباس',
          imageUrl: null,
          progressPercent: 30,
          updatedAtJalali: 'دیروز',
          url: 'https://sobhesahel.ir/news/602',
        ),
      ];

  static List<TourismItem> tourism() => const <TourismItem>[
        TourismItem(
          id: 701,
          title: 'جزیره هرمز؛ رنگین‌کمانی از خاک و دریا',
          imageUrl: null,
          excerpt:
              'راهنمای کامل بازدید از جزیره هرمز، از دره تندیس‌ها تا ساحل نقره‌ای.',
          url: 'https://sobhesahel.ir/tourism/701',
          bookmarked: true,
        ),
        TourismItem(
          id: 702,
          title: 'ژئوپارک قشم؛ نخستین ژئوپارک خاورمیانه',
          imageUrl: null,
          excerpt: 'کاوش در غار نمکدان، دره ستاره‌ها و جنگل‌های حرا.',
          url: 'https://sobhesahel.ir/tourism/702',
          bookmarked: false,
        ),
        TourismItem(
          id: 703,
          title: 'بندر لافت؛ گنجینه بادگیرها',
          imageUrl: null,
          excerpt: 'گشتی در بافت تاریخی بندر لافت و آب‌انبارهای کهن آن.',
          url: 'https://sobhesahel.ir/tourism/703',
          bookmarked: false,
        ),
      ];

  static List<AppNotification> notifications() => const <AppNotification>[
        AppNotification(
          id: 1,
          title: 'امتیاز روزانه دریافت شد',
          body: 'با ورود امروز ۵ امتیاز به حساب شما اضافه شد.',
          icon: 'coins',
          url: null,
          read: false,
          createdAtJalali: 'همین حالا',
        ),
        AppNotification(
          id: 2,
          title: 'ماموریت جدید در دسترس است',
          body: 'یک خبر به اشتراک بگذار و ۱۰ امتیاز بگیر.',
          icon: 'gift',
          url: null,
          read: false,
          createdAtJalali: '۱ ساعت پیش',
        ),
        AppNotification(
          id: 3,
          title: 'اشتراک شما به‌زودی تمدید می‌شود',
          body: 'پلن طلایی ماهانه در ۴ تیر تمدید خواهد شد.',
          icon: 'crown',
          url: null,
          read: false,
          createdAtJalali: 'دیروز',
        ),
        AppNotification(
          id: 4,
          title: 'خبر فوری هرمزگان',
          body: 'افتتاح فاز جدید آب‌شیرین‌کن بندرعباس.',
          icon: 'news',
          url: null,
          read: true,
          createdAtJalali: '۲ روز پیش',
        ),
      ];

  static const NotificationPreferences notifPrefs = NotificationPreferences(
    points: true,
    subscription: true,
    announcements: false,
  );

  static DashboardData dashboard() => DashboardData(
        member: member,
        points: const DashboardPoints(balance: 7240, level: goldLevel),
        streak: streak,
        subscription: subscription,
        missionsToday: missions(),
        recentTransactions: transactions().take(3).toList(),
        badgesCount: 3,
        unreadNotifications: 3,
        todayPointsEarned: 5,
      );
}
