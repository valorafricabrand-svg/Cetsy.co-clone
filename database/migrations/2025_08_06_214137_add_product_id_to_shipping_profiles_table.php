<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipping_profiles', function (Blueprint $table) {
            // Add nullable product_id right after shop_id
            $table->foreignId('product_id')
                  ->nullable()
                  ->after('shop_id')
                  ->constrained('products')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('shipping_profiles', function (Blueprint $table) {
            // Drop the FK first, then the column
            $table->dropForeign(['product_id']);
            $table->dropColumn('product_id');
        });
    }
};
