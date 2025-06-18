<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // products table
Schema::create('products', function (Blueprint $table) {
    $table->id();
    $table->foreignId('shop_id')->constrained()->onDelete('cascade'); // seller/shop
    $table->string('name');
    $table->text('description')->nullable();
    $table->enum('type', ['physical', 'digital', 'service']);
    $table->decimal('price', 10, 2);
    $table->decimal('discount_price', 10, 2)->nullable();
    $table->integer('stock')->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});

// digital files (for digital products)
Schema::create('product_files', function (Blueprint $table) {
    $table->id();
    $table->foreignId('product_id')->constrained()->onDelete('cascade');
    $table->string('file_path');
    $table->timestamps();
});
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
