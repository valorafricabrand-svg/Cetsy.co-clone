<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Use raw SQL to avoid requiring doctrine/dbal for column modification
        DB::statement('ALTER TABLE payout_requests MODIFY wallet_id BIGINT UNSIGNED NULL');
    }

    public function down(): void
    {
        // Warning: this will fail if null wallet_id rows exist
        DB::statement('ALTER TABLE payout_requests MODIFY wallet_id BIGINT UNSIGNED NOT NULL');
    }
};

