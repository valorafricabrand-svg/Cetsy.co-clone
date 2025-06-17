<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'credit',
        'debit',
        'balance',
        'type',
        'reference',
        'description',
    ];

    /**
     * Relationship: Wallet belongs to a User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Accessor for formatted balance
     */
    public function getFormattedBalanceAttribute()
    {
        return number_format($this->balance, 2);
    }


    public function payoutRequests()
{
    return $this->hasMany(PayoutRequest::class);
}

}
