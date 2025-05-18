<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    // allow mass assignment
    protected $fillable = [
        'user_id',
        'shipping_address',
        'total',
        'payment_method',
    ];

    /**
     * The user who placed this order.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The items on this order.
     * Assumes you have an OrderItem model and order_items table.
     */
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
    
}
