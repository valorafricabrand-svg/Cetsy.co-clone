<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $fillable = ['user_id', 'is_read', 'description', 'type', 'related_id', 'related_type'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Constants for notification types
    const TYPE_MESSAGE = 'message';
    const TYPE_OFFER = 'offer';
    const TYPE_ORDER = 'order';
    const TYPE_KYC = 'kyc';
    const TYPE_WALLET = 'wallet';
    const TYPE_SUBSCRIPTION = 'subscription';
    const TYPE_PAYOUT = 'payout';
    const TYPE_PRODUCT = 'product';
    const TYPE_GENERAL = 'general';
}
