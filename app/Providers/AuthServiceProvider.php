<?php

namespace App\Providers;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\Cart;
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
            $sessionCart = session('cart', []);
            $sessionCount = 0;
            if (is_array($sessionCart)) {
                foreach ($sessionCart as $row) {
                    if (is_array($row)) {
                        $sessionCount += (int) ($row['quantity'] ?? 0);
                    }
                }
            }
            if (Auth::check()) {
                $cartCount = $sessionCount;
                if ($cartCount === 0) {
                    $cart = Cart::where('user_id', Auth::id())->first();
                    if ($cart) {
                        $cartCount = (int) $cart->items()->sum('quantity');
                    }
                }
            } else {
                $cartCount = $sessionCount;
            }
            $view->with('cartCount', $cartCount);
        });
    }
}
