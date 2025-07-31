<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VariationOption extends Model
{
    protected $fillable = ['variation_type_id', 'value'];

    public function variationType()
    {
        return $this->belongsTo(VariationType::class);
    }

    // ✅ No withTimestamps() here
    public function variants()
    {
        return $this->belongsToMany(Variant::class, 'variant_variation_option');
    }
}
