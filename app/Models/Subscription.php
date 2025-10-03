<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $fillable = [
        'user_id',
        'shop_id',
        'status',
        'start_date',
        'end_date',
        'amount',
        'payment_method',
        'transaction_id',
        'notes'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'amount' => 'decimal:2'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && 
               $this->end_date && 
               $this->end_date->isFuture();
    }
} 
