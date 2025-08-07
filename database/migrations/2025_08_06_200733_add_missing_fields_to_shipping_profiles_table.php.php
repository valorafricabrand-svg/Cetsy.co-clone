<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipping_profiles', function (Blueprint $table) {
            // Where orders ship from
            if (!Schema::hasColumn('shipping_profiles', 'origin_postal_code')) {
                $table->string('origin_postal_code', 30)->nullable()->after('country_id');
            }

            // Processing time (custom range if not using preset)
            if (!Schema::hasColumn('shipping_profiles', 'processing_custom_min')) {
                $table->unsignedInteger('processing_custom_min')->nullable()->after('processing_time_id');
            }
            if (!Schema::hasColumn('shipping_profiles', 'processing_custom_max')) {
                $table->unsignedInteger('processing_custom_max')->nullable()->after('processing_custom_min');
            }

            // Destination rules & upgrades (JSON blobs)
            if (!Schema::hasColumn('shipping_profiles', 'rules_json')) {
                $table->json('rules_json')->nullable()->after('pickup_available');
            }
            if (!Schema::hasColumn('shipping_profiles', 'upgrades_json')) {
                $table->json('upgrades_json')->nullable()->after('rules_json');
            }

            // Package details (optional)
            if (!Schema::hasColumn('shipping_profiles', 'weight')) {
                $table->string('weight', 50)->nullable()->after('upgrades_json');
            }
            if (!Schema::hasColumn('shipping_profiles', 'length')) {
                $table->string('length', 50)->nullable()->after('weight');
            }
            if (!Schema::hasColumn('shipping_profiles', 'width')) {
                $table->string('width', 50)->nullable()->after('length');
            }
            if (!Schema::hasColumn('shipping_profiles', 'height')) {
                $table->string('height', 50)->nullable()->after('width');
            }
            if (!Schema::hasColumn('shipping_profiles', 'shipping_class')) {
                $table->string('shipping_class', 100)->nullable()->after('height');
            }

            // Requires shipping?
            if (!Schema::hasColumn('shipping_profiles', 'requires_shipping')) {
                $table->boolean('requires_shipping')->default(false)->after('shipping_class');
            }
        });
    }

    public function down(): void
    {
        Schema::table('shipping_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('shipping_profiles', 'requires_shipping')) {
                $table->dropColumn('requires_shipping');
            }
            if (Schema::hasColumn('shipping_profiles', 'shipping_class')) {
                $table->dropColumn('shipping_class');
            }
            if (Schema::hasColumn('shipping_profiles', 'height')) {
                $table->dropColumn('height');
            }
            if (Schema::hasColumn('shipping_profiles', 'width')) {
                $table->dropColumn('width');
            }
            if (Schema::hasColumn('shipping_profiles', 'length')) {
                $table->dropColumn('length');
            }
            if (Schema::hasColumn('shipping_profiles', 'weight')) {
                $table->dropColumn('weight');
            }
            if (Schema::hasColumn('shipping_profiles', 'upgrades_json')) {
                $table->dropColumn('upgrades_json');
            }
            if (Schema::hasColumn('shipping_profiles', 'rules_json')) {
                $table->dropColumn('rules_json');
            }
            if (Schema::hasColumn('shipping_profiles', 'processing_custom_max')) {
                $table->dropColumn('processing_custom_max');
            }
            if (Schema::hasColumn('shipping_profiles', 'processing_custom_min')) {
                $table->dropColumn('processing_custom_min');
            }
            if (Schema::hasColumn('shipping_profiles', 'origin_postal_code')) {
                $table->dropColumn('origin_postal_code');
            }
        });
    }
};
