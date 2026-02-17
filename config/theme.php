<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Bootstrap Compatibility During Tailwind Migration
    |--------------------------------------------------------------------------
    |
    | Keep this true while legacy Bootstrap-based pages are being migrated.
    | Set LEGACY_BOOTSTRAP_COMPAT=false to stop loading Bootstrap assets
    | from the main theme layout.
    |
    */
    'legacy_bootstrap_compat' => (bool) env('LEGACY_BOOTSTRAP_COMPAT', true),
];

