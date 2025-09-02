<?php

return [
    // Percentage fee applied to payout requests (e.g., 0.015 = 1.5%)
    'fee_rate' => env('PAYOUT_FEE_RATE', 0.015),

    // Minimum payout amount (net before fee)
    'min_amount' => env('PAYOUT_MIN_AMOUNT', 1),
];

