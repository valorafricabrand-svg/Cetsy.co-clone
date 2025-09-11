<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Currency;

class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            // code, name, symbol, decimals, usd_rate
            ['USD', 'US Dollar', '$', 2, 1.0],
            ['EUR', 'Euro', '€', 2, 0.0],
            ['GBP', 'British Pound', '£', 2, 0.0],
            ['JPY', 'Japanese Yen', '¥', 0, 0.0],
            ['KES', 'Kenyan Shilling', 'KSh', 2, 0.0],
            ['NGN', 'Nigerian Naira', '₦', 2, 0.0],
            ['INR', 'Indian Rupee', '₹', 2, 0.0],
            ['AUD', 'Australian Dollar', 'A$', 2, 0.0],
            ['CAD', 'Canadian Dollar', 'C$', 2, 0.0],
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

