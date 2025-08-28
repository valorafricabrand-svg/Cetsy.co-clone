<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'description', // or 'message', depending on your column name
        'causer_id',
        'is_read',
        'link',
        'created_at',
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

    public function causer()
    {
        return $this->belongsTo(User::class, 'causer_id');
    }
}