<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    use HasFactory;

    /**
     * Allow both legacy `type` and new fields (`method`, `external_id`, `meta`).
     */
    protected $fillable = [
        'user_id',
        'credit',
        'debit',
        'balance',
        'type',         // legacy
        'method',       // preferred going forward
        'reference',
        'description',
        'external_id',  // e.g. CheckoutRequestID, PayPal order id
        'meta',         // JSON bag (rate, raw payloads, etc.)
    ];

    /**
     * Sensible casting for money/JSON.
     */
    protected $casts = [
        'credit'  => 'float',
        'debit'   => 'float',
        'balance' => 'float',
        'meta'    => 'array',
    ];

    /**
     * Default attributes.
     */
    protected $attributes = [
        'method' => 'wallet',
    ];

    /**
     * Relations
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payoutRequests()
    {
        return $this->hasMany(PayoutRequest::class);
    }

    /**
     * Accessors
     */
    public function getFormattedBalanceAttribute(): string
    {
        return number_format((float) $this->balance, 2);
    }

    /**
     * Backward compatibility:
     * - If `method` is null but legacy `type` exists, use `type`.
     */
    public function getMethodAttribute($value)
    {
        if ($value !== null && $value !== '') {
            return $value;
        }
        // Fall back to legacy `type` column if present
        return $this->getAttributeFromArray('type') ?? null;
    }

    /**
     * When setting `method`, also mirror to legacy `type` (if you still use it in views/queries).
     */
    public function setMethodAttribute($value): void
    {
        $this->attributes['method'] = $value;
        // keep legacy column in sync if it exists on the table
        if (array_key_exists('type', $this->attributes) || $this->isFillable('type')) {
            $this->attributes['type'] = $value;
        }
    }

    /**
     * Scopes (handy for queries)
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeCredits($query)
    {
        return $query->where('credit', '>', 0);
    }

    public function scopeDebits($query)
    {
        return $query->where('debit', '>', 0);
    }

    public function scopeWithExternalId($query, ?string $externalId)
    {
        return $externalId ? $query->where('external_id', $externalId) : $query;
    }
}
