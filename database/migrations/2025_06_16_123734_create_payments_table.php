<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('user_id')->nullable(); // Foreign key removed
            $table->uuid('order_id')->nullable(); 
            $table->integer('payment_type')->default('0')->comment('0=Buyers Payments, 1=Sellers Payments');
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone');
            $table->decimal('total_amount')->default(0);
            $table->string('payment_method')->default('MPESA')->comment('MPESA,CARD,PAYPAL');
            $table->string('payment_ref_code')->nullable()->comment('if Mpesa save mpesa code');
            $table->string('payment_name')->nullable();
            $table->string('payment_number')->nullable();
            $table->string('currency')->default('USD');
            $table->string('payment_status')->default('pending')->comment('pending,failed,canceled,successful');
            $table->integer('paymentStatus')->default(0)->comment('0=pending,1=failed,2=canceled,3=successful');
            $table->text('payment_data')->nullable();
            $table->string('payment_notified')->default('false');
            $table->string('OrderTrackingNumber')->nullable();
            $table->longText('more_details')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payments');
    }
};
