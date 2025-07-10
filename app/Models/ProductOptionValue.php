<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductOptionValue extends Model
{
    protected $fillable = ['product_option_id', 'value'];

    /* value belongs to a single option */
    public function option()
    {
        return $this->belongsTo(ProductOption::class);
    }

    /* value participates in many variations */
    public function variations()
    {
        return $this->belongsToMany(
            ProductVariation::class,
            'product_variation_value',
            'product_option_value_id',
            'product_variation_id'
        )->withTimestamps();
    }
}
