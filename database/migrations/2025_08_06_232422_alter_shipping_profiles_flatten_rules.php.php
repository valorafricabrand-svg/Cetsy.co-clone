<?php
// database/migrations/2025_08_07_000001_alter_shipping_profiles_flatten_rules.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('shipping_profiles', function (Blueprint $table) {
            // Grouping (profile name and default toggle)
            if (!Schema::hasColumn('shipping_profiles', 'profile_name')) {
                $table->string('profile_name', 100)->nullable()->after('product_id');
            }
            if (!Schema::hasColumn('shipping_profiles', 'is_default')) {
                $table->boolean('is_default')->default(false)->after('profile_name');
            }

            // Origin fields (if not already present)
            if (!Schema::hasColumn('shipping_profiles', 'country_id')) {
                // origin country (kept as your previous name)
                $table->unsignedBigInteger('country_id')->nullable()->after('is_default');
            }
            if (!Schema::hasColumn('shipping_profiles', 'origin_postal_code')) {
                $table->string('origin_postal_code', 50)->nullable()->after('country_id');
            }

            // Processing time (preset id or custom)
            if (!Schema::hasColumn('shipping_profiles', 'processing_time_id')) {
                $table->unsignedTinyInteger('processing_time_id')->nullable()->after('origin_postal_code');
            }
            if (!Schema::hasColumn('shipping_profiles', 'processing_custom_min')) {
                $table->unsignedSmallInteger('processing_custom_min')->nullable()->after('processing_time_id');
            }
            if (!Schema::hasColumn('shipping_profiles', 'processing_custom_max')) {
                $table->unsignedSmallInteger('processing_custom_max')->nullable()->after('processing_custom_min');
            }

            // DESTINATION + SERVICE (per-row rule columns)
            if (!Schema::hasColumn('shipping_profiles', 'dest_location_type')) {
                $table->enum('dest_location_type', ['country','everywhere_else'])
                      ->default('country')->after('processing_custom_max');
            }
            if (!Schema::hasColumn('shipping_profiles', 'dest_country_id')) {
                $table->unsignedBigInteger('dest_country_id')->nullable()->after('dest_location_type');
            }
            if (!Schema::hasColumn('shipping_profiles', 'service')) {
                $table->string('service', 100)->default('Other')->after('dest_country_id');
            }
            if (!Schema::hasColumn('shipping_profiles', 'days_min')) {
                $table->unsignedSmallInteger('days_min')->nullable()->after('service');
            }
            if (!Schema::hasColumn('shipping_profiles', 'days_max')) {
                $table->unsignedSmallInteger('days_max')->nullable()->after('days_min');
            }
            if (!Schema::hasColumn('shipping_profiles', 'charge_type')) {
                $table->enum('charge_type', ['fixed','free'])->default('fixed')->after('days_max');
            }

            // RATES (your request)
            if (!Schema::hasColumn('shipping_profiles', 'base_rate')) {     // price_one → base_rate
                $table->decimal('base_rate', 10, 2)->default(0)->after('charge_type');
            }
            if (!Schema::hasColumn('shipping_profiles', 'additional_rate')) { // price_two → additional_rate
                $table->decimal('additional_rate', 10, 2)->default(0)->after('base_rate');
            }

            // Indexing helpers
            if (!Schema::hasColumn('shipping_profiles', 'idx_added')) {
                $table->index(['shop_id','product_id','profile_name'], 'sp_shop_product_profile_idx');
                $table->index(['dest_location_type','dest_country_id'], 'sp_destination_idx');
                // a dummy column just to guard double-adding indexes (not persisted)
            }
        });
    }

    public function down(): void
    {
        Schema::table('shipping_profiles', function (Blueprint $table) {
            // Keep columns; rolling back selectively could drop live data.
            // If you must, you can drop columns here.
        });
    }
};
