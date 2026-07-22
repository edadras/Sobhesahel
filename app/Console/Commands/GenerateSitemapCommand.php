<?php

namespace App\Console\Commands;

use App\Models\News;
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
    protected $signature = 'app:sitemap';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        ini_set('memory_limit', '1024M');

        $sitemapIndex = SitemapIndex::create();

        if (!is_dir(public_path('../../public_html/sitemaps'))){
            mkdir(public_path('../../public_html/sitemaps'));
        }
 
        // 1. Generate manual sitemap
        $manualSitemapPath = public_path('../../public_html/sitemaps/manual.xml');

        // Get latest updated news item (for homepage lastmod)
        $latestPost = News::latest('updated_at')->first();

        $manualSitemap = Sitemap::create()
            ->add(
                Url::create('/')
                    ->setPriority(1.0)
                    ->setLastModificationDate($latestPost?->updated_at)
            )
            ->add(Url::create('/about')->setPriority(0.8))
            ->add(Url::create('/contact')->setPriority(0.8));

        $manualSitemap->writeToFile($manualSitemapPath);
        unset($manualSitemap);

        $sitemapIndex->add('/sitemaps/manual.xml');

        // 2. Paginated post sitemaps (news)
        $page = 1;

        News::chunk(50000, function ($posts) use (&$page, &$sitemapIndex) {
            $sitemapPath = public_path("../../public_html/sitemaps/news-{$page}.xml");
            $sitemap = Sitemap::create();

            foreach ($posts as $post) {
                $sitemap->add(
                    Url::create("/news/{$post->id}/{$post->slug}")
                        ->setLastModificationDate($post->updated_at)
                );

                $this->info("✅ Post id {$post->id} added");
            }

            $sitemap->writeToFile($sitemapPath);
            unset($sitemap); // free memory

            $sitemapIndex->add("/sitemaps/news-{$page}.xml");
            $page++;
        });

        // 3. Write sitemap index
        $sitemapIndex->writeToFile(public_path('../../public_html/sitemap.xml'));
        unset($sitemapIndex);

        $this->info("✅ All sitemaps generated successfully.");
    }
}
