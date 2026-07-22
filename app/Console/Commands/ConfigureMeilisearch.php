<?php

namespace App\Console\Commands;

use App\Support\SearchIndexSettings;
use Illuminate\Console\Command;
use Meilisearch\Client;

class ConfigureMeilisearch extends Command
{
    protected $signature = 'meilisearch:configure';

    protected $description = 'Apply Meilisearch index settings for all searchable content indexes';

    public function handle(): int
    {
        $client = new Client(
            config('scout.meilisearch.host'),
            config('scout.meilisearch.key')
        );

        $settings = SearchIndexSettings::meilisearch();

        foreach (SearchIndexSettings::modelsByIndex() as $indexName => $modelClass) {
            $this->info("Configuring index: {$indexName}");

            $index = $client->index($indexName);

            $index->updateSearchableAttributes($settings['searchableAttributes']);
            $index->updateFilterableAttributes($settings['filterableAttributes']);
            $index->updateSortableAttributes($settings['sortableAttributes']);
            $index->updateRankingRules($settings['rankingRules']);
            $index->updatePagination($settings['pagination']);

            $this->line("  ✓ {$indexName} ({$modelClass})");
        }

        $this->newLine();
        $this->info('Meilisearch indexes configured successfully.');
        $this->comment('Run: php artisan scout:sync-index-settings');
        $this->comment('Then: php artisan search:reindex --sync');

        return self::SUCCESS;
    }
}
