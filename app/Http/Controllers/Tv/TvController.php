<?php

namespace App\Http\Controllers\Tv;

use App\Http\Controllers\Controller;
use App\Http\Resources\ContentMetaDataResource;
use App\Models\LiveStream;
use App\Models\Video;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Morilog\Jalali\Jalalian;
use Spatie\Tags\Tag;

class TvController extends Controller
{
    /**
     * TV subdomain host. Guarded helper: uses config('app.tv_domain') when set,
     * otherwise falls back to "tv." + host of config('app.url'). Never throws,
     * even for odd/empty app.url values.
     */
    public static function tvDomain(): string
    {
        try {
            $configured = trim((string) config('app.tv_domain', ''));

            if ($configured !== '') {
                $host = parse_url($configured, PHP_URL_HOST);

                return is_string($host) && $host !== ''
                    ? $host
                    : trim(preg_replace('#^https?://#i', '', rtrim($configured, '/')), '/');
            }

            $appUrl = (string) config('app.url', '');
            $host = parse_url($appUrl, PHP_URL_HOST);

            if (!is_string($host) || $host === '') {
                // app.url without scheme ("sobhesahel.com") or empty.
                $host = trim(preg_replace('#^https?://#i', '', $appUrl), '/');
                $host = strtok($host, '/') ?: 'localhost';
            }

            return 'tv.' . $host;
        } catch (\Throwable $e) {
            return 'tv.localhost';
        }
    }

    /**
     * Absolute URL on the TV domain (used for canonicals).
     */
    public static function tvUrl(string $path = '/'): string
    {
        $scheme = 'https';

        try {
            $configuredScheme = parse_url((string) config('app.url', ''), PHP_URL_SCHEME);
            if (is_string($configuredScheme) && $configuredScheme !== '') {
                $scheme = $configuredScheme;
            }
        } catch (\Throwable $e) {
            // keep https
        }

        return $scheme . '://' . self::tvDomain() . '/' . ltrim($path, '/');
    }

    /**
     * Are we serving the request on the TV subdomain (vs. the /tv alias)?
     */
    protected function onTvDomain(Request $request): bool
    {
        return strcasecmp($request->getHost(), self::tvDomain()) === 0;
    }

    /**
     * TV home: big latest video + grid + live strip.
     */
    public function home(Request $request)
    {
        $on_tv_domain = $this->onTvDomain($request);

        $videos = Video::query()
            ->where('lang_id', 1)
            ->where('is_published', true)
            ->orderByDesc('id')
            ->take(13)
            ->get()
            ->map(fn ($item) => $this->presentVideo($item, $on_tv_domain));

        $featured = $videos->first();
        $grid = $videos->slice(1)->values();

        $live_streams = LiveStream::activeStreams();

        $seo = [
            'title' => 'صبح ساحل تی‌وی',
            'description' => 'صبح ساحل تی‌وی؛ مرجع ویدئوهای خبری هرمزگان و ایران به همراه پخش زنده رویدادها.',
            'type' => 'website',
            'url' => self::tvUrl('/'),
        ];

        $website_title = 'صبح ساحل تی‌وی | ' . (setting('general.fa_brand_name') ?? 'گروه رسانه‌ای صبح‌ساحل');

        return view('tv.home', compact('featured', 'grid', 'live_streams', 'seo', 'website_title', 'on_tv_domain'));
    }

