<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductOption extends Model
{
    protected $fillable = ['product_id', 'name'];

    /* option belongs to one product */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /* one option → many possible values */
    public function values()
    {
        return $this->hasMany(ProductOptionValue::class)->orderBy('value');
    }
}
