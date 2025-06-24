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
        // 'default_currency' => 'USD', // uncomment if you want a hard default
    ];

    /**
     * Disable timestamps (settings table has no created_at/updated_at).
     */
    public $timestamps = false;
}
