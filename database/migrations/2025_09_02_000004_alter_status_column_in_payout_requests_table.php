<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Switch status to a flexible VARCHAR to support new states like otp_pending, sent, failed, cancelled
        DB::statement("ALTER TABLE payout_requests MODIFY status VARCHAR(32) NOT NULL");
    }

    public function down(): void
    {
        // Best-effort revert to a shorter VARCHAR; adjust if your previous schema used ENUM
        DB::statement("ALTER TABLE payout_requests MODIFY status VARCHAR(16) NOT NULL");
    }
};

