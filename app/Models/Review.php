<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'order_id',
        'order_item_id',
        'rating',
        'comment',
        'approved',
        'shop_id',
    ];

    /* ---------- relationships ---------- */

    public function order()     { return $this->belongsTo(Order::class); }
    public function orderItem() { return $this->belongsTo(OrderItem::class); }
    public function shop() { return $this->belongsTo(Shop::class); }
    public function user() 
    { 
        return $this->hasOneThrough(
            User::class,
            Order::class,
            'id', // Foreign key on orders table
            'id', // Foreign key on users table
            'order_id', // Local key on reviews table
            'user_id' // Local key on orders table
        );
    }
}
