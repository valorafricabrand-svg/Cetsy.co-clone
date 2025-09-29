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
     * Check if this deal is scheduled (not yet started).
     */
    public function isScheduled(): bool
    {
        return $this->starts_at->isFuture();
    }

    /**
     * Check if this deal has expired.
     */
    public function isExpired(): bool
    {
        return $this->ends_at->isPast();
    }

    /**
     * Get the deal status as a string.
     */
    public function getStatusAttribute(): string
    {
        if ($this->isActive()) {
            return 'active';
        } elseif ($this->isScheduled()) {
            return 'scheduled';
        } else {
            return 'expired';
        }
    }

    /**
     * Get the deal status badge class for UI.
     */
    public function getStatusBadgeClass(): string
    {
        return match($this->getStatusAttribute()) {
            'active' => 'bg-success',
            'scheduled' => 'bg-warning',
            'expired' => 'bg-secondary',
            default => 'bg-secondary'
        };
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
