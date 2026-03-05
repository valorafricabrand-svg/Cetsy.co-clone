<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('shops') || Schema::hasColumn('shops', 'is_holiday_mode')) {
            return;
        }

        Schema::table('shops', function (Blueprint $table) {
            $table->boolean('is_holiday_mode')->default(false)->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('shops') || !Schema::hasColumn('shops', 'is_holiday_mode')) {
            return;
        }

        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn('is_holiday_mode');
        });
    }
};
