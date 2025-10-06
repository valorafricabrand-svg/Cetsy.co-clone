<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('subscriptions') && !Schema::hasColumn('subscriptions', 'shop_id')) {
            Schema::table('subscriptions', function (Blueprint $table) {
                $table->foreignId('shop_id')->nullable()->after('user_id')->constrained('shops')->nullOnDelete();
            });
        }

        if (Schema::hasTable('shops') && !Schema::hasColumn('shops', 'is_active')) {
            Schema::table('shops', function (Blueprint $table) {
                $table->boolean('is_active')->default(true)->after('logo');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('subscriptions') && Schema::hasColumn('subscriptions', 'shop_id')) {
            Schema::table('subscriptions', function (Blueprint $table) {
                $table->dropConstrainedForeignId('shop_id');
            });
        }

        if (Schema::hasTable('shops') && Schema::hasColumn('shops', 'is_active')) {
            Schema::table('shops', function (Blueprint $table) {
                $table->dropColumn('is_active');
            });
        }
    }
};

