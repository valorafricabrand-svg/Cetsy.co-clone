<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Activity;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Wallet;
use Carbon\Carbon;

class PauseExpiredListings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:pause-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto-renew eligible expired listings from wallet, otherwise pause expired active listings';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();
        $stats = [
            'processed' => 0,
            'renewed' => 0,
            'paused' => 0,
            'manual' => 0,
            'insufficient_funds' => 0,
            'missing_shop' => 0,
        ];

        Product::query()
            ->whereIn('is_active', [1, 2])
            ->whereNotNull('next_due_date')
            ->where('next_due_date', '<=', $now)
            ->with([
                'category:id,listing_fee,listing_frequency',
                'shop:id,user_id',
            ])
            ->orderBy('id')
            ->chunkById(200, function ($products) use ($now, &$stats) {
                foreach ($products as $product) {
                    $stats['processed']++;

                    if ((string) $product->renewal_type !== 'automatic') {
                        $stats['manual']++;
                        $this->pauseIfActive($product, $stats);
                        continue;
                    }

                    $shopUserId = (int) ($product->shop->user_id ?? 0);
                    if ($shopUserId <= 0) {
                        $stats['missing_shop']++;
                        $this->pauseIfActive($product, $stats);
                        continue;
                    }

                    if ($this->renewFromWallet($product, $shopUserId, $now)) {
                        $stats['renewed']++;
                        continue;
                    }

                    $stats['insufficient_funds']++;
                    $this->pauseIfActive($product, $stats);
                }
            });

        $this->info(
            "Processed {$stats['processed']} expired listing(s). "
            ."Auto-renewed {$stats['renewed']}, paused {$stats['paused']}, "
            ."manual {$stats['manual']}, insufficient funds {$stats['insufficient_funds']}, "
            ."missing shop/user {$stats['missing_shop']}."
        );

        return 0;
    }

    private function pauseIfActive(Product $product, array &$stats): void
    {
        if ((int) $product->is_active !== 1) {
            return;
        }

        $product->forceFill(['is_active' => 2])->save();
        $stats['paused']++;
    }

    private function renewFromWallet(Product $product, int $sellerUserId, Carbon $now): bool
    {
        $fee = max(0, (float) ($product->category->listing_fee ?? 0));
        $freq = (int) ($product->category->listing_frequency ?? 4);
        $freq = in_array($freq, [1, 4], true) ? $freq : 4;
        $plan = $freq === 1 ? 'monthly' : '4months';

        return (bool) DB::transaction(function () use ($product, $sellerUserId, $fee, $freq, $plan, $now) {
            $balance = (float) (Wallet::where('user_id', $sellerUserId)
                ->where('status', 'completed')
                ->selectRaw('COALESCE(SUM(credit - debit), 0) as balance')
                ->lockForUpdate()
                ->value('balance') ?? 0.0);

            if (($balance + 0.00001) < $fee) {
                return false;
            }

            if ($fee > 0) {
                Wallet::create([
                    'user_id' => $sellerUserId,
                    'credit' => 0,
                    'debit' => $fee,
                    'balance' => $balance - $fee,
                    'reference' => strtoupper(uniqid('TXN-')),
                    'method' => 'wallet',
                    'status' => 'completed',
                    'description' => "Listing auto-renewal fee ({$plan})",
                    'meta' => [
                        'product_id' => $product->id,
                        'purpose' => 'listing_auto_renewal',
                        'renewed_at' => $now->toIso8601String(),
                    ],
                ]);
            }

            $baseDue = $product->next_due_date ? Carbon::parse($product->next_due_date) : $now->copy();
            if ($baseDue->lt($now)) {
                $baseDue = $now->copy();
            }
            $nextDue = $baseDue->addMonths($freq);

            $product->forceFill([
                'is_active' => 1,
                'listing_paid_at' => $now,
                'next_due_date' => $nextDue,
            ])->save();

            Payment::create([
                'user_id' => (string) $sellerUserId,
                'shop_id' => $product->shop_id,
                'total_amount' => $fee,
                'payment_method' => 'wallet',
                'paymentStatus' => 3,
                'payment_status' => 'successful',
                'currency' => $product->currency ?? 'USD',
                'local_transaction_id' => $this->nextLocalTransactionId(),
                'payment_name' => 'listing_fee',
                'more_details' => json_encode([
                    'product_id' => $product->id,
                    'source' => 'auto_renew',
                    'plan' => $plan,
                    'frequency_months' => $freq,
                ]),
            ]);

            try {
                Activity::create([
                    'user_id' => $sellerUserId,
                    'is_read' => false,
                    'type' => Activity::TYPE_PRODUCT,
                    'related_id' => $product->id,
                    'related_type' => 'product',
                    'description' => 'Listing auto-renewed via wallet for $' . number_format($fee, 2),
                    'properties' => [
                        'product_id' => $product->id,
                        'amount' => $fee,
                        'plan' => $plan,
                        'next_due_date' => $nextDue->toDateTimeString(),
                    ],
                ]);
            } catch (\Throwable $e) {
                // Notifications should not block renewal.
            }

            return true;
        }, 3);
    }

    private function nextLocalTransactionId(): string
    {
        do {
            $id = 'TRAN_' . time() . strtoupper(bin2hex(random_bytes(3)));
        } while (Payment::where('local_transaction_id', $id)->exists());

        return $id;
    }
}
