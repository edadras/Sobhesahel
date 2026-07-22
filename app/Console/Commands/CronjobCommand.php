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
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->publish_scheduled();
    }

    public function publish_scheduled()
    {
        News::where('status', 'scheduled')->where('publish_at', '<', now())->update([
            'status' => 'published',
            'is_published' => true
        ]);
    }
}
