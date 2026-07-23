// Typed DTOs for the Sobhe Sahel member API (see docs/MEMBER_API.md).
//
// Every model has a tolerant `fromJson` (null-safe, defaulted) so partial or
// guarded backend responses never crash the app. `_str`, `_int`, `_bool`,
// `_double` are defensive coercers used throughout.

String _str(dynamic v, [String fallback = '']) => v?.toString() ?? fallback;

int _int(dynamic v, [int fallback = 0]) {
  if (v is int) return v;
  if (v is double) return v.round();
  if (v is String) return int.tryParse(v) ?? fallback;
  return fallback;
}

double _double(dynamic v, [double fallback = 0]) {
  if (v is num) return v.toDouble();
  if (v is String) return double.tryParse(v) ?? fallback;
  return fallback;
}

bool _bool(dynamic v, [bool fallback = false]) {
  if (v is bool) return v;
  if (v is num) return v != 0;
  if (v is String) return v == 'true' || v == '1';
  return fallback;
}

String? _strOrNull(dynamic v) => v == null ? null : v.toString();

List<Map<String, dynamic>> _list(dynamic v) {
  if (v is List) {
    return v.whereType<Map>().map((e) => e.cast<String, dynamic>()).toList();
  }
  return const <Map<String, dynamic>>[];
}

// ---------------------------------------------------------------------------
// Member
// ---------------------------------------------------------------------------

class Member {
  const Member({
    required this.id,
    required this.firstName,
    required this.lastName,
    required this.fullName,
    required this.mobile,
    this.email,
    this.avatarUrl,
    this.city,
    this.birthDate,
    this.bio,
    this.locale = 'fa',
    this.isActive = true,
    this.createdAt,
  });

  final int id;
  final String firstName;
  final String lastName;
  final String fullName;
  final String mobile;
  final String? email;
  final String? avatarUrl;
  final String? city;
  final String? birthDate;
  final String? bio;
  final String locale;
  final bool isActive;
  final String? createdAt;

  /// Initials used for the fallback avatar.
  String get initials {
    final String a = firstName.isNotEmpty ? firstName[0] : '';
    final String b = lastName.isNotEmpty ? lastName[0] : '';
    final String i = (a + b).trim();
    return i.isEmpty ? (fullName.isNotEmpty ? fullName[0] : '?') : i;
  }

  factory Member.fromJson(Map<String, dynamic> j) => Member(
        id: _int(j['id']),
        firstName: _str(j['first_name']),
        lastName: _str(j['last_name']),
        fullName: _str(j['full_name']),
        mobile: _str(j['mobile']),
        email: _strOrNull(j['email']),
        avatarUrl: _strOrNull(j['avatar_url']),
        city: _strOrNull(j['city']),
        birthDate: _strOrNull(j['birth_date']),
        bio: _strOrNull(j['bio']),
        locale: _str(j['locale'], 'fa'),
        isActive: _bool(j['is_active'], true),
        createdAt: _strOrNull(j['created_at']),
      );

  Member copyWith({
    String? firstName,
    String? lastName,
    String? fullName,
    String? email,
    String? avatarUrl,
    String? city,
    String? birthDate,
    String? bio,
    String? locale,
  }) =>
      Member(
        id: id,
        firstName: firstName ?? this.firstName,
        lastName: lastName ?? this.lastName,
        fullName: fullName ?? this.fullName,
        mobile: mobile,
        email: email ?? this.email,
        avatarUrl: avatarUrl ?? this.avatarUrl,
        city: city ?? this.city,
        birthDate: birthDate ?? this.birthDate,
        bio: bio ?? this.bio,
        locale: locale ?? this.locale,
        isActive: isActive,
        createdAt: createdAt,
      );
}

/// The four membership tiers, mirroring the site's `--tier-*` colours.
enum MemberTier { bronze, silver, gold, platinum }

MemberTier tierFromKey(String key) {
  switch (key.toLowerCase()) {
    case 'silver':
      return MemberTier.silver;
    case 'gold':
      return MemberTier.gold;
    case 'platinum':
      return MemberTier.platinum;
    default:
      return MemberTier.bronze;
  }
}

// ---------------------------------------------------------------------------
// Points & levels
// ---------------------------------------------------------------------------

class MemberLevel {
  const MemberLevel({
    required this.key,
    required this.title,
    required this.progressPercent,
    required this.nextThreshold,
  });

  final String key;
  final String title;
  final double progressPercent;
  final int nextThreshold;

  MemberTier get tier => tierFromKey(key);

