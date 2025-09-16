<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class RenameCountryToCountryIdOnShippingProfilesTable extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            Schema::table('shipping_profiles', function (Blueprint $table) {
                $table->renameColumn('country', 'country_id');
            });

            return;
        }

        DB::statement("ALTER TABLE `shipping_profiles` CHANGE `country` `country_id` VARCHAR(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'KE'");
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            Schema::table('shipping_profiles', function (Blueprint $table) {
                $table->renameColumn('country_id', 'country');
            });

            return;
        }

        DB::statement("ALTER TABLE `shipping_profiles` CHANGE `country_id` `country` VARCHAR(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'KE'");
    }
}
