<?php

// app/Models/PayoutRequest.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayoutRequest extends Model
{
    protected $fillable = ['wallet_id','amount','status','meta', 'user_id', 'payment_method_id'];
    protected $casts = ['meta' => 'array'];

    public function wallet() { return $this->belongsTo(Wallet::class); }
}