  factory MemberLevel.fromJson(Map<String, dynamic> j) => MemberLevel(
        key: _str(j['key'], 'bronze'),
        title: _str(j['title']),
        progressPercent: _double(j['progress_percent']),
        nextThreshold: _int(j['next_threshold']),
      );
}

class PointsRule {
  const PointsRule({
    required this.code,
    required this.title,
    required this.points,
  });

  final String code;
  final String title;

  /// Kept as a String because some rules are expressed as ranges
  /// (e.g. "10 / هر ۱۰۰هزار تومان").
  final String points;

  factory PointsRule.fromJson(Map<String, dynamic> j) => PointsRule(
        code: _str(j['code']),
        title: _str(j['title']),
        points: _str(j['points']),
      );
}

class PointsOverview {
  const PointsOverview({
    required this.balance,
    required this.level,
    required this.earnRules,
    required this.spendRules,
  });

  final int balance;
  final MemberLevel level;
  final List<PointsRule> earnRules;
  final List<PointsRule> spendRules;

  factory PointsOverview.fromJson(Map<String, dynamic> j) => PointsOverview(
        balance: _int(j['balance']),
        level: MemberLevel.fromJson(
            (j['level'] as Map?)?.cast<String, dynamic>() ?? const {}),
        earnRules: _list(j['earn_rules']).map(PointsRule.fromJson).toList(),
        spendRules: _list(j['spend_rules']).map(PointsRule.fromJson).toList(),
      );
}

class PointTransaction {
  const PointTransaction({
    required this.id,
    required this.points,
    required this.balanceAfter,
    required this.ruleCode,
    required this.description,
    required this.createdAt,
    required this.createdAtJalali,
  });

  final int id;
  final int points;
  final int balanceAfter;
  final String ruleCode;
  final String description;
  final String createdAt;
  final String createdAtJalali;

  bool get isEarn => points >= 0;

  factory PointTransaction.fromJson(Map<String, dynamic> j) => PointTransaction(
        id: _int(j['id']),
        points: _int(j['points']),
        balanceAfter: _int(j['balance_after']),
        ruleCode: _str(j['rule_code']),
        description: _str(j['description']),
        createdAt: _str(j['created_at']),
        createdAtJalali: _str(j['created_at_jalali']),
      );
}

// ---------------------------------------------------------------------------
// Club: streak, missions, wheel
// ---------------------------------------------------------------------------

class Streak {
  const Streak({
    required this.currentDays,
    required this.longestDays,
    required this.nextMilestone,
  });

  final int currentDays;
  final int longestDays;
  final int nextMilestone;

  factory Streak.fromJson(Map<String, dynamic> j) => Streak(
        currentDays: _int(j['current_days']),
        longestDays: _int(j['longest_days']),
        nextMilestone: _int(j['next_milestone']),
      );
}

class Mission {
  const Mission({
    required this.code,
    required this.title,
    required this.points,
    required this.progress,
    required this.goal,
    required this.completed,
  });

  final String code;
  final String title;
  final int points;
  final int progress;
  final int goal;
  final bool completed;

  factory Mission.fromJson(Map<String, dynamic> j) => Mission(
        code: _str(j['code']),
        title: _str(j['title']),
        points: _int(j['points']),
        progress: _int(j['progress']),
        goal: _int(j['goal'], 1),
        completed: _bool(j['completed']),
      );
}

class WheelInfo {
  const WheelInfo({required this.freeAvailable, required this.cost});

  final bool freeAvailable;
  final int cost;

  factory WheelInfo.fromJson(Map<String, dynamic> j) => WheelInfo(
        freeAvailable: _bool(j['free_available']),
        cost: _int(j['cost']),
      );
}

class ClubData {
  const ClubData({
    required this.streak,
    required this.missions,
    required this.wheel,
  });

  final Streak streak;
  final List<Mission> missions;
  final WheelInfo wheel;

  factory ClubData.fromJson(Map<String, dynamic> j) => ClubData(
        streak: Streak.fromJson(
            (j['streak'] as Map?)?.cast<String, dynamic>() ?? const {}),
        missions: _list(j['missions']).map(Mission.fromJson).toList(),
        wheel: WheelInfo.fromJson(
            (j['wheel'] as Map?)?.cast<String, dynamic>() ?? const {}),
      );
}

class WheelPrize {
  const WheelPrize({
    required this.title,
    required this.type,
    required this.value,
    required this.pointsBalance,
  });

  final String title;
  final String type;
  final String value;
  final int pointsBalance;

