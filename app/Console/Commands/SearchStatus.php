<?php

namespace App\Console\Commands;

use App\Support\SearchIndexSettings;
use Illuminate\Console\Command;
use Meilisearch\Client;

class SearchStatus extends Command
{
    protected $signature = 'search:status';

    protected $description = 'Show Meilisearch index stats and connection status';

    public function handle(): int
    {
        $host = config('scout.meilisearch.host');
        $this->info("Meilisearch host: {$host}");
        $this->newLine();

        try {
            $client = new Client($host, config('scout.meilisearch.key'));
            $health = $client->health();
            $this->info('Status: '.($health['status'] ?? 'unknown'));
        } catch (\Throwable $exception) {
            $this->error('Cannot connect to Meilisearch: '.$exception->getMessage());
            $this->comment('Fix Meilisearch service, then run: php artisan search:reindex --sync');

            return self::FAILURE;
        }

        $this->newLine();

        foreach (SearchIndexSettings::modelsByIndex() as $indexName => $modelClass) {
            try {
                $stats = $client->index($indexName)->stats();
                $documents = $stats['numberOfDocuments'] ?? 0;
                $indexing = ($stats['isIndexing'] ?? false) ? ' (indexing...)' : '';

                $this->line(sprintf(
                    '  %-10s %8d docs%s  →  %s',
                    $indexName,
                    $documents,
                    $indexing,
                    $modelClass
                ));
            } catch (\Throwable $exception) {
                $this->warn("  {$indexName}: ".$exception->getMessage());
            }
        }

        $this->newLine();
        $this->comment('If docs = 0, run: php artisan scout:sync-index-settings && php artisan meilisearch:configure && php artisan search:reindex --sync');

        return self::SUCCESS;
    }
}
