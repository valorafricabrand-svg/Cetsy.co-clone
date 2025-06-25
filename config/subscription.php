<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Subscription Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration settings for seller subscriptions.
    |
    */

    'monthly_fee' => env('SUBSCRIPTION_MONTHLY_FEE', 5),

    /*
    |--------------------------------------------------------------------------
    | Subscription Features
    |--------------------------------------------------------------------------
    |
    | Features included with an active subscription.
    |
    */
    'features' => [
        'access_seller_dashboard' => true,
        'list_unlimited_products' => true,
        'process_orders' => true,
        'access_analytics' => true,
        'kyc_verification' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Subscription Duration
    |--------------------------------------------------------------------------
    |
    | Default subscription duration in months.
    |
    */
    'duration_months' => 1,

    /*
    |--------------------------------------------------------------------------
    | Grace Period
    |--------------------------------------------------------------------------
    |
    | Grace period in days after subscription expires before features are disabled.
    |
    */
    'grace_period_days' => 7,
]; 