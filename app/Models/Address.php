<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',          // 'shipping' | 'billing'
        'label',         // Optional display label (e.g., 'Default')
        'full_name',
        'email',
        'phone',
        'country_id',
        'country',       // cached country name for simple rendering
        'address',       // optional combined address
        'address_1',
        'address_2',
        'city',
        'state',
        'zip',
        'is_default',
    ];
}

