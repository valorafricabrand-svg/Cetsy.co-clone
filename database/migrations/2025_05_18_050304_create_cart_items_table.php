<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->id();

            // must match carts.id (unsigned BIGINT)
            $table->unsignedBigInteger('cart_id');

            // likewise match products.id (assuming bigIncrements on products)
            $table->unsignedBigInteger('product_id');

            $table->unsignedInteger('quantity')->default(1);
            $table->timestamps();

            // foreign keys
            $table->foreign('cart_id')
                  ->references('id')
                  ->on('carts')
                  ->cascadeOnDelete();

            $table->foreign('product_id')
                  ->references('id')
                  ->on('products')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
