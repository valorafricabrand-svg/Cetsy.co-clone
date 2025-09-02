<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            // Store fee as percent (e.g., 1.5 for 1.5%)
            if (!Schema::hasColumn('settings', 'fee_rate')) {
                $table->decimal('fee_rate', 6, 3)->default(1.500)->after('default_currency');
            }
            if (!Schema::hasColumn('settings', 'min_amount')) {
                $table->decimal('min_amount', 12, 2)->default(1.00)->after('fee_rate');
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'fee_rate')) {
                $table->dropColumn('fee_rate');
            }
            if (Schema::hasColumn('settings', 'min_amount')) {
                $table->dropColumn('min_amount');
            }
        });
    }
};

