<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Auto Translation
    |--------------------------------------------------------------------------
    |
    | When enabled, products and shops with missing translated marketplace
    | content will be queued for background translation after save.
    |
    */
    'enabled' => (bool) env('AUTO_TRANSLATION_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Provider
    |--------------------------------------------------------------------------
    |
    | Supported providers: deepl
    |
    */
    'provider' => env('AUTO_TRANSLATION_PROVIDER', 'deepl'),

    /*
    |--------------------------------------------------------------------------
    | Dispatch Behavior
    |--------------------------------------------------------------------------
    */
    'auto_translate_on_write' => (bool) env('AUTO_TRANSLATION_ON_WRITE', true),
    'queue' => env('AUTO_TRANSLATION_QUEUE', 'default'),
    'timeout' => (int) env('AUTO_TRANSLATION_TIMEOUT', 20),
    'retries' => (int) env('AUTO_TRANSLATION_RETRIES', 2),
    'chunk_size' => (int) env('AUTO_TRANSLATION_CHUNK_SIZE', 100),

    /*
    |--------------------------------------------------------------------------
    | Provider-specific options
    |--------------------------------------------------------------------------
    */
    'providers' => [
        'deepl' => [
            'locale_map' => [
                'en' => 'EN',
                'sw' => 'SW',
            ],
        ],
    ],
];
