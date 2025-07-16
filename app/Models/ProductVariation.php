<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductVariation extends Model
{
    use HasFactory;

    /* ───────────── fillable / casts ───────────── */
    protected $fillable = [
        'product_id',
        'sku',
        'price',
        'stock',
        'image',
                'type',
        'variation_option',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
    ];

    /* ───────────── relationships ───────────── */

    /** Parent product */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /** Option-values that define this variation (Color: Red, Size: M, …) */
    public function values()
    {
        return $this->belongsToMany(
            \App\Models\CategoryAttributeValue::class,
            'product_variation_value',           // pivot table
            'product_variation_id',              // this model’s FK on pivot
            'category_attribute_value_id'        // related model’s FK on pivot
        )->withTimestamps();
    }

    /* ───────────── accessor helpers (optional) ───────────── */

    /** e.g. “Red / M” */
    public function getLabelAttribute()
    {
        return $this->values->pluck('value')->implode(' / ');
    }

    /** remaining stock after cart reservations, etc. */
    public function getAvailableStockAttribute()
    {
        // adjust if you hold reservations; by default same as stock
        return $this->stock;
    }
}
