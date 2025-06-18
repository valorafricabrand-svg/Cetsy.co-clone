<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWalletsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id');
            $table->decimal('credit', 15, 2)->default(0.00);  // Total credited amount
            $table->decimal('debit', 15, 2)->default(0.00);   // Total debited amount
            $table->decimal('balance', 15, 2)->default(0.00); // Current balance
            $table->string('type')->nullable();               // e.g., deposit, order_payment, refund
            $table->string('reference')->nullable();          // Optional reference for transaction
            $table->text('description')->nullable();          // Optional description

            $table->timestamps();

           
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
}
