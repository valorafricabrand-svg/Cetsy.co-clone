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
        api: __DIR__.'/../routes/api.php', // ✅ Added API route registration
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'seller' => EnsureUserIsSeller::class,
            'ensure.seller.kyc' => EnsureSellerKycIsVerified::class,
            'ensure.seller.subscription' => \App\Http\Middleware\EnsureSellerHasActiveSubscription::class,
            'kyc.after.two.sales' => \App\Http\Middleware\RequireKycAfterTwoSales::class,
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
        ]);
    })
    ->withCommands([
        \App\Console\Commands\DeactivateExpiredSubscriptions::class,
    ])
    ->withSchedule(function (Schedule $schedule) {
        // Define your scheduled tasks here
        $schedule->command('products:pause-expired')->everyMinute();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
