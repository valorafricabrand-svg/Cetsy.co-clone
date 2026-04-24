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
    'enabled' => false,

    /*
    |--------------------------------------------------------------------------
    | Provider
    |--------------------------------------------------------------------------
    |
    | Supported providers: deepl
    |
    */
    'provider' => 'deepl',

    /*
    |--------------------------------------------------------------------------
    | Dispatch Behavior
    |--------------------------------------------------------------------------
    */
    'auto_translate_on_write' => true,
    'queue' => 'default',
    'timeout' => 20,
    'retries' => 2,
    'chunk_size' => 100,

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
