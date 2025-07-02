<?php

// database/migrations/xxxx_xx_xx_alter_status_enum_on_orders_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // 👇 add 'cancelled' to the list
            $table->enum('status', [
                'pending',
                'processing',
                'shipped',
                'delivered',
                'cancelled',
            ])->default('pending')->change();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // roll back to the old enum
            $table->enum('status', [
                'pending',
                'processing',
                'shipped',
                'delivered',
            ])->default('pending')->change();
        });
    }
};
