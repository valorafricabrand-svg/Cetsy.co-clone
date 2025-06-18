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
    Schema::create('offers', function (Blueprint $table) {
        $table->id();

        // FK to products table
        $table->foreignId('product_id')
              ->constrained()
              ->cascadeOnDelete();

        // FK to users table (buyer)
        $table->foreignId('buyer_id')
              ->constrained('users')
              ->cascadeOnDelete();

        // price offered (store in base currency units)
        $table->unsignedBigInteger('offer_price');

        // offer workflow status
        $table->enum('status', ['pending', 'accepted', 'declined'])
              ->default('pending');

        $table->timestamps();

        // one active offer per buyer per product (remove if you want multiples)
        $table->unique(['product_id', 'buyer_id']);
    });
}

public function down(): void
{
    Schema::dropIfExists('offers');
}

};
