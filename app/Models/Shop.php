<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'bio',
        'logo',
    ];

    /**
     * Override the route key for implicit model binding to use slug.
     *
     * @return string
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * A shop belongs to a user.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
