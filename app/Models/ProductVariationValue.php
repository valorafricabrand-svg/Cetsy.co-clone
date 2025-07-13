<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductVariationValue extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     * (Since we named it singular in the migration.)
     */
    protected $table = 'product_variation_value';

    /** Mass-assignable fields */
    protected $fillable = [
        'product_variation_id',
        'category_attribute_value_id',
    ];

    /**
     * Get the variation that this pivot belongs to.
     */
    public function variation()
    {
        return $this->belongsTo(ProductVariation::class, 'product_variation_id');
    }

    /**
     * Get the attribute value (e.g. “Red”, “M”) for this pivot.
     */
    public function attributeValue()
    {
        return $this->belongsTo(CategoryAttributeValue::class, 'category_attribute_value_id');
    }
}
