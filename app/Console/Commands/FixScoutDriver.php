<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class FixScoutDriver extends Command
{
    protected $signature = 'scout:fix-driver';
    protected $description = 'Fix Scout driver to use Meilisearch and clear failed jobs';

    public function handle()
    {
        $this->info('🔧 Fixing Scout Driver Configuration...');
        $this->newLine();
        
        // Check current driver
        $currentDriver = config('scout.driver');
        $this->info("Current driver: {$currentDriver}");
        
        if ($currentDriver !== 'meilisearch') {
            $this->warn("⚠️  Driver is not 'meilisearch'!");
            $this->newLine();
            $this->info("Please update your .env file:");
            $this->info("  SCOUT_DRIVER=meilisearch");
            $this->newLine();
            $this->info("Then run:");
            $this->info("  php artisan config:clear");
            $this->newLine();
        } else {
            $this->info("✓ Driver is already set to 'meilisearch'");
        }
        
        // Clear config cache
        $this->info("Clearing config cache...");
        Artisan::call('config:clear');
        $this->info("✓ Config cache cleared");
        
        // Flush failed jobs
        $this->info("Flushing failed jobs...");
        try {
            Artisan::call('queue:flush');
            $this->info("✓ Failed jobs flushed");
        } catch (\Exception $e) {
            $this->warn("⚠️  Could not flush queue: " . $e->getMessage());
        }
        
        $this->newLine();
        $this->info("✅ Done!");
        $this->newLine();
        $this->info("Next steps:");
        $this->info("  1. Make sure SCOUT_DRIVER=meilisearch in .env");
        $this->info("  2. Run: php artisan config:clear");
        $this->info("  3. Run: php artisan search:reindex --incremental --sync");
        $this->info("  4. Start queue worker: php artisan queue:work");
        
        return 0;
    }
}

