<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = [
        'payment_type_id',
        'account_number',
        'account_name',
        'shop_id',
        'bank_name',
        'bank_country',
        'bank_currency',
        'bank_routing_number',
        'swift_bic',
        'iban',
        'bank_address',
        'wise_email',
        'wise_recipient_id',
        'wise_profile_id',
    ];

    public function paymentType()
    {
        return $this->belongsTo(PaymentType::class);
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }
}
