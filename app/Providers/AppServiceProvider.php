<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;       // ← Correct import for Gate facade
use App\Models\User;                        // ← Ensure User model is imported

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
        $this->registerPolicies();

        Gate::define('isAdmin', fn(User $user) => $user->isAdmin());
        Gate::define('isSeller', fn(User $user) => $user->isSeller());
        Gate::define('isBuyer', fn(User $user) => $user->isBuyer());
    }
}
