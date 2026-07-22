<?php

namespace App\Console\Commands;

use App\Models\Gallery;
use App\Models\News;
use App\Models\Note;
use App\Models\Video;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class ReindexSearch extends Command
{
    protected $signature = 'search:reindex {model?} {--sync : Force synchronous indexing (bypass queue)} {--incremental : Only index missing items without flushing}';
    protected $description = 'Re-index all searchable models in Meilisearch';

    public function handle()
    {
        $model = $this->argument('model');
        $sync = $this->option('sync');
        $incremental = $this->option('incremental');

        // Temporarily disable queue if --sync flag is used
        if ($sync) {
            Config::set('scout.queue', false);
            $this->info('⚠️  Queue disabled - indexing synchronously (this may take longer)...');
        }

        $models = [
            'news' => News::class,
            'gallery' => Gallery::class,
            'video' => Video::class,
            'note' => Note::class,
        ];

        if ($model && !isset($models[$model])) {
            $this->error("Invalid model. Available models: " . implode(', ', array_keys($models)));
            return 1;
        }

        $modelsToIndex = $model ? [$model => $models[$model]] : $models;

        foreach ($modelsToIndex as $name => $modelClass) {
            $this->info("📋 Processing {$name}...");
            
            // Count total published items
            $query = $modelClass::where('status', 'published')
                ->where('is_published', true);
            
            $totalCount = $query->count();
            
            if ($totalCount === 0) {
                $this->warn("  ⚠️  No published {$name} found. Skipping...");
                $this->newLine();
                continue;
            }

            // Only flush if not incremental
            if (!$incremental) {
                $this->info("  🗑️  Flushing {$name} index...");
                try {
                    $modelClass::removeAllFromSearch();
                    $this->info("  ✓ Index flushed");
                } catch (\Exception $e) {
                    $this->warn("  ⚠️  Could not flush index: " . $e->getMessage());
                }
            } else {
                $this->info("  🔄 Incremental mode: Only indexing missing/updated items...");
            }

            $this->info("  📤 Re-indexing {$totalCount} published {$name}...");
            
            $bar = $this->output->createProgressBar($totalCount);
            $bar->setFormat('  %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s%');
            $bar->start();

            $indexedCount = 0;
            $chunkSize = 50; // Smaller chunks for better progress tracking
            
            $relations = $name === 'news' ? ['author', 'tags', 'categories'] : ['author', 'tags'];

            $skippedCount = 0;

            $query->with($relations)
                ->orderBy('id', 'ASC')
                ->chunk($chunkSize, function ($items) use ($bar, &$indexedCount, &$skippedCount, $sync) {
                    foreach ($items as $item) {
                        if ($item->shouldBeSearchable()) {
                            $item->searchable();
                            $indexedCount++;

                            if (! $sync && $indexedCount % 10 === 0) {
                                usleep(100000);
                            }
                        } else {
                            $skippedCount++;
                        }

                        $bar->advance();
                    }
                });

            $bar->finish();
            $this->newLine();
            $this->info("  ✓ {$name}: {$indexedCount} items indexed successfully!");

            if ($skippedCount > 0) {
                $this->warn("  ⚠️  {$skippedCount} items skipped by shouldBeSearchable() — check is_published cast.");
            }
            $this->newLine();
        }

        if ($sync) {
            $this->info('💡 Tip: Queue is now re-enabled. New items will be queued automatically.');
        } else {
            $this->info('💡 Tip: If indexing seems slow, use --sync flag to force synchronous indexing.');
            $this->info('💡 Note: Make sure queue worker is running: php artisan queue:work');
        }

        Cache::forget('search.meilisearch_has_docs');

        $this->info('✅ All models re-indexed successfully!');
        $this->newLine();
        $this->info('⏳ Please wait a few seconds for Meilisearch to process the indexes...');
        $this->comment('Recommended: php artisan scout:sync-index-settings && php artisan meilisearch:configure');
        
        return 0;
    }
}

