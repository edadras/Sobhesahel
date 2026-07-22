<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Market prices provider
    |--------------------------------------------------------------------------
    |
    | Which provider PriceFetcherService uses to refresh the market_prices
    | table. "manual" disables remote fetching entirely: prices are then only
    | maintained by editors through the Filament admin panel (default, works
    | with no external service or API key).
    |
    | Any other value must match a key of the "providers" array below.
    |
    */

    'provider' => env('PRICES_PROVIDER', 'manual'),

    /*
    |--------------------------------------------------------------------------
    | Providers
    |--------------------------------------------------------------------------
    |
    | Each non-manual provider is handled by the generic JSON-API driver:
    |
    |   endpoint     Full URL returning JSON.
    |   api_key_env  Name of the env var holding the API key (never the key
    |                itself). Sent according to "api_key_in"/"api_key_param".
    |   api_key_in   "query" (default) or "header".
    |   api_key_param  Query-string key or header name for the API key.
    |   timeout      HTTP timeout in seconds.
    |   items_path   Dot-notation path to the array of price items inside the
    |                JSON response ("" / null when the root is the array).
    |   map          Maps MarketPrice fields to keys inside each item:
    |                symbol (required), price (required), change_amount,
    |                change_percent, name, unit. Dot-notation supported.
    |
    | Only rows whose "symbol" already exists in market_prices are updated —
    | editors stay in control of which instruments appear and how they are
    | named/ordered; the fetcher only refreshes numbers.
    |
    */

    'providers' => [

        // Example generic JSON API provider. Point it at any endpoint that
        // returns a JSON list of prices and adjust the mapping accordingly.
        'json_api' => [
            'driver' => 'json_api',
            'endpoint' => env('PRICES_API_ENDPOINT', ''),
            'api_key_env' => 'PRICES_API_KEY',
            'api_key_in' => 'query',
            'api_key_param' => 'api_key',
            'timeout' => 15,
            'items_path' => 'data',
            'map' => [
                'symbol' => 'symbol',
                'price' => 'price',
                'change_amount' => 'change_amount',
                'change_percent' => 'change_percent',
            ],
        ],

    ],

];
