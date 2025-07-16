<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
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
    protected $description = 'Pause all products whose next_due_date has passed';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();

        $count = Product::where('is_active', 1)
            ->where('next_due_date', '<=', $now)
            ->update(['is_active' => 2]);

        $this->info("Paused {$count} expired listing(s).");

        return 0;
    }
}
