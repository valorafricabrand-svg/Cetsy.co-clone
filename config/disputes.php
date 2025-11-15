<?php

return [
    // Master feature flag to enable/disable Appeals across the app
    'enable_appeals' => env('DISPUTES_ENABLE_APPEALS', false),
    // Disable mutual resolution feature for buyer/seller flows
    'enable_mutual_resolution' => env('DISPUTES_ENABLE_MUTUAL_RESOLUTION', false),
];
