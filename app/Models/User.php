<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Shop;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // Allowed types
    public const TYPE_BUYER  = 'buyer';
    public const TYPE_SELLER = 'seller';
    public const TYPE_ADMIN  = 'admin';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'user_type',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array<int,string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
    ];

    /**
     * One user → one shop.
     */
    public function shop()
    {
        return $this->hasOne(Shop::class);
    }

    /**
     * Helper: is this user a Buyer?
     */
    public function isBuyer(): bool
    {
        return $this->user_type === self::TYPE_BUYER;
    }

    /**
     * Helper: is this user a Seller?
     */
    public function isSeller(): bool
    {
        return $this->user_type === self::TYPE_SELLER;
    }

    /**
     * Helper: is this user an Admin?
     */
    public function isAdmin(): bool
    {
        return $this->user_type === self::TYPE_ADMIN;
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function wishlistItems()
    {
        return $this->hasMany(WishlistItem::class);
    }

    public function kyc()
    {
        return $this->hasOne(Kyc::class);
    }

    public function subscription()
    {
        return $this->hasOne(Subscription::class)->latest();
    }

    public function hasActiveSubscription(): bool
    {
        return $this->subscription && $this->subscription->isActive();
    }


        public function get_gravatar($s = 40, $d = 'mm', $r = 'g', $img = false, $atts = [])
    {
        $email = $this->email;
        $url = 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($email))) . "?s=$s&d=$d&r=$r";

        if (!empty($this->photo)) {
            $url = avatar_img_url($this->photo, $this->photo_storage);
        }

        if ($img) {
            $attributes = '';
            foreach ($atts as $key => $val) {
                $attributes .= " $key=\"$val\"";
            }
            return '<img src="' . $url . '"' . $attributes . ' />';
        }
        return $url;
    }

}
