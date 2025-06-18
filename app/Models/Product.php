<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'discount_price',
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
        'price_type',
        'available_days',
        'available_time',
        'available_time_to',
        'available_time_from',
        'duration_value',
        'duration_unit',
        'is_remote',
        'is_active',
    ];

    protected $casts = [
        'available_days' => 'array',
        'is_remote' => 'boolean',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function media()
    {
        return $this->hasMany(Media::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }


        public function views()
{
    return $this->hasMany(ProductView::class);
}


// Product.php

public function digitalFiles()
{
    return $this->hasMany(DigitalFile::class);
}


public function shippingProfiles()
{
    return $this->belongsToMany(ShippingProfile::class, 'product_shipping')
                ->withPivot('is_default')
                ->withTimestamps();
}



}
