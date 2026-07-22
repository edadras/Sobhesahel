<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckScoutDriver extends Command
{
    protected $signature = 'scout:check';
    protected $description = 'Check current Scout driver configuration';

    public function handle()
    {
        $driver = config('scout.driver');
        $envDriver = env('SCOUT_DRIVER', 'not set');
        
        $this->info('📋 Scout Configuration:');
        $this->newLine();
        $this->info("  Current Driver: {$driver}");
        $this->info("  ENV SCOUT_DRIVER: {$envDriver}");
        $this->newLine();
        
        if ($driver === 'meilisearch') {
            $host = config('scout.meilisearch.host');
            $key = config('scout.meilisearch.key');
            $this->info("  ✓ Meilisearch is configured");
            $this->info("  Host: {$host}");
            $this->info("  Key: " . ($key ? 'Set' : 'Not set'));
        } elseif ($driver === 'elasticsearch' || str_contains($driver, 'elastic')) {
            $this->warn("  ⚠️  ElasticSearch is configured (but we want Meilisearch)");
            $this->newLine();
            $this->error("  ❌ Problem: SCOUT_DRIVER should be 'meilisearch'");
            $this->newLine();
            $this->info("  💡 Solution:");
            $this->info("     1. Edit .env file");
            $this->info("     2. Set: SCOUT_DRIVER=meilisearch");
            $this->info("     3. Run: php artisan config:clear");
            $this->info("     4. Restart queue worker");
        } else {
            $this->warn("  ⚠️  Driver is: {$driver}");
        }
        
        return 0;
    }
}

