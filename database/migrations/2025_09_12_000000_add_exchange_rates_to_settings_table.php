<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'exchange_rates_json')) {
                $table->longText('exchange_rates_json')->nullable()->after('couriers_json');
            }
            if (!Schema::hasColumn('settings', 'exchange_rates_updated_at')) {
                $table->timestamp('exchange_rates_updated_at')->nullable()->after('exchange_rates_json');
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'exchange_rates_updated_at')) {
                $table->dropColumn('exchange_rates_updated_at');
            }
            if (Schema::hasColumn('settings', 'exchange_rates_json')) {
                $table->dropColumn('exchange_rates_json');
            }
        });
    }
};

