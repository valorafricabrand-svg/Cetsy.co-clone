<?php

namespace App\Models;

use App\Models\Product\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $table = 'orders';


        /**
     * Order status constants
     */
    public const STATUS_PENDING    = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_SHIPPED    = 'shipped';
    public const STATUS_DELIVERED  = 'delivered';
    public const STATUS_CANCELLED  = 'cancelled';
    public const STATUS_REFUNDED   = 'refunded';
    public const STATUS_RETURNED   = 'returned';
     public const STATUS_COMPLETED   = 'completed';

    /**
     * Return all possible statuses
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_PROCESSING,
            self::STATUS_SHIPPED,
            self::STATUS_DELIVERED,
            self::STATUS_CANCELLED,
            self::STATUS_REFUNDED,
            self::STATUS_RETURNED,
        ];
    }

       public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING    => 'bg-secondary',
            self::STATUS_PROCESSING => 'bg-info',
            self::STATUS_SHIPPED    => 'bg-primary',
            self::STATUS_DELIVERED  => 'bg-success',
            self::STATUS_CANCELLED  => 'bg-danger',
            self::STATUS_REFUNDED   => 'bg-warning',
            self::STATUS_RETURNED   => 'bg-dark',
            default                 => 'bg-light text-dark',
        };
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }


        public function items()
    {
        return $this->hasMany(OrderItem::class);
    }


        public function user()
{
    return $this->belongsTo(User::class);
}


public function orderItems()
{
    return $this->hasMany(OrderItem::class);
}

    public function payments() {
        return $this->hasMany(Payment::class);
    }

    /**
     * Determine if the order already has a successful payment.
     */
    public function isPaid(): bool
    {
        if ($this->status !== self::STATUS_PENDING) {
            return true;
        }

        return $this->payments()
            ->where('paymentStatus', 3)
            ->exists();
    }


  public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function offer()
    {
        return $this->hasOne(Offer::class);
    }


    public function messages()
{
    return $this->hasMany(OrderMessage::class)
                ->orderBy('created_at','asc');
}

public function reviews()
{
    return $this->hasMany(Review::class);
}

    
}
