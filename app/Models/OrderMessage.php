<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderMessage extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'attachments' => 'array',
    ];

    // Message type constants (for order messages)
    const TYPE_BUYER_MESSAGE = 'buyer_message';
    const TYPE_SELLER_MESSAGE = 'seller_message';
    const TYPE_SYSTEM_MESSAGE = 'system_message';

    public function order() {
        return $this->belongsTo(Order::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    // Message type methods
    public function isBuyerMessage(): bool
    {
        return $this->type === self::TYPE_BUYER_MESSAGE;
    }

    public function isSellerMessage(): bool
    {
        return $this->type === self::TYPE_SELLER_MESSAGE;
    }

    public function isSystemMessage(): bool
    {
        return $this->type === self::TYPE_SYSTEM_MESSAGE;
    }

    public function getTypeLabel(): string
    {
        return match($this->type) {
            self::TYPE_BUYER_MESSAGE => 'Buyer Message',
            self::TYPE_SELLER_MESSAGE => 'Seller Message',
            self::TYPE_SYSTEM_MESSAGE => 'System Message',
            default => 'Order Message'
        };
    }

    public function getMessageTypeClass(): string
    {
        return match($this->type) {
            self::TYPE_BUYER_MESSAGE => 'buyer-message',
            self::TYPE_SELLER_MESSAGE => 'seller-message',
            self::TYPE_SYSTEM_MESSAGE => 'system-message',
            default => 'order-message'
        };
    }

    // Attachment methods
    public function hasAttachments(): bool
    {
        return !empty($this->attachments);
    }

    public function getAttachmentsCount(): int
    {
        return is_array($this->attachments) ? count($this->attachments) : 0;
    }

    // Get message content (alias for body)
    public function getMessageAttribute()
    {
        return $this->body;
    }

    // Default type if none is set
    public function getTypeAttribute($value)
    {
        if (empty($value)) {
            // Determine type based on user relationship if possible
            if ($this->user_id && $this->order) {
                if ($this->user_id === $this->order->user_id) {
                    return self::TYPE_BUYER_MESSAGE;
                } elseif ($this->order->shop && $this->user_id === $this->order->shop->user_id) {
                    return self::TYPE_SELLER_MESSAGE;
                }
            }
            return self::TYPE_SYSTEM_MESSAGE;
        }
        return $value;
    }
}
