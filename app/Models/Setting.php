<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    /**
     * The table associated with the model.
     *
     * (only needed if your table name is non-standard;
     * by default Eloquent will use the plural of the model name)
     */
    protected $table = 'settings';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'site_name',
        'meta_description',
        'phone',
        'email',
        'facebook_url',
        'instagram_url',
        'x_url',
        'linkedin_url',
        'tiktok_url',
        'paypal_client_id',
        'default_currency',
        'youtube_url',
        'whatsapp_number',
        'address',
        'timezone',
    ];

    /**
     * The model's default values for attributes.
     */
    protected $attributes = [
        'site_name'         => '',
        'meta_description'  => '',
        'phone'             => '',
        'email'             => '',
        'facebook_url'      => '',
        'instagram_url'     => '',
        'x_url'             => '',
        'linkedin_url'      => '',
        'tiktok_url'        => '',
        'paypal_client_id'  => '',
        // you can also set a default currency here if you like:
        // 'default_currency' => 'USD',
        'youtube_url'       => '',
        'whatsapp_number'   => '',
        'address'           => '',
        'timezone'          => '',
    ];

    /**
     * Disable timestamps if your settings table doesn't have created_at/updated_at.
     */
    public $timestamps = false;
}
