<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('shipping_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('shipping_profiles', 'is_default')) {
                $table->boolean('is_default')->default(false)->after('name');
            }
            if (!Schema::hasColumn('shipping_profiles', 'rules_json')) {
                // If your MySQL < 5.7, change json() to text()
                $table->json('rules_json')->nullable()->after('processing_custom_max');
            }
            if (!Schema::hasColumn('shipping_profiles', 'upgrades_json')) {
                // If your MySQL < 5.7, change json() to text()
                $table->json('upgrades_json')->nullable()->after('rules_json');
            }
        });
    }

    public function down(): void
    {
        Schema::table('shipping_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('shipping_profiles', 'is_default')) {
                $table->dropColumn('is_default');
            }
            if (Schema::hasColumn('shipping_profiles', 'rules_json')) {
                $table->dropColumn('rules_json');
            }
            if (Schema::hasColumn('shipping_profiles', 'upgrades_json')) {
                $table->dropColumn('upgrades_json');
            }
        });
    }
};
