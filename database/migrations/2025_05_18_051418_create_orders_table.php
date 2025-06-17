<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Foreign key to users table (can be removed if no foreign key needed)
            $table->foreignId('shop_id')->constrained()->onDelete('cascade'); // Foreign key to shops table (can be removed if no foreign key needed)

            // Customer Information
            $table->string('full_name');
            $table->string('email');
            $table->string('phone');

            // Shipping Information
            $table->unsignedBigInteger('shipping_country_id'); // Remove foreign key reference
            $table->string('shipping_address_1');
            $table->string('shipping_address_2')->nullable();
            $table->string('shipping_city');
            $table->string('shipping_state')->nullable();
            $table->string('shipping_postal_code')->nullable();

            // Billing Information (if not same as shipping)
            $table->boolean('billing_same_as_shipping')->default(true);
            $table->unsignedBigInteger('billing_country_id')->nullable(); // Remove foreign key reference
            $table->string('billing_address_1')->nullable();
            $table->string('billing_address_2')->nullable();
            $table->string('billing_city')->nullable();
            $table->string('billing_state')->nullable();
            $table->string('billing_postal_code')->nullable();

            // Shipping Method & Payment Method
            $table->string('shipping_method');
            $table->string('payment_method')->default('paypal');

            // Order Totals
            $table->decimal('subtotal', 10, 2);
            $table->decimal('total_amount', 10, 2);
            $table->decimal('tax_amount', 10, 2)->nullable();
            $table->string('promo_code')->nullable();

            // Order Status
            $table->enum('status', ['pending', 'processing', 'shipped', 'delivered', 'canceled'])->default('pending');
            $table->timestamps();

            // Additional fields for tracking orders
            $table->text('order_notes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
