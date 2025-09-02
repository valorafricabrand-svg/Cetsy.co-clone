<?php

// app/Models/PayoutRequest.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayoutRequest extends Model
{
    protected $fillable = [
        'wallet_id',
        'amount',
        'status',
        'meta',
        'user_id',
        'payment_method_id',
        'paid_at',
        'admin_reason',
    ];

    protected $casts = [
        'meta'    => 'array',
        'paid_at' => 'datetime',
        'amount'  => 'float',
    ];

    public function wallet() { return $this->belongsTo(Wallet::class); }
    public function user()   { return $this->belongsTo(User::class); }
    public function paymentMethod() { return $this->belongsTo(PaymentMethod::class); }
}
