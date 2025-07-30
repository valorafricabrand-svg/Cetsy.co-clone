<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
      View::composer('*', function ($view) {
        $cartCount = 0;

        if (Auth::check()) {
            $cart = Cart::firstOrCreate(['user_id' => Auth::id()]);
            $cartCount = CartItem::where('cart_id', $cart->id)->sum('quantity');
        } else {
            $sessionCart = session('cart', []);
            $cartCount = array_sum($sessionCart);
        }

        $view->with('cartCount', $cartCount);
    });
    }
}
