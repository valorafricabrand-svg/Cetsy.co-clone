<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add 'expired' to the enum values for offers.status
        DB::statement("ALTER TABLE `offers` MODIFY COLUMN `status` ENUM('pending','accepted','declined','expired') NOT NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safely convert any 'expired' statuses back to 'declined' before shrinking the enum
        DB::statement("UPDATE `offers` SET `status` = 'declined' WHERE `status` = 'expired'");
        DB::statement("ALTER TABLE `offers` MODIFY COLUMN `status` ENUM('pending','accepted','declined') NOT NULL DEFAULT 'pending'");
    }
};

