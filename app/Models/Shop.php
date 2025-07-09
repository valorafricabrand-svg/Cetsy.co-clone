<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Review;

/**
 * Class Shop
 *
 * Represents a vendor’s storefront with preferences, branding,
 * payment and billing details. Automatically creates a default
 * shipping profile on creation.
 *
 * @property int         $id
 * @property int         $user_id
 * @property string|null $language
 * @property string|null $country
 * @property string|null $currency
 * @property string      $name
 * @property string      $slug
 * @property string|null $bio
 * @property string|null $logo
 * @property string|null $bank_account
 * @property string|null $routing_number
 * @property string|null $address
 * @property string|null $city
 * @property string|null $postal
 * @property bool        $enable_2fa
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read string|null     $logo_url
 * @property-read \App\Models\User         $user
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Product[] $products
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ShippingProfile[] $shippingProfiles
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\OrderItem[]     $orderItems
 * @property-read Builder           $orders
 *
 * @package App\Models
 */
class Shop extends Model
{
    use HasFactory;

    /**
     * Mass-assignable attributes.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        // Section 1: preferences
        'user_id',
        'language',
        'country',
        'currency',

        // Section 2: name & slug
        'name',
        'slug',
        'bio',
        'logo',
        'featured_image',
        'announcement',
        'policies',

        // Section 3: payment
        'bank_account',
        'routing_number',

        // Section 4: billing
        'address',
        'city',
        'postal',

        // Section 5: security
        'enable_2fa',
    ];

    /**
     * Attribute type casts.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'enable_2fa' => 'boolean',
    ];

    /**
     * Additional accessors to append to model arrays.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'logo_url',
    ];

    /**
     * Model booted callback to attach event listeners.
     */
    protected static function booted(): void
    {
        static::created(function (Shop $shop): void {
            // Create a default shipping profile with base_rate = 0
            $shop->shippingProfiles()->create([
                'name'             => 'Default Shipping',
                'country_id'          => "",
                'base_rate'        => 0,
                'delivery_days'    => 0,
                'pickup_available' => false,
            ]);
        });
    }

    /**
     * Use the `slug` column for route model binding.
     *
     * @return string
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Accessor: full public URL for the shop logo.
     *
     * @return string|null
     */
    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo
            ? Storage::disk('public')->url($this->logo)
            : null;
    }

    /**
     * Accessor: full public URL for the shop featured image.
     *
     * @return string|null
     */
    public function getFeaturedImageUrlAttribute(): ?string
    {
        return $this->featured_image
            ? Storage::disk('public')->url($this->featured_image)
            : null;
    }

    /**
     * A shop belongs to a single user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * A shop can have many products.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * A shop may have multiple shipping profiles.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function shippingProfiles(): HasMany
    {
        return $this->hasMany(ShippingProfile::class);
    }

    /**
     * All order items sold by this shop.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function orderItems(): HasManyThrough
    {
        return $this->hasManyThrough(
            OrderItem::class,
            Product::class,
            'shop_id',    // FK on products table...
            'product_id', // FK on order_items table...
            'id',         // Local key on shops table...
            'id'          // Local key on products table...
        );
    }

    /**
     * All orders that include items from this shop.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function orders(): Builder
    {
        return Order::whereHas('items.product', function (Builder $query) {
            $query->where('shop_id', $this->id);
        });
    }

    /**
     * A shop can have multiple media items.
     */
    public function media()
    {
        return $this->hasMany(Media::class);
    }

    /**
     * All reviews for this shop.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }
}
