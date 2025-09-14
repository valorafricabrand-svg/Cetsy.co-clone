<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Currency;

class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            // code, name, symbol, decimals, usd_rate (approx defaults)
            ['USD', 'US Dollar', '$', 2, 1.00],
            ['EUR', 'Euro', '€', 2, 0.92],
            ['GBP', 'British Pound', '£', 2, 0.78],
            ['JPY', 'Japanese Yen', '¥', 0, 150.00],
            ['KES', 'Kenyan Shilling', 'KSh', 2, (float) env('USD_TO_KES', 130)],
            ['NGN', 'Nigerian Naira', '₦', 2, 1600.00],
            ['INR', 'Indian Rupee', '₹', 2, 84.00],
            ['AUD', 'Australian Dollar', 'A$', 2, 1.50],
            ['CAD', 'Canadian Dollar', 'C$', 2, 1.35],
        ];

        foreach ($rows as [$code, $name, $symbol, $decimals, $rate]) {
            Currency::updateOrCreate(
                ['code' => $code],
                [
                    'name' => $name,
                    'symbol' => $symbol,
                    'decimal_places' => $decimals,
                    'usd_rate' => $rate,
                    'is_active' => true,
                ]
            );
        }
    }
}
