<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDefaultsToShippingAndPaymentMethodInOrdersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // give shipping_method a default
            $table->string('shipping_method')
                  ->default('standard')
                  ->change();

            // give payment_method a default
            $table->string('payment_method')
                  ->default('pending')
                  ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // remove the defaults
            $table->string('shipping_method')
                  ->default(null)
                  ->change();

            $table->string('payment_method')
                  ->default(null)
                  ->change();
        });
    }
}
