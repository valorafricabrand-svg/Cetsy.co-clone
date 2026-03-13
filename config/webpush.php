<?php

return [
    'enabled' => filter_var(env('WEBPUSH_ENABLED', true), FILTER_VALIDATE_BOOL)
        && filled(env('WEBPUSH_VAPID_PUBLIC_KEY'))
        && filled(env('WEBPUSH_VAPID_PRIVATE_KEY')),

    'vapid' => [
        'subject' => env('WEBPUSH_VAPID_SUBJECT', 'mailto:' . env('SUPPORT_EMAIL', env('MAIL_FROM_ADDRESS', 'support@example.com'))),
        'public_key' => env('WEBPUSH_VAPID_PUBLIC_KEY'),
        'private_key' => env('WEBPUSH_VAPID_PRIVATE_KEY'),
    ],

    'icon' => env('WEBPUSH_ICON', rtrim(env('APP_URL', 'http://localhost'), '/') . '/assets/images/cetsylogmain.png'),
    'badge' => env('WEBPUSH_BADGE', rtrim(env('APP_URL', 'http://localhost'), '/') . '/favicon.ico'),
];