  factory WheelPrize.fromJson(Map<String, dynamic> j) {
    final Map<String, dynamic> prize =
        (j['prize'] as Map?)?.cast<String, dynamic>() ?? j;
    return WheelPrize(
      title: _str(prize['title']),
      type: _str(prize['type']),
      value: _str(prize['value']),
      pointsBalance: _int(j['points_balance']),
    );
  }
}

// ---------------------------------------------------------------------------
// Badges
// ---------------------------------------------------------------------------

class Badge {
  const Badge({
    required this.code,
    required this.title,
    required this.description,
    required this.icon,
    required this.earned,
    this.awardedAtJalali,
    required this.conditionText,
  });

  final String code;
  final String title;
  final String description;
  final String icon;
  final bool earned;
  final String? awardedAtJalali;
  final String conditionText;

  factory Badge.fromJson(Map<String, dynamic> j) => Badge(
        code: _str(j['code']),
        title: _str(j['title']),
        description: _str(j['description']),
        icon: _str(j['icon']),
        earned: _bool(j['earned']),
        awardedAtJalali: _strOrNull(j['awarded_at_jalali']),
        conditionText: _str(j['condition_text']),
      );
}

// ---------------------------------------------------------------------------
// Shop
// ---------------------------------------------------------------------------

class Product {
  const Product({
    required this.id,
    required this.title,
    required this.slug,
    this.imageUrl,
    required this.description,
    required this.price,
    this.pointsPrice,
    required this.type,
    required this.inStock,
  });

  final int id;
  final String title;
  final String slug;
  final String? imageUrl;
  final String description;
  final int price;
  final int? pointsPrice;
  final String type;
  final bool inStock;

  factory Product.fromJson(Map<String, dynamic> j) => Product(
        id: _int(j['id']),
        title: _str(j['title']),
        slug: _str(j['slug']),
        imageUrl: _strOrNull(j['image_url']),
        description: _str(j['description']),
        price: _int(j['price']),
        pointsPrice: j['points_price'] == null ? null : _int(j['points_price']),
        type: _str(j['type']),
        inStock: _bool(j['in_stock'], true),
      );
}

class Order {
  const Order({
    required this.id,
    required this.productTitle,
    required this.amount,
    required this.paidWith,
    required this.status,
    required this.createdAtJalali,
    this.downloadUrl,
  });

  final int id;
  final String productTitle;
  final int amount;
  final String paidWith;
  final String status;
  final String createdAtJalali;
  final String? downloadUrl;

  factory Order.fromJson(Map<String, dynamic> j) => Order(
        id: _int(j['id']),
        productTitle: _str(j['product_title']),
        amount: _int(j['amount']),
        paidWith: _str(j['paid_with']),
        status: _str(j['status']),
        createdAtJalali: _str(j['created_at_jalali']),
        downloadUrl: _strOrNull(j['download_url']),
      );
}

// ---------------------------------------------------------------------------
// Subscription
// ---------------------------------------------------------------------------

class SubscriptionPlan {
  const SubscriptionPlan({
    required this.id,
    required this.name,
    required this.durationDays,
    required this.price,
    this.pointsPrice,
    required this.features,
    this.badge,
  });

  final int id;
  final String name;
  final int durationDays;
  final int price;
  final int? pointsPrice;
  final List<String> features;
  final String? badge;

  factory SubscriptionPlan.fromJson(Map<String, dynamic> j) => SubscriptionPlan(
        id: _int(j['id']),
        name: _str(j['name']),
        durationDays: _int(j['duration_days']),
        price: _int(j['price']),
        pointsPrice: j['points_price'] == null ? null : _int(j['points_price']),
        features: (j['features'] as List?)
                ?.map((e) => e.toString())
                .toList() ??
            const <String>[],
        badge: _strOrNull(j['badge']),
      );
}

class ActiveSubscription {
  const ActiveSubscription({
    required this.planTitle,
    required this.endsAtJalali,
    required this.daysLeft,
    required this.status,
  });

  final String planTitle;
  final String endsAtJalali;
  final int daysLeft;
  final String status;

  bool get isActive => status == 'active';

  factory ActiveSubscription.fromJson(Map<String, dynamic> j) =>
      ActiveSubscription(
        planTitle: _str(j['plan_title']),
        endsAtJalali: _str(j['ends_at_jalali']),
        daysLeft: _int(j['days_left']),
        status: _str(j['status'], 'active'),
      );
}

class SubscriptionOverview {
  const SubscriptionOverview({this.current, required this.plans});

