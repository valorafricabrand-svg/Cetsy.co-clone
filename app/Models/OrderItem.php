<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'price',
        'shipping_profile_id',  // Add this field here
        'shipping_cost'
    ];

    /**
     * Get the order that owns this item.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the product that was ordered.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the shipping profile selected for this order item.
     */
    public function shippingProfile()
    {
        return $this->belongsTo(ShippingProfile::class, 'shipping_profile_id');
    }


     public function variation()
    {
        return $this->belongsTo(ProductVariation::class, 'product_variation_id');
    }
}
