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
    if (Schema::hasTable('product_variation_value')) {
        Schema::drop('product_variation_value');
    }

    Schema::create('product_variation_value', function (Blueprint $table) {
        $table->id();
        $table->foreignId('product_variation_id')
              ->constrained('product_variations')
              ->cascadeOnDelete();
        $table->foreignId('category_attribute_value_id')
              ->constrained('category_attribute_values')
              ->cascadeOnDelete();
        $table->timestamps();
        $table->unique(['product_variation_id','category_attribute_value_id'], 'pv_val_unique');
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variation_value');
    }
};
