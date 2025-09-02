<?php
// database/migrations/2025_08_29_000001_widen_money_columns.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // --- payments.total_amount -> DECIMAL(18,2)
        if (Schema::hasTable('payments') && Schema::hasColumn('payments', 'total_amount')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->decimal('total_amount', 18, 2)->change();
            });
        }

        // --- wallets.credit/debit/balance -> DECIMAL(18,2)
        if (Schema::hasTable('wallets')) {
            foreach (['credit', 'debit', 'balance'] as $col) {
                if (Schema::hasColumn('wallets', $col)) {
                    Schema::table('wallets', function (Blueprint $table) use ($col) {
                        $table->decimal($col, 18, 2)->change();
                    });
                }
            }
        }

        // --- orders.total_amount -> DECIMAL(18,2) (if present)
        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'total_amount')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->decimal('total_amount', 18, 2)->change();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('payments') && Schema::hasColumn('payments', 'total_amount')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->decimal('total_amount', 10, 2)->change();
            });
        }

        if (Schema::hasTable('wallets')) {
            foreach (['credit', 'debit', 'balance'] as $col) {
                if (Schema::hasColumn('wallets', $col)) {
                    Schema::table('wallets', function (Blueprint $table) use ($col) {
                        $table->decimal($col, 10, 2)->change();
                    });
                }
            }
        }

        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'total_amount')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->decimal('total_amount', 10, 2)->change();
            });
        }
    }
};
