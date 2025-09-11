<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Setting;
use App\Models\Currency;

class UpdateExchangeRates extends Command
{
    protected $signature = 'rates:update';
    protected $description = 'Fetch USD-based FX rates and store in settings';

    public function handle(): int
    {
        try {
            $resp = Http::timeout(15)->acceptJson()->get('https://api.exchangerate.host/latest', [
                'base' => 'USD',
            ]);
            if (!$resp->ok()) {
                $this->error('Failed to fetch rates: ' . $resp->status());
                return self::FAILURE;
            }
            $data = $resp->json();
            $rates = $data['rates'] ?? [];
            if (!is_array($rates) || empty($rates)) {
                $this->error('Missing rates in response');
                return self::FAILURE;
            }

            // Upsert currency records
            foreach ($rates as $code => $rate) {
                if (!is_string($code)) continue;
                $code = strtoupper($code);
                $data = [
                    'usd_rate' => (float)$rate,
                    'is_active' => true,
                ];
                // Try to keep name/symbol if already set
                $existing = Currency::where('code', $code)->first();
                if ($existing) {
                    $existing->update($data);
                } else {
                    Currency::create(array_merge([
                        'code' => $code,
                        'name' => \function_exists('currencies') && isset(currencies()[$code]) ? currencies()[$code] : $code,
                        'symbol' => null,
                        'decimal_places' => 2,
                    ], $data));
                }
            }

            // Also store JSON for backward compatibility
            $settings = Setting::first() ?: Setting::create(['default_currency' => 'USD']);
            $settings->exchange_rates_json = json_encode($rates);
            $settings->exchange_rates_updated_at = now();
            $settings->save();

            $this->info('Exchange rates updated: ' . count($rates) . ' currencies');
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Error: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
