<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\ReleaseOnHoldFunds::class,
        // Legacy: ProcessApprovedPayouts removed from schedule and command list
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Auto-release on-hold funds for shipped orders after grace period
        $schedule->command('orders:release-onhold')->hourly();

        // Update FX rates daily at 01:00
        $schedule->command('rates:update')->dailyAt('01:00');

        // Removed legacy process:approved-payouts from schedule
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
