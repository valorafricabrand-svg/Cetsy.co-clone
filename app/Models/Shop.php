<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Shop extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',

        // Section 1: preferences
        'language',
        'country',
        'currency',

        // Section 2: name & slug
        'name',
        'slug',
        'bio',
        'logo',

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
     * The attributes that should be cast.
     */
    protected $casts = [
        'enable_2fa' => 'boolean',
    ];

    /**
     * Append these accessors to the model's array form.
     */
    protected $appends = [
        'logo_url',
    ];

    /**
     * Use the `slug` column for implicit route model binding.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Get a full URL for the shop logo (or null if none).
     */
    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo
            ? Storage::disk('public')->url($this->logo)
            : null;
    }

    /**
     * A shop belongs to a user.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * A shop has many products.
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * All order items sold by this shop.
     */
    public function orderItems()
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
     * All orders that contain this shop's products.
     */
    public function orders()
    {
        return Order::whereHas('items.product', function ($q) {
            $q->where('shop_id', $this->id);
        });
    }


    public function shippingProfiles()
{
    return $this->hasMany(\App\Models\ShippingProfile::class);
}

}
