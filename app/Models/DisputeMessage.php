<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DisputeMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'dispute_id', 'user_id', 'message', 'attachments', 
        'type', 'is_internal'
    ];

    protected $casts = [
        'attachments' => 'array',
        'is_internal' => 'boolean',
    ];

    // Type constants
    const TYPE_BUYER_MESSAGE = 'buyer_message';
    const TYPE_SELLER_MESSAGE = 'seller_message';
    const TYPE_ADMIN_MESSAGE = 'admin_message';
    const TYPE_SYSTEM_MESSAGE = 'system_message';

    // Relationships
    public function dispute(): BelongsTo
    {
        return $this->belongsTo(Dispute::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withDefault(null);
    }

    // Scopes
    public function scopePublic($query)
    {
        return $query->where('is_internal', false);
    }

    public function scopeInternal($query)
    {
        return $query->where('is_internal', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    // Methods
    public function isBuyerMessage(): bool
    {
        return $this->type === self::TYPE_BUYER_MESSAGE;
    }

    public function isSellerMessage(): bool
    {
        return $this->type === self::TYPE_SELLER_MESSAGE;
    }

    public function isAdminMessage(): bool
    {
        return $this->type === self::TYPE_ADMIN_MESSAGE;
    }

    public function isSystemMessage(): bool
    {
        return $this->type === self::TYPE_SYSTEM_MESSAGE;
    }

    public function isInternal(): bool
    {
        return $this->is_internal;
    }

    public function getTypeLabel(): string
    {
        return match($this->type) {
            self::TYPE_BUYER_MESSAGE => 'Buyer Message',
            self::TYPE_SELLER_MESSAGE => 'Seller Message',
            self::TYPE_ADMIN_MESSAGE => 'Admin Message',
            self::TYPE_SYSTEM_MESSAGE => 'System Message',
            default => 'Unknown'
        };
    }

    public function getMessageTypeClass(): string
    {
        return match($this->type) {
            self::TYPE_BUYER_MESSAGE => 'buyer-message',
            self::TYPE_SELLER_MESSAGE => 'seller-message',
            self::TYPE_ADMIN_MESSAGE => 'admin-message',
            self::TYPE_SYSTEM_MESSAGE => 'system-message',
            default => 'default-message'
        };
    }

    public function hasAttachments(): bool
    {
        return !empty($this->attachments);
    }

    public function getAttachmentsCount(): int
    {
        return is_array($this->attachments) ? count($this->attachments) : 0;
    }
}
