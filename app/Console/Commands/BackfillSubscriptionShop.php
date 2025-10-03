<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Subscription;
use App\Models\User;
use App\Models\Shop;

class BackfillSubscriptionShop extends Command
{
    protected $signature = 'subscriptions:backfill-shop {--sync-shops : Also sync shops.is_active based on active subscriptions}';

    protected $description = 'Backfill subscriptions.shop_id from user->shop and optionally sync shops.is_active';

    public function handle(): int
    {
        $fixed = 0; $skipped = 0;
        $subs = Subscription::whereNull('shop_id')->get();
        foreach ($subs as $sub) {
            $user = User::find($sub->user_id);
            $shop = $user?->shop;
            if ($shop) {
                $sub->shop_id = $shop->id;
                $sub->save();
                $fixed++;
            } else {
                $skipped++;
            }
        }
        $this->info("Subscriptions updated with shop_id: {$fixed}. Skipped (no shop): {$skipped}.");

        if ($this->option('sync-shops')) {
            $grace = function_exists('subscription_grace_days') ? (int) subscription_grace_days() : 5;
            $activeShopIds = Subscription::where('status', 'active')
                ->where('end_date', '>', now()->subDays($grace))
                ->pluck('shop_id')
                ->filter()
                ->unique()
                ->values();

            // Set active for shops in the list
            $activated = Shop::whereIn('id', $activeShopIds)->update(['is_active' => true]);
            // Deactivate shops not in the list (that currently are active)
            $deactivated = Shop::whereNotIn('id', $activeShopIds)->where('is_active', true)->update(['is_active' => false]);
            $this->info("Shops synced. Activated: {$activated}, Deactivated: {$deactivated}.");
        }

        return self::SUCCESS;
    }
}

