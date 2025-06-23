<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = ['payment_type_id', 'account_number', 'account_name', 'shop_id'];

    public function paymentType()
    {
        return $this->belongsTo(PaymentType::class);
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }
}
