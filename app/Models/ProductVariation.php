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
            ProductOptionValue::class,
            'product_variation_value',        // pivot table
            'product_variation_id',
            'product_option_value_id'
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
