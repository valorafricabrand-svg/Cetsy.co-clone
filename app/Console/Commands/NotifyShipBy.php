<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ShipByReminderNotification;

class NotifyShipBy extends Command
{
    protected $signature = 'orders:notify-shipby';
    protected $description = 'Notify buyers and sellers when ship-by dates are today';

    public function handle(): int
    {
        $today = now()->startOfDay();

        $orders = Order::query()
            ->whereIn('status', [Order::STATUS_PENDING, Order::STATUS_PROCESSING])
            ->with(['items.shippingProfile.processingTime','shop.user','user'])
            ->latest('id')
            ->get();

        $count = 0;
        foreach ($orders as $order) {
            $minDays = null; $maxDays = null;
            foreach ($order->items as $it) {
                $sp = $it->shippingProfile;
                $pMin = $sp?->processing_custom_min ?? optional($sp?->processingTime)->start_day;
                $pMax = $sp?->processing_custom_max ?? optional($sp?->processingTime)->end_day;
                if (is_numeric($pMin)) $minDays = is_null($minDays) ? (int)$pMin : min($minDays, (int)$pMin);
                if (is_numeric($pMax)) $maxDays = is_null($maxDays) ? (int)$pMax : max($maxDays, (int)$pMax);
            }
            if (is_null($minDays) && is_null($maxDays)) continue;

            $placedAt = $order->created_at ?: now();
            $shipStart = $placedAt->copy()->addDays((int)($minDays ?? $maxDays));
            $shipEnd   = $placedAt->copy()->addDays((int)($maxDays ?? $minDays));

            $windowLabel = $shipStart->format('M j').($maxDays ? (' – '.$shipEnd->format('M j')) : '');

            // Notify when either edge equals today
            if ($shipStart->isSameDay($today) || $shipEnd->isSameDay($today)) {
                if ($order->user) {
                    Notification::send($order->user, new ShipByReminderNotification($order, 'buyer', $windowLabel));
                }
                if ($order->shop && $order->shop->user) {
                    Notification::send($order->shop->user, new ShipByReminderNotification($order, 'seller', $windowLabel));
                }
                $count++;
            }
        }

        $this->info("Ship-by notifications sent for {$count} order(s).");
        return Command::SUCCESS;
    }
}

