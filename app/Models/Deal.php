<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Carbon\Carbon;
use App\Models\Product;

class Deal extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'name',
        'discount_percent',
        'applies_to_all',
        'starts_at',
        'ends_at',
        'shop_id'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'discount_percent' => 'integer',
        'applies_to_all'   => 'boolean',
        'starts_at'        => 'datetime',
        'ends_at'          => 'datetime',
    ];

    /**
     * The products that belong to this deal.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'deal_product',  // pivot table
            'deal_id',
            'product_id'
        );
    }

    /**
     * Scope a query to only include currently active deals.
     */
    public function scopeActive($query)
    {
        $now = Carbon::now();
        return $query->where('starts_at', '<=', $now)
                     ->where('ends_at', '>=', $now);
    }

    /**
     * Check if this deal is currently active.
     */
    public function isActive(): bool
    {
        $now = Carbon::now();
        return $this->starts_at->lte($now) && $this->ends_at->gte($now);
    }

    /**
     * Determine if this deal applies to the given product.
     *
     * @param  int|Product  $product
     */
    public function appliesTo($product): bool
    {
        $id = $product instanceof Product
            ? $product->id
            : (int) $product;

        if ($this->applies_to_all) {
            return true;
        }

        // lazy‑load pivot if needed
        return $this->products->contains('id', $id);
    }
}
