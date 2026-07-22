<?php

namespace App\Services;

use App\Models\MarketPrice;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Refreshes the market_prices table from the provider configured in
 * config/prices.php.
 *
 * Driver architecture: "manual" is a no-op (editors maintain prices in the
 * admin panel), every other provider entry is served by the generic JSON-API
 * driver which pulls a JSON document over HTTP and maps its items onto
 * existing MarketPrice rows by symbol.
 */
class PriceFetcherService
{
    /**
     * Run the configured provider.
     *
     * @return array{provider: string, updated: int, skipped: int, error: string|null}
     */
    public function fetch(): array
    {
        $provider = (string) config('prices.provider', 'manual');

        if ($provider === 'manual') {
            return [
                'provider' => 'manual',
                'updated' => 0,
                'skipped' => 0,
                'error' => null,
            ];
        }

        $config = config("prices.providers.{$provider}");

        if (! is_array($config)) {
            return $this->failure($provider, "Provider [{$provider}] is not defined in config/prices.php.");
        }

        $driver = $config['driver'] ?? 'json_api';

        if ($driver !== 'json_api') {
            return $this->failure($provider, "Unknown price driver [{$driver}].");
        }

        return $this->fetchJsonApi($provider, $config);
    }

    /**
     * Generic JSON-API driver.
     */
    protected function fetchJsonApi(string $provider, array $config): array
    {
        $endpoint = (string) ($config['endpoint'] ?? '');

        if ($endpoint === '') {
            return $this->failure($provider, "Provider [{$provider}] has no endpoint configured.");
        }

        try {
            $request = Http::acceptJson()->timeout((int) ($config['timeout'] ?? 15));

            $query = [];
            $apiKeyEnv = $config['api_key_env'] ?? null;
            $apiKey = $apiKeyEnv ? env($apiKeyEnv) : null;

            if ($apiKey) {
                $param = $config['api_key_param'] ?? 'api_key';

                if (($config['api_key_in'] ?? 'query') === 'header') {
                    $request = $request->withHeaders([$param => $apiKey]);
                } else {
                    $query[$param] = $apiKey;
                }
            }

            $response = $request->get($endpoint, $query);

            if ($response->failed()) {
                return $this->failure($provider, "HTTP {$response->status()} from {$endpoint}.");
            }

            $json = $response->json();

            if (! is_array($json)) {
                return $this->failure($provider, 'Response is not valid JSON.');
            }

            $itemsPath = $config['items_path'] ?? null;
            $items = ($itemsPath === null || $itemsPath === '') ? $json : Arr::get($json, $itemsPath);

            if (! is_array($items)) {
                return $this->failure($provider, "No item list found at path [{$itemsPath}].");
            }

            return $this->applyItems($provider, $items, (array) ($config['map'] ?? []));
        } catch (Throwable $e) {
            return $this->failure($provider, $e->getMessage());
        }
    }

    /**
     * Map fetched items onto existing MarketPrice rows (matched by symbol).
     */
    protected function applyItems(string $provider, array $items, array $map): array
    {
        $symbolKey = $map['symbol'] ?? 'symbol';
        $priceKey = $map['price'] ?? 'price';

        $updated = 0;
        $skipped = 0;
        $now = now();

        foreach ($items as $item) {
            if (! is_array($item)) {
                $skipped++;
                continue;
            }

            $symbol = Arr::get($item, $symbolKey);
            $price = Arr::get($item, $priceKey);

            if ($symbol === null || $price === null || ! is_numeric($price)) {
                $skipped++;
                continue;
            }

            $row = MarketPrice::query()->where('symbol', (string) $symbol)->first();

            if ($row === null) {
                // Editors decide which instruments exist; unknown symbols are ignored.
                $skipped++;
                continue;
            }

            $attributes = [
                'price' => (float) $price,
                'source' => $provider,
                'fetched_at' => $now,
            ];

            foreach (['change_amount', 'change_percent'] as $field) {
                if (isset($map[$field])) {
                    $value = Arr::get($item, $map[$field]);

                    if ($value !== null && is_numeric($value)) {
                        $attributes[$field] = (float) $value;
                    }
                }
            }

            foreach (['name', 'unit'] as $field) {
                if (isset($map[$field])) {
                    $value = Arr::get($item, $map[$field]);

                    if (is_string($value) && $value !== '') {
                        $attributes[$field] = $value;
                    }
                }
            }

            $row->update($attributes);
            $updated++;
        }

        MarketPrice::clearCache();

        return [
            'provider' => $provider,
            'updated' => $updated,
            'skipped' => $skipped,
            'error' => null,
        ];
    }

    protected function failure(string $provider, string $message): array
    {
        Log::warning("PriceFetcherService [{$provider}]: {$message}");

        return [
            'provider' => $provider,
            'updated' => 0,
            'skipped' => 0,
            'error' => $message,
        ];
    }
}
