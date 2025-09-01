<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add new status value to ENUM
        DB::statement("ALTER TABLE disputes MODIFY COLUMN status ENUM('pending', 'under_review', 'resolved', 'appealed', 'appeal_under_review', 'final', 'mutually_resolved')");
        
        // Add new decision value to ENUM
        DB::statement("ALTER TABLE disputes MODIFY COLUMN decision ENUM('buyer_wins', 'seller_wins', 'partial_refund', 'no_action', 'mutual_agreement')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the new status value from ENUM
        DB::statement("ALTER TABLE disputes MODIFY COLUMN status ENUM('pending', 'under_review', 'resolved', 'appealed', 'appeal_under_review', 'final')");
        
        // Remove the new decision value from ENUM
        DB::statement("ALTER TABLE disputes MODIFY COLUMN decision ENUM('buyer_wins', 'seller_wins', 'partial_refund', 'no_action')");
    }
};
