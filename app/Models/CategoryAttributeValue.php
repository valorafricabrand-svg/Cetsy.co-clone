<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryAttributeValue extends Model
{
    protected $fillable = ['category_attribute_id','value'];

    public function attribute()
    {
        return $this->belongsTo(CategoryAttribute::class,'category_attribute_id');
    }


    public function variations()
{
    return $this->belongsToMany(
        \App\Models\ProductVariation::class,
        'product_variation_value',
        'category_attribute_value_id',
        'product_variation_id'
    )->withTimestamps();
}
}
