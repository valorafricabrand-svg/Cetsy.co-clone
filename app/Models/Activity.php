<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'description',
        'causer_id',
        'is_read',
        'link',
        'created_at',
        'related_id',
        'related_type',
        'user_id',
    ];

    // Constants for notification types
    public const TYPE_MESSAGE      = 'message';
    public const TYPE_OFFER        = 'offer';
    public const TYPE_ORDER        = 'order';
    public const TYPE_KYC          = 'kyc';
    public const TYPE_WALLET       = 'wallet';
    public const TYPE_SUBSCRIPTION = 'subscription';
    public const TYPE_PAYOUT       = 'payout';
    public const TYPE_PRODUCT      = 'product';
    public const TYPE_GENERAL      = 'general';

    // 👇 Add this for shop activities
    public const TYPE_SHOP         = 'shop';

    public function causer()
    {
        return $this->belongsTo(User::class, 'causer_id');
    }
}
