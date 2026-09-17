<?php

return [

    /*
    |--------------------------------------------------------------------------
    | REST Countries API
    |--------------------------------------------------------------------------
    |
    | Configuration for the external REST Countries service used to obtain
    | the list of destination countries for the travel insurance quotes.
    |
    */

    'base_url' => env('REST_COUNTRIES_API_URL', 'https://api.restcountries.com/countries/v5'),

    'key' => env('REST_COUNTRIES_API_KEY'),

    // Maximum number of objects per request (capped by the free plan at 100).
    'limit' => (int) env('REST_COUNTRIES_API_LIMIT', 100),

    // Request timeout in seconds.
    'timeout' => (int) env('REST_COUNTRIES_API_TIMEOUT', 5),

    'retry_times' => (int) env('REST_COUNTRIES_API_RETRY_TIMES', 2),

    // Delay between retries in milliseconds.
    'retry_delay' => (int) env('REST_COUNTRIES_API_RETRY_DELAY', 500),

    // Safety cap for the pagination loop.
    'max_pages' => (int) env('REST_COUNTRIES_API_MAX_PAGES', 10),

    // How long the response is cached (seconds).
    'cache_ttl' => (int) env('REST_COUNTRIES_API_CACHE_TTL', 86400),

    // Fields requested from the API to reduce the payload size.
    'fields' => env('REST_COUNTRIES_API_FIELDS', 'names,codes,region,subregion,flag'),

];
