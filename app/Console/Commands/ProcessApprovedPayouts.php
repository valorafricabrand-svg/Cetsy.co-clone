<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Output\OutputInterface;

class ProcessApprovedPayouts extends Command
{
    protected $signature = "process:approved-payouts";
    protected $description = "Legacy payout processor (deprecated).";

    public function handle(): int
    {
        $this->warn('process:approved-payouts is deprecated and no longer processes payouts.');
        $this->warn('Use the admin payout tools or new automation instead.');

        if ($this->getOutput()->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            $this->line('Command kept for backward compatibility only.');
        }

        return Command::SUCCESS;
    }
}
