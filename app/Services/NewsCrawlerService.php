<?php

namespace App\Services;

use App\Models\FetchedItem;
use App\Models\News;
use App\Models\NewsSource;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SimpleXMLElement;
use Throwable;

/**
 * WP-13 — رصد و پایش منابع خبری.
 *
 * Fetches RSS 2.0 / Atom feeds of the configured external news sources,
 * dedupes entries, stores them as FetchedItem rows for the editorial
 * cartable, and can turn a fetched item into a draft News record.
 *
 * Every source is processed inside its own try/catch: one broken feed can
 * never abort the whole crawl run.
 */
class NewsCrawlerService
{
    protected const MEDIA_NS = 'http://search.yahoo.com/mrss/';
    protected const CONTENT_NS = 'http://purl.org/rss/1.0/modules/content/';

    /**
     * Crawl every active source that is due according to its own
     * fetch_interval_minutes.
     *
     * @return array<int, array{source: string, skipped: bool, fetched: int, new: int, error: ?string}>
     */
    public function crawlDueSources(bool $force = false): array
    {
        $results = [];

        foreach (NewsSource::where('is_active', true)->orderBy('id')->get() as $source) {
            if (! $force && ! $source->isDue()) {
                $results[$source->id] = [
                    'source' => $source->name,
                    'skipped' => true,
                    'fetched' => 0,
                    'new' => 0,
                    'error' => null,
                ];

                continue;
            }

            $results[$source->id] = $this->crawlSource($source);
        }

        return $results;
    }

    /**
     * Fetch and store the feed of a single source. Never throws.
     *
     * @return array{source: string, skipped: bool, fetched: int, new: int, error: ?string}
     */
    public function crawlSource(NewsSource $source): array
    {
        $result = [
            'source' => $source->name,
            'skipped' => false,
            'fetched' => 0,
            'new' => 0,
            'error' => null,
        ];

        try {
            $response = Http::withHeaders([
                'User-Agent' => (string) config('crawler.user_agent'),
                'Accept' => 'application/rss+xml, application/atom+xml, application/xml, text/xml;q=0.9, */*;q=0.8',
            ])
                ->timeout((int) config('crawler.timeout', 10))
                ->get($source->feed_url);

            if ($response->failed()) {
                throw new \RuntimeException('HTTP '.$response->status().' while fetching feed');
            }

            $items = $this->parseFeed($response->body(), (string) $source->type);
            $items = array_slice($items, 0, max(1, (int) config('crawler.max_items_per_fetch', 50)));

            foreach ($items as $item) {
                $result['fetched']++;

                if ($this->storeItem($source, $item)) {
                    $result['new']++;
                }
            }
        } catch (Throwable $e) {
            $result['error'] = $e->getMessage();

            Log::warning('[crawler] Failed to crawl news source', [
                'source_id' => $source->id,
                'source' => $source->name,
                'feed_url' => $source->feed_url,
                'error' => $e->getMessage(),
            ]);
        }

        // Always stamp the attempt so a permanently broken feed still honors
        // its interval instead of being retried on every scheduler tick.
        try {
            $source->forceFill(['last_fetched_at' => now()])->save();
        } catch (Throwable $e) {
            report($e);
        }

        return $result;
    }

