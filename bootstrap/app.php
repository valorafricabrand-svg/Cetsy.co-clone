<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\EnsureSellerKycIsVerified;
use App\Http\Middleware\EnsureUserIsSeller;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php', // ✅ API routes stay registered
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // ✅ Completely disable CSRF protection (globally)
        // $middleware->disableCsrfProtection(); // removed: keep CSRF enabled

        // (Alternative approach instead of the line above)
        // $middleware->web(remove: [
        //     \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
        // ]);

        // Your aliases
        $middleware->alias([
            'seller' => EnsureUserIsSeller::class,
            'ensure.seller.kyc' => EnsureSellerKycIsVerified::class,
            'ensure.seller.subscription' => \App\Http\Middleware\EnsureSellerHasActiveSubscription::class,
            'kyc.after.two.sales' => \App\Http\Middleware\RequireKycAfterTwoSales::class,
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
        ]);

        // Global middlewares you already had
        $middleware->append(\App\Http\Middleware\ApplyCurrency::class);
        $middleware->append(\App\Http\Middleware\AttachCurrencyToRequest::class);
    })
    ->withCommands([
        \App\Console\Commands\DeactivateExpiredSubscriptions::class,
        \App\Console\Commands\NotifyShipBy::class,
        \App\Console\Commands\SendSubscriptionExpiryReminders::class,
        \App\Console\Commands\BackfillSubscriptionShop::class,
    ])
    ->withSchedule(function (Schedule $schedule) {
        // Define your scheduled tasks here
        $schedule->command('products:pause-expired')->everyMinute();
        // Ship-by reminders daily
        $schedule->command('orders:notify-shipby')->dailyAt('08:00');
        // Subscription expiry reminders daily
        $schedule->command('subscriptions:remind-expiring')->dailyAt('09:00');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();







