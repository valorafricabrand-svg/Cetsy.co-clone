<?php

return [
    'default' => 'en',

    'cookie' => env('APP_LOCALE_COOKIE', 'locale'),

    'catalog' => [
        'en' => [
            'name' => 'English',
            'native' => 'English',
            'html' => 'en',
            'og' => 'en_US',
        ],
        'sw' => [
            'name' => 'Swahili',
            'native' => 'Kiswahili',
            'html' => 'sw',
            'og' => 'sw_KE',
        ],
    ],

    'supported' => [
        'en' => [
            'name' => 'English',
            'native' => 'English',
            'html' => 'en',
            'og' => 'en_US',
        ],
        'sw' => [
            'name' => 'Swahili',
            'native' => 'Kiswahili',
            'html' => 'sw',
            'og' => 'sw_KE',
        ],
    ],
];