    /**
     * Create the FetchedItem if this entry has not been seen before.
     * Returns true when a new row was stored.
     */
    protected function storeItem(NewsSource $source, array $item): bool
    {
        $identity = $item['guid'] ?: $item['link'] ?: $item['title'];

        if ($identity === '' || $item['title'] === '') {
            return false;
        }

        $hash = sha1($source->id.'|'.$identity);

        if (FetchedItem::where('guid_hash', $hash)->exists()) {
            return false;
        }

        try {
            FetchedItem::create([
                'news_source_id' => $source->id,
                'guid' => Str::limit($item['guid'] ?: $item['link'], 2000, ''),
                'guid_hash' => $hash,
                'title' => Str::limit($item['title'], 1000),
                'summary' => $item['summary'] !== '' ? $item['summary'] : null,
                'content' => $item['content'] !== '' ? $item['content'] : null,
                'original_url' => $item['link'] !== '' ? Str::limit($item['link'], 2000, '') : null,
                'image_url' => $item['image'] !== '' ? Str::limit($item['image'], 2000, '') : null,
                'published_at' => $item['published_at'],
                'status' => FetchedItem::STATUS_NEW,
            ]);
        } catch (Throwable $e) {
            // A race between two runs can violate the unique guid_hash —
            // that simply means the item already exists.
            Log::debug('[crawler] Skipped feed item', ['error' => $e->getMessage()]);

            return false;
        }

        return true;
    }

    /**
     * Parse a feed body into a normalized list of entries.
     *
     * @return array<int, array{guid: string, title: string, link: string, summary: string, content: string, image: string, published_at: ?Carbon}>
     */
    public function parseFeed(string $body, string $typeHint = 'rss'): array
    {
        $previous = libxml_use_internal_errors(true);

        try {
            $xml = simplexml_load_string($body, SimpleXMLElement::class, LIBXML_NOCDATA | LIBXML_NONET);

            if ($xml === false) {
                $errors = libxml_get_errors();
                $first = $errors !== [] ? trim($errors[0]->message) : 'unknown parse error';

                throw new \RuntimeException('Invalid XML feed: '.$first);
            }
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }

        // Detect the real format from the document, fall back to the
        // configured type of the source.
        if (strtolower($xml->getName()) === 'feed') {
            return $this->parseAtom($xml);
        }

        if (isset($xml->channel)) {
            return $this->parseRss($xml);
        }

        return $typeHint === 'atom' ? $this->parseAtom($xml) : $this->parseRss($xml);
    }

    /**
     * RSS 2.0: <rss><channel><item>…
     */
    protected function parseRss(SimpleXMLElement $xml): array
    {
        $items = [];

        $channel = $xml->channel ?? $xml;

        foreach ($channel->item as $entry) {
            $link = trim((string) $entry->link);
            $guid = trim((string) $entry->guid);

            // content:encoded carries the full HTML body on many feeds.
            $contentNs = $entry->children(self::CONTENT_NS);
            $content = trim((string) ($contentNs->encoded ?? ''));

            $image = $this->extractRssImage($entry);

            if ($image === '' && $content !== '') {
                $image = $this->extractImageFromHtml($content);
            }

            $items[] = [
                'guid' => $guid,
                'title' => $this->cleanText((string) $entry->title, 1000),
                'link' => $link,
                'summary' => $this->cleanText((string) $entry->description, 2000),
                'content' => $this->cleanHtml($content),
                'image' => $image,
                'published_at' => $this->parseDate((string) $entry->pubDate),
            ];
        }

        return $items;
    }

    /**
     * Atom: <feed><entry>…
     */
    protected function parseAtom(SimpleXMLElement $xml): array
    {
        $items = [];

        foreach ($xml->entry as $entry) {
            $link = '';
            $image = '';

            foreach ($entry->link as $linkEl) {
                $rel = (string) $linkEl['rel'];
                $href = trim((string) $linkEl['href']);
                $type = strtolower((string) $linkEl['type']);

                if ($href === '') {
                    continue;
                }

                if ($rel === '' || $rel === 'alternate') {
                    $link = $link !== '' ? $link : $href;
                } elseif ($rel === 'enclosure' && str_starts_with($type, 'image/')) {
                    $image = $image !== '' ? $image : $href;
                }
            }

            $content = trim((string) $entry->content);
            $summary = trim((string) $entry->summary);

            if ($image === '') {
                $image = $this->extractMediaImage($entry);
            }

            if ($image === '' && $content !== '') {
                $image = $this->extractImageFromHtml($content);
            }

            $published = (string) $entry->published !== ''
                ? (string) $entry->published
                : (string) $entry->updated;

            $items[] = [
                'guid' => trim((string) $entry->id),
                'title' => $this->cleanText((string) $entry->title, 1000),
                'link' => $link,
                'summary' => $this->cleanText($summary !== '' ? $summary : $content, 2000),
                'content' => $this->cleanHtml($content),
                'image' => $image,
                'published_at' => $this->parseDate($published),
            ];
        }

        return $items;
    }

