<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            // Skip enum ALTER for SQLite; not required for tests
            return;
        }

        // Add 'expired' to the enum values for offers.status (MySQL/MariaDB)
        DB::statement("ALTER TABLE `offers` MODIFY COLUMN `status` ENUM('pending','accepted','declined','expired') NOT NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            return;
        }

        // Safely convert any 'expired' statuses back to 'declined' before shrinking the enum
        DB::statement("UPDATE `offers` SET `status` = 'declined' WHERE `status` = 'expired'");
        DB::statement("ALTER TABLE `offers` MODIFY COLUMN `status` ENUM('pending','accepted','declined') NOT NULL DEFAULT 'pending'");
    }
};
