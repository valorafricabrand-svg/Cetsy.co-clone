<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        /* -----------------------------------------------------------
         |  1. product_variations  (SKU / price / stock)
         |----------------------------------------------------------- */
        Schema::create('product_variations', function (Blueprint $table) {
            $table->id();

            // just an unsigned big integer, no FK
            $table->unsignedBigInteger('product_id');

            $table->string('sku')->unique();
            $table->decimal('price', 10, 2);
            $table->unsignedInteger('stock')->default(0);

            /* optional override thumbnail per variation */
            $table->string('image')->nullable();

            $table->timestamps();
        });

        /* -----------------------------------------------------------
         |  2. product_variation_value  (pivot)
         |----------------------------------------------------------- */
        Schema::create('product_variation_value', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('product_variation_id');
            $table->unsignedBigInteger('product_option_value_id');

            $table->timestamps();

            /* prevent duplicate links (variation_id + value_id) */
            $table->unique(
                ['product_variation_id', 'product_option_value_id'],
                'pv_value_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variation_value');
        Schema::dropIfExists('product_variations');
    }
};
