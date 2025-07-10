<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopPolicy extends Model
{
    protected $fillable = ['shop_id', 'shipping', 'returns'];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }
}

