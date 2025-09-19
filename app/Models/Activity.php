<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $fillable = [
        'user_id',
        'is_read',
        'description',
        'type',
        'related_id',
        'related_type',
        'title',
        'message',
        'link',
        'properties',
        'causer_id',
        'causer_type',
        'subject_id',
        'subject_type',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'properties' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function causer()
    {
        return $this->morphTo();
    }

    public function subject()
    {
        return $this->morphTo();
    }

    public function related()
    {
        return $this->morphTo(__FUNCTION__, 'related_type', 'related_id');
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
    const TYPE_DISPUTE = 'dispute';
}
