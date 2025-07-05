<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

use App\Models\Shop;
use App\Models\Country;
use App\Models\Order;
use App\Models\WishlistItem;
use App\Models\Kyc;
use App\Models\Subscription;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // User role constants
    public const TYPE_BUYER  = 'buyer';
    public const TYPE_SELLER = 'seller';
    public const TYPE_ADMIN  = 'admin';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'user_type',
        'is_active',
        'country_id',
        'phone',
        'photo',
        'photo_storage',
    ];

    /**
     * The attributes that should be hidden for arrays and JSON.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
    ];

    /**
     * One user has one shop.
     */
    public function shop()
    {
        return $this->hasOne(Shop::class);
    }

    /**
     * One user belongs to one country.
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * One user has many orders.
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * One user has many wishlist items.
     */
    public function wishlistItems()
    {
        return $this->hasMany(WishlistItem::class);
    }

    /**
     * One user has one KYC record.
     */
    public function kyc()
    {
        return $this->hasOne(Kyc::class);
    }

    /**
     * One user has one latest subscription.
     */
    public function subscription()
    {
        return $this->hasOne(Subscription::class)->latest();
    }

    /**
     * Check if user has active subscription.
     */
    public function hasActiveSubscription(): bool
    {
        return $this->subscription && $this->subscription->isActive();
    }

    /**
     * Helper: Check if the user is a buyer.
     */
    public function isBuyer(): bool
    {
        return $this->user_type === self::TYPE_BUYER;
    }

    /**
     * Helper: Check if the user is a seller.
     */
    public function isSeller(): bool
    {
        return $this->user_type === self::TYPE_SELLER;
    }

    /**
     * Helper: Check if the user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->user_type === self::TYPE_ADMIN;
    }

    /**
     * Gravatar or uploaded profile photo accessor.
     */
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