    /**
     * Image of an RSS item: <enclosure>, then media:content/media:thumbnail.
     */
    protected function extractRssImage(SimpleXMLElement $entry): string
    {
        foreach ($entry->enclosure as $enclosure) {
            $url = trim((string) $enclosure['url']);
            $type = strtolower((string) $enclosure['type']);

            if ($url !== '' && (str_starts_with($type, 'image/') || $this->looksLikeImageUrl($url))) {
                return $url;
            }
        }

        return $this->extractMediaImage($entry);
    }

    /**
     * media:content / media:thumbnail (Media RSS namespace, used by RSS and Atom).
     */
    protected function extractMediaImage(SimpleXMLElement $entry): string
    {
        $media = $entry->children(self::MEDIA_NS);

        foreach (['content', 'thumbnail'] as $tag) {
            foreach ($media->{$tag} as $el) {
                // Attributes of a namespaced element must be read through
                // attributes() — $el['url'] returns nothing for media:* tags.
                $attributes = $el->attributes();
                $url = trim((string) ($attributes['url'] ?? ''));
                $type = strtolower((string) ($attributes['type'] ?? ''));
                $medium = strtolower((string) ($attributes['medium'] ?? ''));

                if ($url === '') {
                    continue;
                }

                if ($tag === 'thumbnail'
                    || $medium === 'image'
                    || str_starts_with($type, 'image/')
                    || $this->looksLikeImageUrl($url)) {
                    return $url;
                }
            }
        }

        return '';
    }

