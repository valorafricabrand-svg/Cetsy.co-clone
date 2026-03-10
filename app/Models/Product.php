<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

class Product extends Model
{
    use HasFactory;

    public const TYPE_PHYSICAL = 'physical';
    public const TYPE_DIGITAL = 'digital';
    public const TYPE_SERVICE = 'service';

    // Ensure computed price (after deals or product discount) appears in JSON
    protected $appends = ['discounted_price'];

    protected $fillable = [
        'shop_id',
        'category_id',
        'name',
        'slug',
        'sku',
        'description',
        'price',
        'discount_price',
        'image',
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
        'pickup_available',
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
        'renewal_type',
        'tags'
    ];

    protected $casts = [
        'pickup_available' => 'boolean',
    ];

    public static function mapCategoryListingTypeToProductType(?string $listingType): ?string
    {
        return match (strtolower((string) $listingType)) {
            'products' => self::TYPE_PHYSICAL,
            'services' => self::TYPE_SERVICE,
            'digital' => self::TYPE_DIGITAL,
            default => null,
        };
    }

    public static function mapProductTypeToCategoryListingType(?string $type): ?string
    {
        return match (strtolower((string) $type)) {
            self::TYPE_PHYSICAL => 'products',
            self::TYPE_SERVICE => 'services',
            self::TYPE_DIGITAL => 'digital',
            default => null,
        };
    }

    public static function resolveTypeForCategory(?string $type, Category|int|null $category = null): ?string
    {
        $normalizedType = strtolower((string) $type);
        $normalizedType = in_array($normalizedType, [
            self::TYPE_PHYSICAL,
            self::TYPE_DIGITAL,
            self::TYPE_SERVICE,
        ], true) ? $normalizedType : null;

        $categoryModel = null;
        if ($category instanceof Category) {
            $categoryModel = $category;
        } elseif (! empty($category)) {
            $categoryModel = Category::query()
                ->select(['id', 'listing_type'])
                ->find((int) $category);
        }

        return self::mapCategoryListingTypeToProductType($categoryModel->listing_type ?? null) ?? $normalizedType;
    }

    public function getEffectiveTypeAttribute(): ?string
    {
        $category = $this->relationLoaded('category') ? $this->category : $this->category_id;

        return self::resolveTypeForCategory($this->type, $category);
    }

    public function scopeWhereDisplayType(Builder $query, string $type): Builder
    {
        $type = strtolower(trim($type));
        if (! in_array($type, [self::TYPE_PHYSICAL, self::TYPE_DIGITAL, self::TYPE_SERVICE], true)) {
            return $query;
        }

        $listingType = self::mapProductTypeToCategoryListingType($type);

        return $query->where(function (Builder $outer) use ($type, $listingType) {
            if ($listingType) {
                $outer->whereHas('category', function (Builder $categoryQuery) use ($listingType) {
                    $categoryQuery->where('listing_type', $listingType);
                });
            }

            $outer->orWhere(function (Builder $fallback) use ($type) {
                $fallback->where('type', $type)
                    ->where(function (Builder $categoryFallback) {
                        $categoryFallback->whereNull('category_id')
                            ->orWhereDoesntHave('category')
                            ->orWhereHas('category', function (Builder $categoryQuery) {
                                $categoryQuery->whereNull('listing_type')
                                    ->orWhere('listing_type', '');
                            });
                    });
            });
        });
    }

    /**
     * Determine if the product should be considered reserved:
     * - Only for physical products with stock exactly 1
     * - There exists a pending order item referencing this product (unpaid)
     */
    public function getIsReservedAttribute(): bool
    {
        try {
            if (($this->type ?? null) !== 'physical') return false;
            $stock = (int) ($this->stock ?? 0);
            if ($stock !== 1) return false;

            return \App\Models\OrderItem::where('product_id', $this->id)
                ->whereHas('order', function ($q) {
                    $q->where('status', \App\Models\Order::STATUS_PENDING)
                      ->whereDoesntHave('payments', function($pq){
                          $pq->where('paymentStatus', 3); // successful
                      });
                })
                ->exists();
        } catch (\Throwable $e) {
            return false;
        }
    }

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




public function variationTypes() {
    return $this->hasMany(VariationType::class);
}
public function variations() {
    return $this->hasMany(Variant::class)->with('options');
}



    /** Deals pivot */
    public function deals()
    {
        return $this->belongsToMany(Deal::class);
    }

    /** Returns the active deal for this product's shop (site-wide within the shop or specific to this product) */
    public function activeDeal()
    {
        $now = Carbon::now();
        return Deal::where('starts_at', '<=', $now)
                   ->where('ends_at', '>=', $now)
                   // Important: only consider deals from the same shop
                   ->where('shop_id', $this->shop_id)
                   ->where(function($q) {
                       $q->where('applies_to_all', true)
                         ->orWhereHas('products', fn($qb) =>
                             $qb->where('products.id', $this->id)
                         );
                   })
                   ->first();
    }

    /**
     * Apply the product or active deal discount to a given price.
     */
    public function applyDiscount(float $price): float
    {
        if ($this->discount_percent) {
            return round($price * (1 - $this->discount_percent / 100), 2);
        }

        if ($deal = $this->activeDeal()) {
            return round($price * (1 - $deal->discount_percent / 100), 2);
        }

        return $price;
    }

    /** Final price after either explicit sale price, product %, or active deal % */
    public function getDiscountedPriceAttribute()
    {
        $price = (float) ($this->price ?? 0);

        // If an explicit sale/discount price is set and lower than base, prefer it
        if (!is_null($this->discount_price) && (float) $this->discount_price > 0 && (float) $this->discount_price < $price) {
            return (float) $this->discount_price;
        }

        // Otherwise, apply dynamic discount_percent or active deal to base price
        return $this->applyDiscount($price);
    }






}
