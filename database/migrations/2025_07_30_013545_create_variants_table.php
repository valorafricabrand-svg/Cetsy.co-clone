<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVariantsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('variants', function (Blueprint $table) {
            $table->id();

            // Link back to the parent product
            $table->foreignId('product_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // SKU (if you vary or set it manually)
            $table->string('sku')->nullable()->unique();

            // Base price for this variant
            $table->decimal('price', 10, 2)->default(0);

            // Stock/quantity for this variant
            $table->integer('stock')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('variants');
    }
}
