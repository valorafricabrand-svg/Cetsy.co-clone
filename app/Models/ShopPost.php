<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopPost extends Model
{
    protected $fillable = ['shop_id', 'title', 'description', 'image', 'status', 'published_at', 'expired_at'];

    protected $casts = [
        'published_at' => 'datetime',
        'expired_at' => 'datetime',
    ];

    public function shop() { return $this->belongsTo(Shop::class); }
}
