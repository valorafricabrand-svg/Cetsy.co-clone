<?php

// app/Models/ShippingProfile.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingProfile extends Model
{
    protected $fillable = [
        'shop_id',
        'name',
        'country_id',
        'base_rate',
        'delivery_days',
        'pickup_available',
    ];

    protected $casts = [
        'base_rate'        => 'float',
        'delivery_days'    => 'integer',
        'pickup_available' => 'boolean',
    ];

    /**
     * Each shipping profile belongs to one shop.
     */
    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }


    public function products()
{
    return $this->belongsToMany(Product::class, 'product_shipping')
                ->withPivot('is_default')
                ->withTimestamps();
}

   public function country()
    {
        return $this->belongsTo(Country::class);
    }

}