    /**
     * Video archive with pagination + category (tag) filter.
     */
    public function videos(Request $request)
    {
        $on_tv_domain = $this->onTvDomain($request);

        $category = trim((string) $request->query('category', ''));

        $query = Video::query()
            ->where('lang_id', 1)
            ->where('is_published', true)
            ->orderByDesc('id');

        if ($category !== '') {
            // Tags are stored under the "fa" locale; fall back to the app locale.
            $tag = rescue(fn () => Tag::findFromString($category, null, 'fa'), null, false)
                ?? rescue(fn () => Tag::findFromString($category, null, app()->getLocale()), null, false);

            if ($tag) {
                $query->withAnyTags([$tag]);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        $videos = $query->paginate(24)->withQueryString();

        $items = collect($videos->items())
            ->map(fn ($item) => $this->presentVideo($item, $on_tv_domain));

        $categories = $this->videoCategories();

        $page_title = $category !== '' ? 'ویدئوها: ' . $category : 'آرشیو ویدئوها';

        $seo = [
            'title' => $page_title . ' | صبح ساحل تی‌وی',
            'description' => 'آرشیو ویدئوهای صبح ساحل تی‌وی؛ جدیدترین ویدئوهای خبری هرمزگان و ایران.',
            'type' => 'website',
            'url' => self::tvUrl('/videos' . ($category !== '' ? '?category=' . urlencode($category) : '')),
        ];

        $website_title = $page_title . ' | صبح ساحل تی‌وی';

        return view('tv.videos', compact('videos', 'items', 'categories', 'category', 'page_title', 'seo', 'website_title', 'on_tv_domain'));
    }

    /**
     * Single video page (published only).
     */
    public function single(Request $request, $code, $slug = null)
    {
        $on_tv_domain = $this->onTvDomain($request);

        $post = Video::query()
            ->where('lang_id', 1)
            ->where('is_published', true)
            ->findOrFail($code);

        try {
            $post->posted_at_jalali = Jalalian::fromCarbon(Carbon::parse($post->publish_at))->format('%d %B %Y H:i');
        } catch (\Throwable $e) {
            $post->posted_at_jalali = null;
        }

        $related = Video::query()
            ->where('lang_id', 1)
            ->where('is_published', true)
            ->where('id', '!=', $post->id)
            ->orderByDesc('id')
            ->take(8)
            ->get()
            ->map(fn ($item) => $this->presentVideo($item, $on_tv_domain));

        $seo = $post->getSeoData();
        $seo['type'] = 'article';
        // Canonical on TV pages points to the TV domain.
        $seo['url'] = self::tvUrl('/video/' . $post->id . '/' . $post->slug);

        $website_title = $post->title . ' | صبح ساحل تی‌وی';

        return view('tv.single', compact('post', 'related', 'seo', 'website_title', 'on_tv_domain'));
    }

    /**
     * Live streams on the TV subdomain.
     */
    public function live(Request $request)
    {
        $on_tv_domain = $this->onTvDomain($request);

        $streams = LiveStream::activeStreams();

        $live_streams = $streams->filter(fn ($stream) => $stream->is_live)->values();
        $upcoming_streams = $streams->reject(fn ($stream) => $stream->is_live)->values();

        $seo = [
            'title' => 'پخش زنده | صبح ساحل تی‌وی',
            'description' => 'پخش زنده صبح ساحل تی‌وی؛ تماشای آنلاین رویدادها و برنامه‌های زنده هرمزگان و ایران.',
            'type' => 'website',
            'url' => self::tvUrl('/live'),
        ];

        $website_title = 'پخش زنده | صبح ساحل تی‌وی';

        return view('tv.live', compact('live_streams', 'upcoming_streams', 'seo', 'website_title', 'on_tv_domain'));
    }

    /**
     * Resolve a Video into array data for the TV views. Internal links stay on
     * the TV domain when browsing there, and fall back to the main-site single
     * pages when the TV home is viewed through the /tv alias (no DNS needed).
     */
    protected function presentVideo(Video $video, bool $on_tv_domain): array
    {
        $data = ContentMetaDataResource::make($video)->resolve();

        try {
            $data['tv_url'] = $on_tv_domain
                ? route('tv.single', ['code' => $video->id, 'slug' => $video->slug])
                : ($data['url'] ?? '#');
        } catch (\Throwable $e) {
            $data['tv_url'] = $data['url'] ?? '#';
        }

        return $data;
    }

    /**
     * Tags used as video categories for the archive filter (null-safe).
     */
    protected function videoCategories()
    {
        return rescue(function () {
            return Tag::query()
                ->join('taggables', 'taggables.tag_id', '=', 'tags.id')
                ->where('taggables.taggable_type', Video::class)
                ->select('tags.*')
                ->distinct()
                ->orderBy('tags.order_column')
                ->limit(30)
                ->get();
        }, collect(), false);
    }
}