  final ActiveSubscription? current;
  final List<SubscriptionPlan> plans;

  factory SubscriptionOverview.fromJson(Map<String, dynamic> j) =>
      SubscriptionOverview(
        current: j['current'] == null
            ? null
            : ActiveSubscription.fromJson(
                (j['current'] as Map).cast<String, dynamic>()),
        plans: _list(j['plans']).map(SubscriptionPlan.fromJson).toList(),
      );
}

// ---------------------------------------------------------------------------
// Archive
// ---------------------------------------------------------------------------

class ArchiveIssue {
  const ArchiveIssue({
    required this.id,
    required this.title,
    this.coverUrl,
    required this.dateJalali,
    required this.type,
    required this.accessible,
    required this.unlockCost,
  });

  final int id;
  final String title;
  final String? coverUrl;
  final String dateJalali;
  final String type;
  final bool accessible;
  final int unlockCost;

  factory ArchiveIssue.fromJson(Map<String, dynamic> j) => ArchiveIssue(
        id: _int(j['id']),
        title: _str(j['title']),
        coverUrl: _strOrNull(j['cover_url']),
        dateJalali: _str(j['date_jalali']),
        type: _str(j['type']),
        accessible: _bool(j['accessible']),
        unlockCost: _int(j['unlock_cost']),
      );
}

// ---------------------------------------------------------------------------
// Library: bookmarks, authors, reading
// ---------------------------------------------------------------------------

class Bookmark {
  const Bookmark({
    required this.id,
    required this.type,
    required this.itemId,
    required this.title,
    this.imageUrl,
    required this.url,
    required this.dateJalali,
  });

  final int id;
  final String type;
  final int itemId;
  final String title;
  final String? imageUrl;
  final String url;
  final String dateJalali;

  factory Bookmark.fromJson(Map<String, dynamic> j) {
    final Map<String, dynamic> b =
        (j['bookmarkable'] as Map?)?.cast<String, dynamic>() ?? const {};
    return Bookmark(
      id: _int(j['id']),
      type: _str(b['type']),
      itemId: _int(b['id']),
      title: _str(b['title']),
      imageUrl: _strOrNull(b['image_url']),
      url: _str(b['url']),
      dateJalali: _str(b['date_jalali']),
    );
  }
}

class Author {
  const Author({
    required this.id,
    required this.name,
    required this.type,
    this.avatarUrl,
    required this.url,
    this.following = true,
  });

  final int id;
  final String name;
  final String type;
  final String? avatarUrl;
  final String url;
  final bool following;

  factory Author.fromJson(Map<String, dynamic> j) => Author(
        id: _int(j['id']),
        name: _str(j['name']),
        type: _str(j['type']),
        avatarUrl: _strOrNull(j['avatar_url']),
        url: _str(j['url']),
        following: _bool(j['following'], true),
      );
}

class ReadingItem {
  const ReadingItem({
    required this.newsId,
    required this.title,
    this.imageUrl,
    required this.progressPercent,
    required this.updatedAtJalali,
    required this.url,
  });

  final int newsId;
  final String title;
  final String? imageUrl;
  final double progressPercent;
  final String updatedAtJalali;
  final String url;

  factory ReadingItem.fromJson(Map<String, dynamic> j) {
    final Map<String, dynamic> news =
        (j['news'] as Map?)?.cast<String, dynamic>() ?? const {};
    return ReadingItem(
      newsId: _int(news['id']),
      title: _str(news['title']),
      imageUrl: _strOrNull(news['image_url']),
      progressPercent: _double(j['progress_percent']),
      updatedAtJalali: _str(j['updated_at_jalali']),
      url: _str(news['url']),
    );
  }
}

// ---------------------------------------------------------------------------
// Tourism
// ---------------------------------------------------------------------------

class TourismItem {
  const TourismItem({
    required this.id,
    required this.title,
    this.imageUrl,
    required this.excerpt,
    required this.url,
    required this.bookmarked,
  });

  final int id;
  final String title;
  final String? imageUrl;
  final String excerpt;
  final String url;
  final bool bookmarked;

  factory TourismItem.fromJson(Map<String, dynamic> j) => TourismItem(
        id: _int(j['id']),
        title: _str(j['title']),
        imageUrl: _strOrNull(j['image_url']),
        excerpt: _str(j['excerpt']),
        url: _str(j['url']),
        bookmarked: _bool(j['bookmarked']),
      );
}

// ---------------------------------------------------------------------------
// Notifications
// ---------------------------------------------------------------------------

