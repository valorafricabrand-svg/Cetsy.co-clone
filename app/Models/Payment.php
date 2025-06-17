<?php

namespace App\Models;

use App\Models\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory;

 
    protected $guarded = ['id'];
    protected $table = 'payments';


      /** Map DB integer → label / badge */
    public const STATUSES = [
        0 => ['label' => 'pending',   'badge' => 'secondary'],
        1 => ['label' => 'failed',    'badge' => 'danger'],
        2 => ['label' => 'canceled',  'badge' => 'warning'],
        3 => ['label' => 'successful','badge' => 'success'],
    ];

    /** Readable label */
    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->paymentStatus]['label'] ?? 'unknown';
    }

    /** Bootstrap badge class */
    public function getStatusBadgeClassAttribute(): string
    {
        return 'bg-' . (self::STATUSES[$this->paymentStatus]['badge'] ?? 'dark');
    }


    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function orders() : HasMany
    {
        return $this->hasMany(Order::class,);
    }

    public function shop()
{
    return $this->belongsTo(Shop::class);
}

  
}
