<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BulkPriceLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'user_id',
        'direction',
        'percent',
        'column',
        'round_to',
        'apply_all',
        'selection_count',
        'affected_products',
        'affected_variants',
    ];
}

