<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProcessingTime;

class ProcessingTimeSeeder extends Seeder
{
    public function run()
    {
        // Define your processing‐time options
        $options = [
            ['name' => 'Fast (1‑2 days)',     'days' => 2],
            ['name' => 'Standard (3‑5 days)', 'days' => 5],
            ['name' => 'Slow (7‑10 days)',     'days' => 10],
        ];

        foreach ($options as $opt) {
            ProcessingTime::updateOrCreate(
                ['name' => $opt['name']],       // match on name
                ['days' => $opt['days']]        // set days
            );
        }
    }
}
