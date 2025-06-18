<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')
                  ->constrained()
                  ->cascadeOnDelete();
            $table->foreignId('category_id')
                  ->nullable()
                  ->constrained()
                  ->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->unsignedInteger('stock')->default(0);
            $table->enum('status', ['draft', 'active', 'archived'])
                  ->default('draft');


                  $table->boolean('renewal_option')->default(0);
                  $table->unsignedBigInteger('listTypeFee_id');
          
                  $table->string('variation_one_name')->nullable();
                  $table->string('variation_two_name')->nullable();
          
                  $table->unsignedBigInteger('origin_id');
                  $table->string('origin_postal_code', 50)->nullable();
                  $table->unsignedBigInteger('processing_time_id');
          
                  $table->unsignedBigInteger('local_shipping_service_id')->nullable();
                  $table->string('local_shipping_service_other')->nullable();
                  $table->unsignedBigInteger('localshippingPeriod_id')->nullable();
                  $table->decimal('local_default_shipping_price', 10, 2)->nullable();
                  $table->decimal('local_shipping_price', 10, 2)->nullable();
          
                  $table->integer('shipping_type')->nullable();
                  $table->unsignedBigInteger('international_shipping_service_id')->nullable();
                  $table->string('international_shipping_service_other')->nullable();
                  $table->unsignedBigInteger('internationalshippingPeriod_id')->nullable();
                  $table->decimal('default_shipping_price', 10, 2)->nullable();
                  $table->decimal('shipping_price', 10, 2)->nullable();
                  $table->integer('shipping_type_other')->nullable();
          
                  $table->boolean('item_return')->nullable();
                  $table->boolean('item_exchange')->nullable();
                  $table->integer('total_return_days')->nullable();
          
                  // Foreign key constraints (optional)
                  $table->foreign('listTypeFee_id')->references('id')->on('listing_fee_types');
                  $table->foreign('origin_id')->references('id')->on('countries');
                  $table->foreign('processing_time_id')->references('id')->on('processing_times');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
