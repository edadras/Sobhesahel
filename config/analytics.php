<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Matomo (self-hosted analytics)
    |--------------------------------------------------------------------------
    | When matomo_url and matomo_token are both set, the admin dashboard
    | statistics widgets will fetch live visitor data from the Matomo HTTP
    | API. When they are empty, the widgets silently fall back to internal
    | (database) statistics and show "—" placeholders for live numbers.
    */
    'matomo_url' => env('MATOMO_URL'),
    'matomo_token' => env('MATOMO_TOKEN'),
    'matomo_site_id' => env('MATOMO_SITE_ID', 1),

    /*
     * The property id of which you want to display data.
     */
    'property_id' => env('ANALYTICS_PROPERTY_ID'),

    /*
     * Path to the client secret json file. Take a look at the README of this package
     * to learn how to get this file. You can also pass the credentials as an array
     * instead of a file path.
     */
    'service_account_credentials_json' => storage_path('app/analytics/service-account-credentials.json'),

    /*
     * The amount of minutes the Google API responses will be cached.
     * If you set this to zero, the responses won't be cached at all.
     */
    'cache_lifetime_in_minutes' => 60 * 24,

    /*
     * Here you may configure the "store" that the underlying Google_Client will
     * use to store it's data.  You may also add extra parameters that will
     * be passed on setCacheConfig (see docs for google-api-php-client).
     *
     * Optional parameters: "lifetime", "prefix"
     */
    'cache' => [
        'store' => 'file',
    ],
];
