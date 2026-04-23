<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\EnsureSellerKycIsVerified;
use App\Http\Middleware\EnsureUserIsSeller;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\TrackUserPlatform;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Cache;

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

        $middleware->web(append: [
            SetLocale::class,
        ]);

        // Global middlewares you already had
        $middleware->append(\App\Http\Middleware\ApplyCurrency::class);
        $middleware->append(\App\Http\Middleware\AttachCurrencyToRequest::class);
        $middleware->append(TrackUserPlatform::class);
    })
    ->withCommands([
        \App\Console\Commands\DeactivateExpiredSubscriptions::class,
        \App\Console\Commands\NotifyShipBy::class,
        \App\Console\Commands\SendSubscriptionExpiryReminders::class,
        \App\Console\Commands\BackfillSubscriptionShop::class,
        \App\Console\Commands\AutoCancelPendingOrders::class,
        \App\Console\Commands\GenerateWebPushVapidKeys::class,
    ])
    ->withSchedule(function (Schedule $schedule) {
        // Shared-hosting friendly queue processing (works with cron + schedule:run).
        $schedule->command('queue:work --stop-when-empty --tries=1 --timeout=7200')
            ->everyMinute()
            ->withoutOverlapping(120);

        // Heartbeat used by admin status panel diagnostics.
        $schedule->call(function (): void {
            Cache::put('system:scheduler:heartbeat', now()->toIso8601String(), now()->addMinutes(10));
        })->everyMinute();

        // Define your scheduled tasks here
        $schedule->command('products:pause-expired')->everyMinute();
        // Ship-by reminders daily
        $schedule->command('orders:notify-shipby')->everyMinute();
        // Subscription expiry reminders daily
        $schedule->command('subscriptions:remind-expiring')->everyMinute();
        // Deactivate expired subscriptions and shops daily
        $schedule->command('subscriptions:deactivate-expired')->everyMinute();
        // Auto-cancel pending orders daily
        $schedule->command('orders:auto-cancel')->everyMinute();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

