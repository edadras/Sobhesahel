<?php

namespace App\Console\Commands;

use App\Models\Gallery;
use App\Models\News;
use App\Models\Note;
use App\Models\Video;
use Illuminate\Console\Command;

class TestSearch extends Command
{
    protected $signature = 'search:test {term? : Optional search term to test}';

    protected $description = 'Test if Meilisearch is working and has indexed documents';

    public function handle(): int
    {
        $models = [
            'news' => News::class,
            'gallery' => Gallery::class,
            'video' => Video::class,
            'note' => Note::class,
        ];

        $term = $this->argument('term');

        $this->info('🔍 Testing Meilisearch indexes...');
        if ($term) {
            $this->info("Search term: {$term}");
        }
        $this->newLine();

        foreach ($models as $name => $modelClass) {
            $this->info("📋 Testing {$name}...");

            $dbQuery = $modelClass::where('status', 'published')->where('is_published', true);

            if ($term) {
                $dbQuery->where(function ($query) use ($term) {
                    $query->where('title', 'like', '%'.$term.'%')
                        ->orWhere('short_description', 'like', '%'.$term.'%')
                        ->orWhere('body', 'like', '%'.$term.'%');
                });
            }

            $dbCount = $dbQuery->count();
            $this->info("  📊 Database matches: {$dbCount}");

            try {
                $query = $term ?? '';
                $raw = $modelClass::search($query)->take(10)->raw();
                $searchCount = $raw['estimatedTotalHits']
                    ?? $raw['totalHits']
                    ?? count($raw['hits'] ?? []);

                $this->info("  🔎 Meilisearch matches: {$searchCount}");

                if ($searchCount === 0) {
                    $this->warn('  ⚠️  No Meilisearch results for this query.');
                } else {
                    $this->info('  ✓ Meilisearch is responding');

                    $searchResults = $modelClass::search($query)->take(3)->get();

                    if ($searchResults->count() > 0) {
                        $this->info('  📝 Sample results:');
                        foreach ($searchResults as $item) {
                            $this->line('    - ID: '.$item->id.', Title: '.mb_substr($item->title ?? 'N/A', 0, 60));
                        }
                    }
                }
            } catch (\Throwable $e) {
                $this->error('  ❌ Meilisearch error: '.$e->getMessage());
            }

            $this->newLine();
        }

        $this->info('✅ Test completed!');

        if ($term) {
            $this->comment('If Database > 0 but Meilisearch = 0, run: php artisan search:reindex --sync');
        }

        return self::SUCCESS;
    }
}
