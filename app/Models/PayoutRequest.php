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
        'approved_by','approved_at','paid_by','rejected_by','rejected_at',
    ];

    protected $casts = [
        'meta'    => 'array',
        'paid_at' => 'datetime',
        'amount'  => 'float',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function wallet() { return $this->belongsTo(Wallet::class); }
    public function user()   { return $this->belongsTo(User::class); }
    public function paymentMethod() { return $this->belongsTo(PaymentMethod::class); }
}
