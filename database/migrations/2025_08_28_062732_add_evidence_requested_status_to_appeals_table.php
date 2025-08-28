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
        // Add the missing 'evidence_requested' status to the appeals table
        DB::statement("ALTER TABLE appeals MODIFY COLUMN status ENUM('pending', 'evidence_requested', 'approved', 'rejected') NOT NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the 'evidence_requested' status from the appeals table
        DB::statement("ALTER TABLE appeals MODIFY COLUMN status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending'");
    }
};
