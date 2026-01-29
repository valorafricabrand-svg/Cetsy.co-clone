<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'payout_schedule')) {
                $table->string('payout_schedule', 20)->nullable()->after('auto_release_days');
            }
            if (!Schema::hasColumn('settings', 'payout_weekday')) {
                $table->unsignedTinyInteger('payout_weekday')->nullable()->after('payout_schedule');
            }
            if (!Schema::hasColumn('settings', 'payout_month_day')) {
                $table->unsignedTinyInteger('payout_month_day')->nullable()->after('payout_weekday');
            }
            if (!Schema::hasColumn('settings', 'payout_auto_approve')) {
                $table->boolean('payout_auto_approve')->default(false)->after('payout_month_day');
            }
            if (!Schema::hasColumn('settings', 'payout_auto_disburse')) {
                $table->boolean('payout_auto_disburse')->default(false)->after('payout_auto_approve');
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $cols = [
                'payout_auto_disburse',
                'payout_auto_approve',
                'payout_month_day',
                'payout_weekday',
                'payout_schedule',
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('settings', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
