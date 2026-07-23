<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use Illuminate\Console\Command;

/**
 * اشتراک‌های ویژه‌ای که تاریخ پایان آن‌ها گذشته را «منقضی‌شده» می‌کند.
 */
class ExpireSubscriptionsCommand extends Command
{
    protected $signature = 'app:expire-subscriptions';

    protected $description = 'Mark active subscriptions whose ends_at has passed as expired (اشتراک ویژه)';

    public function handle(): int
    {
        $count = Subscription::where('status', Subscription::STATUS_ACTIVE)
            ->whereNotNull('ends_at')
            ->where('ends_at', '<', now())
            ->update(['status' => Subscription::STATUS_EXPIRED]);

        $this->info("Expired subscriptions marked: {$count}");

        return self::SUCCESS;
    }
}
