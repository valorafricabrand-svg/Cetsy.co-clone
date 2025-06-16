<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingPeriod extends Model
{
    protected $guarded = ['id'];
    protected $table = 'shipping_periods';
}
