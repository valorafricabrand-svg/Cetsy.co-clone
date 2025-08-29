<?php
// database/migrations/2025_08_29_000000_add_order_id_to_wallets_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            // Adds nullable FK -> orders.id, indexed, and sets NULL if an order is deleted
            $table->foreignId('order_id')
                  ->nullable()
                  ->after('user_id')
                  ->constrained('orders')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            // Drop FK then column (order matters)
            $table->dropForeign(['order_id']);
            $table->dropColumn('order_id');
        });
    }
};
