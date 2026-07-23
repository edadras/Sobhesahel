<?php

namespace App\Console\Commands;

use App\Models\NewsSource;
use App\Services\NewsCrawlerService;
use Illuminate\Console\Command;

class CrawlSourcesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:crawl-sources
                            {--source= : Crawl only this news source ID, ignoring its fetch interval}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch new items from active external news sources (RSS/Atom) into the monitoring cartable and prune stale fetched items (WP-13)';

    /**
     * Execute the console command.
     */
    public function handle(NewsCrawlerService $crawler): int
    {
        if ($sourceId = $this->option('source')) {
            $source = NewsSource::find($sourceId);

            if ($source === null) {
                $this->error("News source [{$sourceId}] not found.");

                return self::FAILURE;
            }

            $result = $crawler->crawlSource($source);
            $this->reportResult($result);

            return $result['error'] === null ? self::SUCCESS : self::FAILURE;
        }

        $results = $crawler->crawlDueSources();

        if ($results === []) {
            $this->info('No active news sources configured.');
        }

        $failures = 0;

        foreach ($results as $result) {
            if ($result['skipped']) {
                $this->line("- {$result['source']}: skipped (not due yet)");

                continue;
            }

            $this->reportResult($result);

            if ($result['error'] !== null) {
                $failures++;
            }
        }

        $pruned = $crawler->prune();

        if ($pruned > 0) {
            $this->info("Pruned {$pruned} stale fetched item(s).");
        }

        // Individual source failures are logged but never fail the run.
        if ($failures > 0) {
            $this->warn("{$failures} source(s) failed — see the log for details.");
        }

        return self::SUCCESS;
    }

    protected function reportResult(array $result): void
    {
        if ($result['error'] !== null) {
            $this->error("- {$result['source']}: FAILED — {$result['error']}");

            return;
        }

        $this->info("- {$result['source']}: {$result['fetched']} item(s) read, {$result['new']} new.");
    }
}
