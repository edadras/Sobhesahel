<?php

namespace App\Console\Commands;

use App\Services\PriceFetcherService;
use Illuminate\Console\Command;

class FetchPricesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-prices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch market prices (currency, gold, coin, crypto) from the provider configured in config/prices.php and update market_prices rows';

    /**
     * Execute the console command.
     */
    public function handle(PriceFetcherService $fetcher): int
    {
        $result = $fetcher->fetch();

        if ($result['provider'] === 'manual') {
            $this->info('Prices provider is "manual" — nothing fetched, prices are managed from the admin panel.');

            return self::SUCCESS;
        }

        if ($result['error'] !== null) {
            $this->error("Provider [{$result['provider']}] failed: {$result['error']}");

            return self::FAILURE;
        }

        $this->info("Provider [{$result['provider']}]: {$result['updated']} price(s) updated, {$result['skipped']} item(s) skipped.");

        return self::SUCCESS;
    }
}
