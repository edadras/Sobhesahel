<?php

namespace App\Console\Commands;

use App\Models\News;
use Illuminate\Console\Command;

class CronjobCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish scheduled news and auto-archive expired news (archive_at)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->publish_scheduled();
        $this->archive_expired();
    }

    public function publish_scheduled()
    {
        News::where('status', 'scheduled')->where('publish_at', '<', now())->update([
            'status' => 'published',
            'is_published' => true
        ]);
    }

    /**
     * آرشیو خودکار — suspend published news whose archive_at has passed.
     *
     * Saved one-by-one (not a mass update) so ContentTrait keeps
     * is_published in sync and NewsObserver records an "auto_archived"
     * NewsRevision entry for the audit trail (see News::$isAutoArchiving).
     */
    public function archive_expired()
    {
        try {
            News::query()
                ->where('status', 'published')
                ->whereNotNull('archive_at')
                ->where('archive_at', '<=', now())
                ->orderBy('id')
                ->chunkById(50, function ($items) {
                    foreach ($items as $news) {
                        try {
                            $news->isAutoArchiving = true;
                            $news->status = 'suspended';
                            $news->save();
                        } catch (\Throwable $e) {
                            report($e);
                        }
                    }
                });
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