    /**
     * Last resort: first <img src="…"> inside the HTML content.
     */
    protected function extractImageFromHtml(string $html): string
    {
        if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $html, $m)) {
            $url = trim($m[1]);

            if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
                return $url;
            }
        }

        return '';
    }

    protected function looksLikeImageUrl(string $url): bool
    {
        $path = (string) (parse_url($url, PHP_URL_PATH) ?? '');

        return (bool) preg_match('/\.(jpe?g|png|webp|gif|avif)$/i', $path);
    }

    /**
     * Plain text for titles/summaries: tags stripped, entities decoded,
     * whitespace collapsed, length-limited.
     */
    protected function cleanText(string $value, int $limit = 2000): string
    {
        $value = html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = trim((string) preg_replace('/\s+/u', ' ', $value));

        return Str::limit($value, $limit, '…');
    }

    /**
     * HTML content kept for the draft body: scripts/styles/iframes removed.
     * Editors always review the draft before publishing.
     */
    protected function cleanHtml(string $html): string
    {
        if ($html === '') {
            return '';
        }

        $html = (string) preg_replace('#<(script|style|iframe|object|embed|form)\b[^>]*>.*?</\1>#is', '', $html);
        $html = (string) preg_replace('#<(script|style|iframe|object|embed|form)\b[^>]*/?>#i', '', $html);
        // Strip inline event handlers (onclick=…, onerror=…).
        $html = (string) preg_replace('/\son\w+\s*=\s*(".*?"|\'.*?\'|[^\s>]+)/i', '', $html);

        return trim($html);
    }

    protected function parseDate(string $value): ?Carbon
    {
        $value = trim($value);

        if ($value === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->setTimezone(config('app.timezone'));
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Delete stale "new"/"dismissed" items (saved ones are always kept).
     */
    public function prune(): int
    {
        $days = (int) config('crawler.prune_after_days', 30);

        if ($days <= 0) {
            return 0;
        }

        return FetchedItem::whereIn('status', [FetchedItem::STATUS_NEW, FetchedItem::STATUS_DISMISSED])
            ->where('created_at', '<', now()->subDays($days))
            ->delete();
    }

    /**
     * ذخیره در سامانه — turn a fetched item into a DRAFT news record with
     * source attribution appended to the body (منبع: …) and, when possible,
     * the original image downloaded into the standard news upload disk.
     */
    public function saveToNews(FetchedItem $item): News
    {
        // Idempotent: re-saving an already-saved item returns the same draft.
        if ($item->saved_news_id !== null) {
            $existing = News::withTrashed()->find($item->saved_news_id);

            if ($existing !== null) {
                return $existing;
            }
        }

        $source = $item->source;

        $body = trim((string) $item->content);

        if ($body === '') {
            $body = '<p>'.e($item->summary !== null && $item->summary !== '' ? $item->summary : $item->title).'</p>';
        }

        // Source attribution per Persian journalistic convention.
        $sourceName = $source?->name ?: 'منبع خارجی';
        $attribution = $item->original_url
            ? '<p><strong>منبع: <a href="'.e($item->original_url).'" target="_blank" rel="noopener noreferrer nofollow">'.e($sourceName).'</a></strong></p>'
            : '<p><strong>منبع: '.e($sourceName).'</strong></p>';

        $body .= "\n".$attribution;

        $news = new News();
        $news->title = Str::limit($item->title, 250, '…');
        $news->short_description = $item->summary;
        $news->body = $body;
        $news->status = 'draft';
        $news->is_published = 0;
        $news->lang_id = 1;
        $news->user_id = auth()->id() ?? 1;

        // Image download is best-effort: a failure must never block saving.
        if (! empty($item->image_url)) {
            try {
                $path = $this->downloadImage($item->image_url);

                if ($path !== null) {
                    $news->image_original = $path;
                }
            } catch (Throwable $e) {
                Log::warning('[crawler] Failed to download item image', [
                    'fetched_item_id' => $item->id,
                    'image_url' => $item->image_url,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $news->save();

        // Default category of the source (if configured).
        if ($source?->category_id) {
            try {
                $news->categories()->syncWithoutDetaching([$source->category_id]);
            } catch (Throwable $e) {
                report($e);
            }
        }

        $item->forceFill([
            'status' => FetchedItem::STATUS_SAVED,
            'saved_news_id' => $news->id,
        ])->save();

        return $news;
    }

    /**
     * Download an image into the same disk the news form uploads to.
     * Returns the stored path or null when the download is unusable.
     */
    protected function downloadImage(string $url): ?string
    {
        $response = Http::withHeaders([
            'User-Agent' => (string) config('crawler.user_agent'),
        ])
            ->timeout((int) config('crawler.timeout', 10))
            ->get($url);

        if ($response->failed()) {
            return null;
        }

        $body = $response->body();

        if ($body === '' || strlen($body) > (int) config('crawler.max_image_bytes', 10 * 1024 * 1024)) {
            return null;
        }

        $contentType = strtolower(trim(explode(';', (string) $response->header('Content-Type'))[0]));

        $extensions = [
            'image/jpeg' => 'jpg',
            'image/jpg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            'image/avif' => 'avif',
        ];

        $extension = $extensions[$contentType]
            ?? strtolower(pathinfo((string) (parse_url($url, PHP_URL_PATH) ?? ''), PATHINFO_EXTENSION));

        if (! in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'avif'], true)) {
            return null;
        }

        $directory = trim((string) config('crawler.image_directory', ''), '/');
        $path = ($directory !== '' ? $directory.'/' : '').Str::random(40).'.'.$extension;

        Storage::disk((string) config('crawler.image_disk', 'public'))->put($path, $body);

        return $path;
    }
}
