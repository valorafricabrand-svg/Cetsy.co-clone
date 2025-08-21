<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    protected $fillable = [
        'user_id',
        'channel',
        'external_id',
        'amount_usd',
        'amount_kes',
        'status',
        
        'meta',
    ];

    protected $casts = [
        'amount_usd' => 'float',
        'amount_kes' => 'integer',
        'meta'       => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
