<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingProfile extends Model
{
    protected $fillable = [
        'shop_id',
        'name',
        'country',
        'base_rate',
        'delivery_days',
        'pickup_available',
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

}
