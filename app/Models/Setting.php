<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    /**
     * Table name (only needed if non-standard).
     */
    protected $table = 'settings';

    /**
     * Mass-assignable attributes.
     */
    protected $fillable = [
        // Branding
        'site_name',
        'meta_description',
        'logo_url',
        'favicon_url',

        // Contact
        'phone',
        'email',
        'address',
        'whatsapp_number',
        'timezone',

        // Social
        'facebook_url',
        'instagram_url',
        'x_url',
        'linkedin_url',
        'tiktok_url',
        'youtube_url',

        // Payment
        'paypal_client_id',
        'default_currency',
        'paypal_transaction_fee_percent',
        // Payouts
        'fee_rate',        // percent, e.g. 1.5
        'min_amount',      // minimum payout amount
        'auto_release_days', // days before auto-releasing on-hold funds
        // Payout scheduling
        'payout_schedule',      // manual|weekly|biweekly|monthly
        'payout_weekday',       // 0=Sun .. 6=Sat
        'payout_month_day',     // 1..28
        'payout_auto_approve',  // auto-approve pending payouts on schedule
        'payout_auto_disburse', // auto-send for supported methods

        // Shipping defaults
        'couriers_json',
    ];

    /**
     * Default attribute values.
     */
    protected $attributes = [
        'site_name'        => '',
        'meta_description' => '',

        'logo_url'         => '',
        'favicon_url'      => '',

        'phone'            => '',
        'email'            => '',
        'address'          => '',
        'whatsapp_number'  => '',
        'timezone'         => '',

        'facebook_url'     => '',
        'instagram_url'    => '',
        'x_url'            => '',
        'linkedin_url'     => '',
        'tiktok_url'       => '',
        'youtube_url'      => '',

        'paypal_client_id' => '',
        'fee_rate'         => 1.5,
        'min_amount'       => 1.0,
        'auto_release_days'=> 3,
        'default_currency' => 'USD',
        'payout_schedule'  => 'manual',
        'payout_weekday'   => 5,   // Friday
        'payout_month_day' => 15,  // 15th
        'payout_auto_approve'  => false,
        'payout_auto_disburse' => false,
    ];

    /**
     * Disable timestamps (settings table has no created_at/updated_at).
     */
    public $timestamps = false;
}
