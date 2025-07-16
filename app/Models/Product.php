<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'category_id',
        'name',
        'slug',
        'description',
        'price',
        'stock',
        'status',
        'product_type',
        'condition',
        'discount_percent',
        'low_stock',
        'download_file',
        'download_limit',
        'access_expiry',
        'renewal_option',
        'listTypeFee_id',
        'origin_id',
        'origin_postal_code',
        'processing_time_id',
        'local_shipping_service_id',
        'local_shipping_service_other',
        'localshippingPeriod_id',
        'local_default_shipping_price',
        'local_shipping_price',
        'shipping_type',
        'international_shipping_service_id',
        'international_shipping_service_other',
        'internationalshippingPeriod_id',
        'default_shipping_price',
        'shipping_price',
        'shipping_type_other',
        'shipping_type_other_other',
        'shipping_type_other_other_other',
        'shipping_type_other_other_other_other',
        'item_return',
        'item_exchange',
        'total_return_days',
        'type',
        'phone',
        'email',
        'location',
        'tags',
        'is_active',
        'listing_paid_at',
        'next_due_date',
        'featured_image',
        'country_id',
        'renewal_type'
    ];

    /**
     * A product belongs to a shop.
     */
    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    /**
     * A product belongs to a category.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * A product can have multiple media items.
     */
    public function media()
    {
        return $this->hasMany(Media::class);
    }

    /**
     * Use the slug for route model binding.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * A product can have many variants.
     */
    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    /**
     * Track views for analytics.
     */
    public function views()
    {
        return $this->hasMany(ProductView::class);
    }

    /**
     * Digital products can have multiple downloadable files.
     */
    public function digitalFiles()
    {
        return $this->hasMany(DigitalFile::class);
    }

    /**
     * A product can belong to many shipping profiles.
     * Pivot contains `is_default` and timestamps.
     */
    public function shippingProfiles()
    {
        return $this->belongsToMany(ShippingProfile::class, 'product_shipping')
                    ->withPivot('is_default')
                    ->withTimestamps();
    }


    public function reviews()
{
    return $this->hasMany(ProductReview::class);
}

/* Optional shortcuts */
public function scopeWithAvgRating($query)
{
    return $query->withAvg('reviews', 'rating')
                 ->withCount('reviews');
}

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

  public function faqs()
    {
        return $this->hasMany(ProductFaq::class);
    }


    // app/Models/Product.php

public function options()
{
    return $this->hasMany(ProductOption::class);
}

public function variations()
{
    return $this->hasMany(ProductVariation::class);
}



    /** Deals pivot */
    public function deals()
    {
        return $this->belongsToMany(Deal::class);
    }

    /** Returns the active deal (site‑wide or for this product) */
    public function activeDeal()
    {
        $now = Carbon::now();
        return Deal::where('starts_at', '<=', $now)
                   ->where('ends_at', '>=', $now)
                   ->where(function($q) {
                       $q->where('applies_to_all', true)
                         ->orWhereHas('products', fn($qb) =>
                             $qb->where('products.id', $this->id)
                         );
                   })
                   ->first();
    }

    /** Final price after either product % or active deal % */
    public function getDiscountedPriceAttribute()
    {
        $base = $this->price;

        // 1️⃣ product‑level %
        if ($this->discount_percent) {
            return round($base * (1 - $this->discount_percent / 100), 2);
        }

        // 2️⃣ one‑off deal %
        if ($deal = $this->activeDeal()) {
            return round($base * (1 - $deal->discount_percent / 100), 2);
        }

        // 3️⃣ no discount
        return $base;
    }


}
