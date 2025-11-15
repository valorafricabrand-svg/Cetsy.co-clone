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
        'image_path',
        'seller_response',
        'seller_responded_at',
        'approved_at',
        'approved_by',
        'rejected_at',
        'rejected_by',
        'rejection_reason',
        'approved',
        'created_by',
        'updated_at',
        'shop_id',

    ];

    protected $casts = [
        'seller_responded_at' => 'datetime',
    ];

    /* ---------- relationships ---------- */

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }
    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }
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
