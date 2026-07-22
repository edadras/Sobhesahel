<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Gallery;
use App\Models\News;
use App\Models\Note;
use App\Models\Podcast;
use App\Models\Video;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\SitemapIndex;
use Spatie\Sitemap\Tags\Url;

class GenerateSitemapCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sitemap {--with-tags : Also include tag pages (tag routes are currently disabled on the website)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate sitemap.xml (index) plus sub-sitemaps for news, notes, videos, podcasts, photos, categories and author pages';

    /**
     * Maximum number of URLs per sub-sitemap file (Google allows up to 50,000).
     */
    protected const URLS_PER_SITEMAP = 20000;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        ini_set('memory_limit', '1024M');

        $baseDir = $this->resolveBaseDir();

        if (!is_dir($baseDir . '/sitemaps')) {
            mkdir($baseDir . '/sitemaps', 0755, true);
        }

        $index = SitemapIndex::create();

        $this->writeStaticSitemap($baseDir, $index);

        $contentTypes = [
            'news' => News::class,
            'note' => Note::class,
            'video' => Video::class,
            'podcast' => Podcast::class,
            'photo' => Gallery::class,
        ];

        foreach ($contentTypes as $name => $model) {
            $this->writeContentSitemaps($baseDir, $index, $name, $model);
        }

        $this->writeCategorySitemap($baseDir, $index);
        $this->writeAuthorSitemap($baseDir, $index);

        if ($this->option('with-tags')) {
            $this->writeTagSitemap($baseDir, $index);
        }

        $index->writeToFile($baseDir . '/sitemap.xml');

        $this->info("All sitemaps generated successfully in {$baseDir}");

