<?php
// app/Models/Offer.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Offer extends Model
{
    protected $fillable = [
        'product_id', 
        'buyer_id', 
        'offer_price', 
        'status',
        'is_counter_offer',
        'original_offer_id',
        'seller_notes',
        'buyer_notes',
        'order_id'
    ];

    protected $casts = [
        'is_counter_offer' => 'boolean',
        'offer_price' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = [
        'status_badge_class',
        'status_label',
        'formatted_price',
        'time_ago',
        'is_negotiable'
    ];

    public function product() { 
        return $this->belongsTo(Product::class); 
    }
    
    public function buyer() { 
        return $this->belongsTo(User::class, 'buyer_id'); 
    }

    public function originalOffer() {
        return $this->belongsTo(Offer::class, 'original_offer_id');
    }

    public function counterOffers() {
        return $this->hasMany(Offer::class, 'original_offer_id');
    }

    public function seller() {
        return $this->product->shop->user ?? null;
    }

    public function order() {
        return $this->belongsTo(Order::class);
    }

    // Scopes for filtering
    public function scopePending(Builder $query): void
    {
        $query->where('status', 'pending');
    }

    public function scopeAccepted(Builder $query): void
    {
        $query->where('status', 'accepted');
    }

    public function scopeDeclined(Builder $query): void
    {
        $query->where('status', 'declined');
    }

    public function scopeCounterOffers(Builder $query): void
    {
        $query->where('is_counter_offer', true);
    }

    public function scopeOriginalOffers(Builder $query): void
    {
        $query->where('is_counter_offer', false);
    }

    public function scopeForProduct(Builder $query, $productId): void
    {
        $query->where('product_id', $productId);
    }

    public function scopeForBuyer(Builder $query, $buyerId): void
    {
        $query->where('buyer_id', $buyerId);
    }

    // Status badge and label methods
    public function getStatusBadgeClassAttribute()
    {
        return match ($this->status) {
            'pending' => 'bg-warning text-dark',
            'accepted' => 'bg-success',
            'declined' => 'bg-danger',
            'expired' => 'bg-secondary',
            default => 'bg-secondary',
        };
    }

    public function getStatusLabelAttribute()
    {
        if ($this->status === 'pending') {
            // Clarify which side is expected to respond
            return $this->is_counter_offer ? 'Pending buyer response' : 'Pending seller response';
        }
        return match ($this->status) {
            'accepted' => 'Accepted',
            'declined' => 'Declined',
            'expired'  => 'Expired',
            default    => ucfirst((string)$this->status),
        };
    }

    public function getFormattedPriceAttribute()
    {
        return get_currency() . ' ' . number_format($this->offer_price, 2);
    }

    public function getTimeAgoAttribute()
    {
        return $this->created_at ? $this->created_at->diffForHumans() : '';
    }

    public function getIsNegotiableAttribute()
    {
        return $this->status === 'pending';
    }

    // Status check methods
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isAccepted()
    {
        return $this->status === 'accepted';
    }

    public function isDeclined()
    {
        return $this->status === 'declined';
    }

    public function isExpired()
    {
        return $this->status === 'expired';
    }

    public function isCounterOffer()
    {
        return $this->is_counter_offer;
    }

    public function isOriginalOffer()
    {
        return !$this->is_counter_offer;
    }

    // Business logic methods
    public function canBeAccepted()
    {
        return $this->status === 'pending';
    }

    public function canBeDeclined()
    {
        return $this->status === 'pending';
    }

    public function canBeCountered()
    {
        return $this->status === 'pending';
    }

    public function getOriginalOffer()
    {
        // If this is a counter offer and has a self-reference, return null
        // Otherwise, return the original offer
        if ($this->is_counter_offer && $this->original_offer_id == $this->id) {
            return null; // This is a self-referenced counter offer
        }
        return $this->is_counter_offer ? $this->originalOffer : $this;
    }

    public function getLatestCounterOffer()
    {
        return $this->counterOffers()->latest()->first();
    }

    public function getOfferHistory()
    {
        $history = collect();
        
        // If this is a counter offer, add the original offer info from notes
        if ($this->is_counter_offer && $this->original_offer_id == $this->id) {
            // Extract original offer info from buyer_notes
            $originalInfo = $this->extractOriginalOfferInfo();
            if ($originalInfo) {
                $history->push($originalInfo);
            }
        }
        
        $history->push($this);
        $history = $history->merge($this->counterOffers()->orderBy('created_at')->get());
        
        return $history;
    }

    public function extractOriginalOfferInfo()
    {
        if (!$this->buyer_notes || !str_contains($this->buyer_notes, 'Original offer:')) {
            return null;
        }

        // Extract original price from notes - handles both "$10,000.00" and "USD 10,000.00" formats
        preg_match('/Original offer: (?:\$|[A-Z]{3})?\s?([\d,]+\.?\d*)/', $this->buyer_notes, $matches);
        if (isset($matches[1])) {
            $originalPrice = str_replace(',', '', $matches[1]);
            return (object) [
                'offer_price' => $originalPrice,
                'created_at' => $this->created_at,
                'is_original' => true,
                'status' => 'declined'
            ];
        }

        return null;
    }

    public function getPriceDifference()
    {
        if (!$this->is_counter_offer) {
            return 0;
        }
        
        $originalInfo = $this->extractOriginalOfferInfo();
        if ($originalInfo) {
            return $this->offer_price - $originalInfo->offer_price;
        }
        
        return 0;
    }

    public function getPriceDifferencePercentage()
    {
        if (!$this->is_counter_offer) {
            return 0;
        }
        
        $originalInfo = $this->extractOriginalOfferInfo();
        if ($originalInfo && $originalInfo->offer_price > 0) {
            return (($this->offer_price - $originalInfo->offer_price) / $originalInfo->offer_price) * 100;
        }
        
        return 0;
    }

    // Get the latest active offer for a buyer-product combination
    public static function getLatestActiveOffer($productId, $buyerId)
    {
        return static::where('product_id', $productId)
            ->where('buyer_id', $buyerId)
            ->where('status', 'pending')
            ->latest()
            ->first();
    }

    // Check if there's already a pending offer for this buyer-product combination
    public static function hasPendingOffer($productId, $buyerId)
    {
        return static::where('product_id', $productId)
            ->where('buyer_id', $buyerId)
            ->where('status', 'pending')
            ->exists();
    }

    // Auto-expire offers after certain time (optional feature)
    public function shouldExpire()
    {
        // Auto-expire offers after 7 days
        return $this->isPending() && $this->created_at->diffInDays(now()) >= 7;
    }

    public function markAsExpired()
    {
        if ($this->isPending()) {
            $this->update(['status' => 'expired']);
        }
    }
}
