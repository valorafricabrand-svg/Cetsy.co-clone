<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('currencies')) {
            // Table should exist already; if not, skip gracefully
            return;
        }

        Schema::table('currencies', function (Blueprint $table) {
            if (!Schema::hasColumn('currencies', 'code')) {
                $table->string('code', 3)->unique()->after('id');
            }
            if (!Schema::hasColumn('currencies', 'name')) {
                $table->string('name', 64)->nullable()->after('code');
            }
            if (!Schema::hasColumn('currencies', 'symbol')) {
                $table->string('symbol', 8)->nullable()->after('name');
            }
            if (!Schema::hasColumn('currencies', 'decimal_places')) {
                $table->unsignedTinyInteger('decimal_places')->default(2)->after('symbol');
            }
            if (!Schema::hasColumn('currencies', 'usd_rate')) {
                $table->decimal('usd_rate', 18, 8)->default(1.00000000)->after('decimal_places');
            }
            if (!Schema::hasColumn('currencies', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('usd_rate');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('currencies')) return;

        Schema::table('currencies', function (Blueprint $table) {
            if (Schema::hasColumn('currencies', 'is_active')) {
                $table->dropColumn('is_active');
            }
            if (Schema::hasColumn('currencies', 'usd_rate')) {
                $table->dropColumn('usd_rate');
            }
            if (Schema::hasColumn('currencies', 'decimal_places')) {
                $table->dropColumn('decimal_places');
            }
            if (Schema::hasColumn('currencies', 'symbol')) {
                $table->dropColumn('symbol');
            }
            if (Schema::hasColumn('currencies', 'name')) {
                $table->dropColumn('name');
            }
            if (Schema::hasColumn('currencies', 'code')) {
                // Drop unique index implicitly with column
                $table->dropColumn('code');
            }
        });
    }
};

