<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Variant extends Model
{
    protected $fillable = ['product_id', 'sku', 'price', 'stock'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // ✅ No withTimestamps() here
    public function options()
    {
        return $this->belongsToMany(VariationOption::class, 'variant_variation_option');
    }
}
