<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'name',
        'country_id',
        'base_rate',
        'delivery_days',
        'pickup_available',
        'processing_time_id',    // newly added
    ];

    protected $casts = [
        'base_rate'          => 'float',
        'delivery_days'      => 'integer',
        'pickup_available'   => 'boolean',
        'processing_time_id' => 'integer',  // cast as integer
    ];

    /**
     * Each shipping profile belongs to one shop.
     */
    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    /**
     * The processing time (e.g. same‑day, 2‑day) selected for this profile.
     */
    public function processingTime()
    {
        return $this->belongsTo(ProcessingTime::class, 'processing_time_id');
    }

    /**
     * Countries this profile ships to.
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Products using this profile (pivot table).
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_shipping')
                    ->withPivot('is_default')
                    ->withTimestamps();
    }
}
