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
        // Add 'closed' status to the disputes status enum
        DB::statement("ALTER TABLE disputes MODIFY COLUMN status ENUM('pending', 'under_review', 'resolved', 'appealed', 'appeal_under_review', 'final', 'mutually_resolved', 'closed')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'closed' status from the disputes status enum
        DB::statement("ALTER TABLE disputes MODIFY COLUMN status ENUM('pending', 'under_review', 'resolved', 'appealed', 'appeal_under_review', 'final', 'mutually_resolved')");
    }
};
