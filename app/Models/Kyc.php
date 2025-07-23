<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kyc extends Model
{
    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'id_number',
        'id_type',
        'id_front',
        'id_back',
        'selfie',
        'status',
        'admin_notes',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }


      public function scopeStatus($query, $status)
    {
        return $status ? $query->where('status', $status) : $query;
    }


        public function scopeSearch($q, $term)
    {
        if (!$term) return $q;

        return $q->where(function ($sub) use ($term) {
            $sub->where('id_number', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('phone', 'like', "%{$term}%")
                ->orWhere('first_name', 'like', "%{$term}%")
                ->orWhere('last_name', 'like', "%{$term}%")
                ->orWhere('id', $term)
                ->orWhereHas('user', function ($u) use ($term) {
                    $u->where('name', 'like', "%{$term}%")
                      ->orWhere('email', 'like', "%{$term}%");
                });
        });
    }
}
