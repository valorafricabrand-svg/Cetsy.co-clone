<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->unsignedBigInteger('product_variation_id')->nullable()->after('product_id');
            // If you want FK:
            // $table->foreign('product_variation_id')->references('id')->on('product_variations')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            // $table->dropForeign(['product_variation_id']);
            $table->dropColumn('product_variation_id');
        });
    }
};
