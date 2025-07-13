<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Offer;
use Carbon\Carbon;

class ExpireOldOffers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'offers:expire {--days=7 : Number of days after which offers should expire}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire old pending offers that have not been responded to';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = $this->option('days');
        $cutoffDate = Carbon::now()->subDays($days);

        $offers = Offer::where('status', 'pending')
            ->where('created_at', '<', $cutoffDate)
            ->get();

        $count = 0;
        foreach ($offers as $offer) {
            if ($offer->shouldExpire()) {
                $offer->markAsExpired();
                $count++;
            }
        }

        $this->info("Expired {$count} offers that were older than {$days} days.");
        
        if ($count > 0) {
            $this->info("Offers have been automatically expired to keep the marketplace active.");
        }

        return 0;
    }
} 