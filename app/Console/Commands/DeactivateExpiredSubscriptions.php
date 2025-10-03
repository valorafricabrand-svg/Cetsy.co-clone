<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Subscription;

class DeactivateExpiredSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:deactivate-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deactivate all subscriptions that have expired.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $grace = function_exists('subscription_grace_days') ? (int) subscription_grace_days() : (int) setting('subscription_grace_days', 5);
        $expired = Subscription::where('status', 'active')
            ->where('end_date', '<', now()->subDays($grace))
            ->update(['status' => 'inactive']);

        // Deactivate shops that no longer have an active subscription
        $shopsToDeactivate = \App\Models\Shop::whereNotExists(function($q) use ($grace){
                $q->selectRaw(1)
                  ->from('subscriptions')
                  ->whereColumn('subscriptions.shop_id', 'shops.id')
                  ->where('subscriptions.status', 'active')
                  ->where('subscriptions.end_date', '>', now()->subDays($grace));
            })
            ->where('is_active', true)
            ->update(['is_active' => false]);

        $this->info("Deactivated $expired expired subscriptions. Shops deactivated: $shopsToDeactivate");
    }
}
