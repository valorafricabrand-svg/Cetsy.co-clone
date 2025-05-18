<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Product;

class Shop extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'bio',
        'logo',
    ];

    /**
     * Use the `slug` column for implicit route model binding.
     *
     * @return string
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * A shop belongs to a user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * A shop has many products.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function orderItems()
    {
        return $this->hasManyThrough(
            OrderItem::class,
            Product::class,
            'shop_id',     // Foreign key on products table...
            'product_id',  // Foreign key on order_items table...
            'id',          // Local key on shops table...
            'id'           // Local key on products table...
        );
    }

    /**
     * Orders containing this shop's products.
     * Allows ->count() and ->sum('total').
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function orders()
    {
        return Order::whereHas('items.product', function ($query) {
            $query->where('shop_id', $this->id);
        });
    }
}