class AppNotification {
  const AppNotification({
    required this.id,
    required this.title,
    required this.body,
    required this.icon,
    this.url,
    required this.read,
    required this.createdAtJalali,
  });

  final int id;
  final String title;
  final String body;
  final String icon;
  final String? url;
  final bool read;
  final String createdAtJalali;

  factory AppNotification.fromJson(Map<String, dynamic> j) => AppNotification(
        id: _int(j['id']),
        title: _str(j['title']),
        body: _str(j['body']),
        icon: _str(j['icon']),
        url: _strOrNull(j['url']),
        read: _bool(j['read']),
        createdAtJalali: _str(j['created_at_jalali']),
      );
}

class NotificationPreferences {
  const NotificationPreferences({
    required this.points,
    required this.subscription,
    required this.announcements,
  });

  final bool points;
  final bool subscription;
  final bool announcements;

  factory NotificationPreferences.fromJson(Map<String, dynamic> j) =>
      NotificationPreferences(
        points: _bool(j['points'], true),
        subscription: _bool(j['subscription'], true),
        announcements: _bool(j['announcements'], true),
      );

  Map<String, dynamic> toJson() => <String, dynamic>{
        'points': points,
        'subscription': subscription,
        'announcements': announcements,
      };

  NotificationPreferences copyWith({
    bool? points,
    bool? subscription,
    bool? announcements,
  }) =>
      NotificationPreferences(
        points: points ?? this.points,
        subscription: subscription ?? this.subscription,
        announcements: announcements ?? this.announcements,
      );
}

// ---------------------------------------------------------------------------
// Dashboard aggregate payload
// ---------------------------------------------------------------------------

class DashboardPoints {
  const DashboardPoints({required this.balance, required this.level});

  final int balance;
  final MemberLevel level;

  factory DashboardPoints.fromJson(Map<String, dynamic> j) => DashboardPoints(
        balance: _int(j['balance']),
        level: MemberLevel.fromJson(
            (j['level'] as Map?)?.cast<String, dynamic>() ?? const {}),
      );
}

class DashboardData {
  const DashboardData({
    required this.member,
    required this.points,
    required this.streak,
    this.subscription,
    required this.missionsToday,
    required this.recentTransactions,
    required this.badgesCount,
    required this.unreadNotifications,
    required this.todayPointsEarned,
  });

  final Member member;
  final DashboardPoints points;
  final Streak streak;
  final ActiveSubscription? subscription;
  final List<Mission> missionsToday;
  final List<PointTransaction> recentTransactions;
  final int badgesCount;
  final int unreadNotifications;
  final int todayPointsEarned;

  factory DashboardData.fromJson(Map<String, dynamic> j) => DashboardData(
        member: Member.fromJson(
            (j['member'] as Map?)?.cast<String, dynamic>() ?? const {}),
        points: DashboardPoints.fromJson(
            (j['points'] as Map?)?.cast<String, dynamic>() ?? const {}),
        streak: Streak.fromJson(
            (j['streak'] as Map?)?.cast<String, dynamic>() ?? const {}),
        subscription: j['subscription'] == null
            ? null
            : ActiveSubscription.fromJson(
                (j['subscription'] as Map).cast<String, dynamic>()),
        missionsToday:
            _list(j['missions_today']).map(Mission.fromJson).toList(),
        recentTransactions: _list(j['recent_transactions'])
            .map(PointTransaction.fromJson)
            .toList(),
        badgesCount: _int(j['badges_count']),
        unreadNotifications: _int(j['unread_notifications']),
        todayPointsEarned: _int(j['today_points_earned'], 5),
      );
}

// ---------------------------------------------------------------------------
// Auth result
// ---------------------------------------------------------------------------

class AuthResult {
  const AuthResult({
    required this.token,
    required this.member,
    this.isNewMember = false,
  });

  final String token;
  final Member member;
  final bool isNewMember;

  factory AuthResult.fromJson(Map<String, dynamic> j) => AuthResult(
        token: _str(j['token']),
        member: Member.fromJson(
            (j['member'] as Map?)?.cast<String, dynamic>() ?? const {}),
        isNewMember: _bool(j['is_new_member']),
      );
}

/// Simple pagination wrapper for list endpoints returning `{data, meta}`.
class Paged<T> {
  const Paged({required this.items, this.currentPage = 1, this.lastPage = 1});

  final List<T> items;
  final int currentPage;
  final int lastPage;

  bool get hasMore => currentPage < lastPage;
}
