<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductView extends Model
{
    /** @var string */
    protected $table = 'product_views';

    /** @var array<int, string> */
    protected $fillable = [
        'product_id',
        'viewer_id',
        'ip',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'product_id' => 'integer',   // use 'string' if you store UUIDs
        'viewer_id'  => 'integer',   // same: switch to 'string' for UUIDs
    ];

    /* --------------------------------------------------------------------
     | Relationships
     *------------------------------------------------------------------- */

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function viewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viewer_id');
    }
}
