<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('channel', 50);              // paypal | mpesa_stk | bank | ...
            $table->string('external_id', 191)->nullable()->index(); // CheckoutRequestID / PayPal order id etc.

            $table->decimal('amount_usd', 12, 2)->nullable();
            $table->unsignedBigInteger('amount_kes')->nullable();    // convenience for M-Pesa

            $table->string('status', 30)->default('pending'); // pending|success|failed|reversed
            $table->json('meta')->nullable();

            $table->timestamps();
        });

        // Add wallet_balance to users if not present
        if (!Schema::hasColumn('users','wallet_balance')) {
            Schema::table('users', function (Blueprint $table) {
                $table->decimal('wallet_balance', 12, 2)->default(0)->after('remember_token');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');

        if (Schema::hasColumn('users','wallet_balance')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('wallet_balance');
            });
        }
    }
};
