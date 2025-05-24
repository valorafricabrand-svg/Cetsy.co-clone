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
}