        return self::SUCCESS;
    }

    /**
     * On production the actual web root is ../../public_html relative to public/.
     * Fall back to public_path() everywhere else (local, staging, CI).
     */
    private function resolveBaseDir(): string
    {
        $productionRoot = realpath(public_path('../../public_html'));

        return $productionRoot !== false ? $productionRoot : public_path();
    }

    /**
     * Home page, content index pages and static pages.
     */
    private function writeStaticSitemap(string $baseDir, SitemapIndex $index): void
    {
        $sitemap = Sitemap::create();

        $latestNews = News::query()->where('is_published', true)->latest('updated_at')->first();

        $sitemap->add(
            Url::create(url('/'))
                ->setPriority(1.0)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_HOURLY)
                ->setLastModificationDate($latestNews?->updated_at ?? now())
        );

        $indexPages = [
            'news' => News::class,
            'note' => Note::class,
            'video' => Video::class,
            'podcast' => Podcast::class,
            'photo' => Gallery::class,
        ];

        foreach ($indexPages as $type => $model) {
            $latest = $model::query()->where('is_published', true)->latest('updated_at')->first();

            $sitemap->add(
                Url::create(route('website.rtl.index', ['type' => $type]))
                    ->setPriority(0.9)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                    ->setLastModificationDate($latest?->updated_at ?? now())
            );
        }

        $sitemap->add(
            Url::create(route('website.rtl.archive'))
                ->setPriority(0.7)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
        );

        $sitemap->add(
            Url::create(route('website.rtl.about'))
                ->setPriority(0.5)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
        );

        $sitemap->add(
            Url::create(route('website.rtl.contact'))
                ->setPriority(0.5)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
        );

        $sitemap->writeToFile($baseDir . '/sitemaps/manual.xml');
        unset($sitemap);

        $index->add('/sitemaps/manual.xml');

        $this->info('Static sitemap written.');
    }

    /**
     * Paginated sitemaps for a published content type (news, note, video, podcast, photo).
     */
    private function writeContentSitemaps(string $baseDir, SitemapIndex $index, string $name, string $modelClass): void
    {
        $page = 1;
        $count = 0;
        $total = 0;
        $sitemap = Sitemap::create();

        $flush = function () use (&$sitemap, &$page, &$count, $baseDir, $index, $name) {
            $sitemap->writeToFile($baseDir . "/sitemaps/{$name}-{$page}.xml");
            $index->add("/sitemaps/{$name}-{$page}.xml");

            $sitemap = Sitemap::create();
            $page++;
            $count = 0;
        };

        $modelClass::query()
            ->where('is_published', true)
            ->where('lang_id', 1)
            ->orderBy('id')
            ->chunkById(1000, function ($posts) use (&$sitemap, &$count, &$total, $flush) {
                foreach ($posts as $post) {
                    try {
                        $url = $post->getUrl();
                    } catch (\Throwable $e) {
                        continue;
                    }

                    $sitemap->add(
                        Url::create($url)
                            ->setLastModificationDate($post->updated_at ?? $post->created_at ?? now())
                    );

                    $count++;
                    $total++;

                    if ($count >= self::URLS_PER_SITEMAP) {
                        $flush();
                    }
                }
            });

        if ($count > 0 || $page === 1) {
            $flush();
        }

        $this->info("{$name}: {$total} urls written.");
    }

    /**
     * Category archive pages, lastmod = most recently updated news in the category.
     */
    private function writeCategorySitemap(string $baseDir, SitemapIndex $index): void
    {
        $sitemap = Sitemap::create();

        Category::query()
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->get()
            ->each(function (Category $category) use ($sitemap) {
                $lastNews = $category->news()
                    ->where('is_published', true)
                    ->latest('updated_at')
                    ->first();

                $sitemap->add(
                    Url::create(route('website.rtl.category', ['slug' => $category->slug]))
                        ->setPriority(0.8)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                        ->setLastModificationDate($lastNews?->updated_at ?? $category->updated_at ?? now())
                );
            });

        $sitemap->writeToFile($baseDir . '/sitemaps/categories.xml');
        unset($sitemap);

        $index->add('/sitemaps/categories.xml');

        $this->info('Category sitemap written.');
    }

    /**
     * Author pages for dedicated authors and staff users with published news.
     */
    private function writeAuthorSitemap(string $baseDir, SitemapIndex $index): void
    {
        $sitemap = Sitemap::create();

        // Dedicated author profiles
        News::query()
            ->where('is_published', true)
            ->whereNotNull('author_id')
            ->selectRaw('author_id, MAX(updated_at) as last_update')
            ->groupBy('author_id')
            ->get()
            ->each(function ($row) use ($sitemap) {
                $sitemap->add(
                    Url::create(route('website.rtl.author', ['user_type' => 'author', 'id' => $row->author_id]))
                        ->setPriority(0.4)
                        ->setLastModificationDate($this->parseDate($row->last_update))
                );
            });

        // Staff users who published without a dedicated author profile
        News::query()
            ->where('is_published', true)
            ->whereNull('author_id')
            ->whereNotNull('user_id')
            ->selectRaw('user_id, MAX(updated_at) as last_update')
            ->groupBy('user_id')
            ->get()
            ->each(function ($row) use ($sitemap) {
                $sitemap->add(
                    Url::create(route('website.rtl.author', ['user_type' => 'user', 'id' => $row->user_id]))
                        ->setPriority(0.4)
                        ->setLastModificationDate($this->parseDate($row->last_update))
                );
            });

        $sitemap->writeToFile($baseDir . '/sitemaps/authors.xml');
        unset($sitemap);

        $index->add('/sitemaps/authors.xml');

        $this->info('Author sitemap written.');
    }

    /**
     * Tag pages. Disabled by default because the public tag route currently
     * returns 503 — enable with --with-tags once the tag pages go live.
     */
    private function writeTagSitemap(string $baseDir, SitemapIndex $index): void
    {
        $sitemap = Sitemap::create();

        \Spatie\Tags\Tag::query()->get()->each(function ($tag) use ($sitemap) {
            try {
                $name = $tag->getTranslation('name', 'fa', false)
                    ?: $tag->getTranslation('name', app()->getLocale(), false);
            } catch (\Throwable $e) {
                $name = null;
            }

            if (empty($name)) {
                return;
            }

            $sitemap->add(
                Url::create(route('website.rtl.tag', ['name' => $name]))
                    ->setPriority(0.3)
                    ->setLastModificationDate($tag->updated_at ?? now())
            );
        });

        $sitemap->writeToFile($baseDir . '/sitemaps/tags.xml');
        unset($sitemap);

        $index->add('/sitemaps/tags.xml');

        $this->info('Tag sitemap written.');
    }

    private function parseDate($value): Carbon
    {
        try {
            return !empty($value) ? Carbon::parse($value) : now();
        } catch (\Throwable $e) {
            return now();
        }
    }
}
