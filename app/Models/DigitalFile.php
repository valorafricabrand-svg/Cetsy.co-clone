<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DigitalFile extends Model
{
    protected $fillable = [
        'product_id',
        'filename',
        'filepath',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
