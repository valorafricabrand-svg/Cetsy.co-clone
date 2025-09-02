<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_items', 'downloaded_at')) {
                $table->timestamp('downloaded_at')->nullable()->after('shipping_cost');
            }
            if (!Schema::hasColumn('order_items', 'download_count')) {
                $table->unsignedInteger('download_count')->default(0)->after('downloaded_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'download_count')) {
                $table->dropColumn('download_count');
            }
            if (Schema::hasColumn('order_items', 'downloaded_at')) {
                $table->dropColumn('downloaded_at');
            }
        });
    }
};

