<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPlatformStat extends Model
{
    use HasFactory;

    public const PLATFORM_WEB = 'web';
    public const PLATFORM_APP = 'app';

    protected $fillable = [
        'user_id',
        'last_platform',
        'web_hits',
        'app_hits',
        'last_seen_at',
        'last_ip',
        'last_user_agent',
    ];

    protected $casts = [
        'web_hits' => 'integer',
        'app_hits' => 'integer',
        'last_seen_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

