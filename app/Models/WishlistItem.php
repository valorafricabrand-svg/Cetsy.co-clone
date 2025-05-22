<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WishlistItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
    ];

    /**
     * The user who added this to their wishlist.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The product that was wish-listed.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
