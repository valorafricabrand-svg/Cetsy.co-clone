<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\Models\Wallet;
use App\Models\Payment;

class ReleaseOnHoldFunds extends Command
{
    protected $signature = 'orders:release-onhold {--days= : Days after shipped to auto-release (defaults to settings/3)}';
    protected $description = 'Auto-release on-hold seller funds for shipped orders after a grace period and mark orders completed.';

    public function handle(): int
    {
        $defaultDays = function_exists('setting') ? (int) setting('auto_release_days', 3) : 3;
        $daysOption  = $this->option('days');
        $days        = is_numeric($daysOption) ? (int) $daysOption : $defaultDays;

        $cutoff = now()->subDays(max(1, $days));

        $orders = Order::where('status', Order::STATUS_SHIPPED)
            ->where('updated_at', '<=', $cutoff)
            ->with(['shop:id,user_id', 'payments:id,order_id,local_transaction_id'])
            ->get();

        if ($orders->isEmpty()) {
            $this->info('No shipped orders eligible for auto-release.');
            return 0;
        }

        $released = 0;

        foreach ($orders as $order) {
            DB::transaction(function () use ($order, &$released) {
                // Mark order completed and delivered_at if not set
                $order->update([
                    'status'       => Order::STATUS_COMPLETED,
                    'delivered_at' => $order->delivered_at ?: now(),
                ]);

                $sellerId = optional($order->shop)->user_id;
                if (!$sellerId) {
                    return; // skip if shop/user missing
                }

                // Try to match on-hold wallet rows by meta->order_id or fallback by payment reference
                $paymentRef = optional($order->payments->first())->local_transaction_id;

                $affected = Wallet::where('user_id', $sellerId)
                    ->where('status', 'on_hold')
                    ->where(function ($q) use ($order, $paymentRef) {
                        $q->where('meta->order_id', $order->id);
                        if ($paymentRef) {
                            $q->orWhere('reference', $paymentRef);
                        }
                    })
                    ->update(['status' => 'completed']);

                if ($affected > 0) {
                    $released += $affected;
                }
            });
        }

        $this->info("Processed {$orders->count()} orders; released {$released} wallet rows.");
        Log::info('orders:release-onhold summary', ['orders' => $orders->count(), 'released_rows' => $released, 'days' => $days]);
        return 0;
    }
}

