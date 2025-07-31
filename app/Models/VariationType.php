<?php
// app/Models/VariationType.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VariationType extends Model
{
    protected $fillable = [
        'product_id',
        'name',
        'link_photos',
    ];

    /**
     * The product this variation type belongs to.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * All options (e.g. “Red”, “Large”) for this type.
     */
    public function options(): HasMany
    {
        return $this->hasMany(VariationOption::class);
    }
}
