<?php
// database/migrations/2025_08_21_120000_patch_wallets_table_add_columns.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            // Add columns if missing (idempotent)
            if (!Schema::hasColumn('wallets', 'reference')) {
                $table->string('reference', 100)->nullable()->after('balance');
            }
            if (!Schema::hasColumn('wallets', 'method')) {
                $table->string('method', 50)->nullable()->after('reference');
            }
            if (!Schema::hasColumn('wallets', 'description')) {
                $table->string('description', 255)->nullable()->after('method');
            }
            if (!Schema::hasColumn('wallets', 'external_id')) {
                $table->string('external_id', 191)->nullable()->after('description');
                $table->index('external_id');
            }
            if (!Schema::hasColumn('wallets', 'meta')) {
                $table->json('meta')->nullable()->after('external_id');
            }

            // Ensure balance column exists (some schemas used nullable/0)
            if (!Schema::hasColumn('wallets', 'balance')) {
                $table->decimal('balance', 12, 2)->default(0)->after('debit');
            }
        });

        // Backfill NULL methods to 'wallet' for consistency
        DB::table('wallets')->whereNull('method')->update(['method' => 'wallet']);
    }

    public function down(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            if (Schema::hasColumn('wallets', 'meta')) {
                $table->dropColumn('meta');
            }
            if (Schema::hasColumn('wallets', 'external_id')) {
                $table->dropIndex(['external_id']);
                $table->dropColumn('external_id');
            }
            if (Schema::hasColumn('wallets', 'description')) {
                $table->dropColumn('description');
            }
            if (Schema::hasColumn('wallets', 'method')) {
                $table->dropColumn('method');
            }
            if (Schema::hasColumn('wallets', 'reference')) {
                $table->dropColumn('reference');
            }
            // Do NOT drop balance in down() unless you really want to
            // if (Schema::hasColumn('wallets', 'balance')) {
            //     $table->dropColumn('balance');
            // }
        });
    }
};
