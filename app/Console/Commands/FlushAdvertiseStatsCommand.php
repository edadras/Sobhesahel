<?php

namespace App\Console\Commands;

use App\Models\Advertise;
use Illuminate\Console\Command;

class FlushAdvertiseStatsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:advertise-flush-stats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Flush cache-buffered advertise impressions/clicks into advertise_daily_stats and deactivate ads that passed their limits';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $summary = Advertise::flushBufferedStats();

        $this->info(sprintf(
            'Advertise stats flushed: %d views, %d clicks, %d ads deactivated (limits reached).',
            $summary['views'],
            $summary['clicks'],
            $summary['deactivated'],
        ));

        return self::SUCCESS;
    }
}
