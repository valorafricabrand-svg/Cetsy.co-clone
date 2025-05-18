<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->id();                                  // unsigned BIGINT PK
            $table->unsignedBigInteger('order_id');        // FK to orders
            $table->unsignedBigInteger('product_id');      // FK to products
            $table->unsignedInteger('quantity');
            $table->decimal('price', 10, 2);               // price at time of ordering
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('order_id')
                  ->references('id')
                  ->on('orders')
                  ->cascadeOnDelete();

            $table->foreign('product_id')
                  ->references('id')
                  ->on('products')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
