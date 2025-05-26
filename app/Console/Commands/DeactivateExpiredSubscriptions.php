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
        $expired = Subscription::where('status', 'active')
            ->where('end_date', '<', now())
            ->update(['status' => 'inactive']);

        $this->info("Deactivated $expired expired subscriptions.");
    }
}
