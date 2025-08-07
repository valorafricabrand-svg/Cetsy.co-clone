<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingProfile extends Model
{
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | Constants
    |--------------------------------------------------------------------------
    */
    public const DEST_COUNTRY          = 'country';
    public const DEST_EVERYWHERE_ELSE  = 'everywhere_else';

    public const CHARGE_FIXED          = 'fixed';
    public const CHARGE_FREE           = 'free';

    /*
    |--------------------------------------------------------------------------
    | Mass-assignable columns
    |--------------------------------------------------------------------------
    | NOTE:
    | - Each row represents ONE destination/service rule for a product profile.
    | - We keep origin + processing on every row (duplicated inside a profile_name group).
    */
    protected $fillable = [
        // Ownership / grouping
        'shop_id',
        'profile_name',       // e.g. "Standard shipping" (group name)
        'name', 
        'product_id',              // (legacy) kept for backward compatibility

        // Origin (ship-from scope for this profile group)
        'country_id',         // origin country_id
        'origin_postal_code',

        // Processing time (preset id OR custom range)
        'processing_time_id',     // 1..5 (nullable if custom)
        'processing_custom_min',  // nullable
        'processing_custom_max',  // nullable

        // Destination rule (this row)
        'dest_location_type',     // 'country' | 'everywhere_else'
        'dest_country_id',        // nullable when everywhere_else
        'service',                // e.g. "Courier", "Postal", "Express"
        'days_min',               // nullable smallint
        'days_max',               // nullable smallint
        'charge_type',            // 'fixed' | 'free'

        // Rates
        'base_rate',              // price_one  → base_rate
        'additional_rate',        // price_two  → additional_rate

        // Optional package details (if you use them)
        'weight',
        'length',
        'width',
        'height',
        'shipping_class',
        'requires_shipping',

        // Legacy/simple fields you mentioned earlier (kept if you still use them)
        'delivery_days',
        'pickup_available',
    ];

    /*
    |--------------------------------------------------------------------------
    | Attribute casting
    |--------------------------------------------------------------------------
    */
    protected $casts = [
        'processing_time_id'     => 'integer',
        'processing_custom_min'  => 'integer',
        'processing_custom_max'  => 'integer',

        'days_min'               => 'integer',
        'days_max'               => 'integer',

        'base_rate'              => 'decimal:2',
        'additional_rate'        => 'decimal:2',

        'requires_shipping'      => 'boolean',
        'pickup_available'       => 'boolean',
        'delivery_days'          => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /** Ship-from country (origin) */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    /** Optional: processing preset */
    public function processingTime()
    {
        return $this->belongsTo(ProcessingTime::class, 'processing_time_id');
    }

    /** Profiles attached to products (per-product default kept on the pivot) */
    public function products()
    {
        // Pivot table: product_shipping (product_id, shipping_profile_id, is_default, timestamps)
        return $this->belongsToMany(Product::class, 'product_shipping', 'shipping_profile_id', 'product_id')
            ->withPivot('is_default')
            ->withTimestamps();
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    /** Limit by shop id */
    public function scopeForShop($query, int $shopId)
    {
        return $query->where('shop_id', $shopId);
    }

    /** Limit by profile group name (e.g. "Standard shipping") */
    public function scopeProfileName($query, ?string $name)
    {
        return $name ? $query->where('profile_name', $name) : $query;
    }

    /** Filter a destination (country vs everywhere_else) */
    public function scopeDestination($query, string $type, ?int $countryId = null)
    {
        $query->where('dest_location_type', $type);
        if ($type === self::DEST_COUNTRY) {
            $query->where('dest_country_id', $countryId);
        }
        return $query;
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors / Mutators (backward-compatibility)
    |--------------------------------------------------------------------------
    */

    /** Treat `name` and `profile_name` as aliases; prefer profile_name */
    public function getNameAttribute(?string $value): ?string
    {
        return $this->attributes['profile_name'] ?? $value;
    }

    public function setNameAttribute(?string $value): void
    {
        // Keep legacy compatibility: writing name also writes profile_name
        $this->attributes['name'] = $value;
        $this->attributes['profile_name'] = $value;
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /** True if using a custom processing range (no preset id, but min/max set) */
    public function getUsesCustomProcessingAttribute(): bool
    {
        return is_null($this->processing_time_id)
            && !is_null($this->processing_custom_min)
            && !is_null($this->processing_custom_max);
    }

    /** Human label for destination */
    public function getDestinationLabelAttribute(): string
    {
        if ($this->dest_location_type === self::DEST_EVERYWHERE_ELSE) {
            return 'Everywhere else';
        }
        return optional($this->destCountry)->name ?? 'Country';
    }




    // in app/Models/ShippingProfile.php

public function destCountry()
{
    return $this->belongsTo(Country::class, 'dest_country_id');
}

}
