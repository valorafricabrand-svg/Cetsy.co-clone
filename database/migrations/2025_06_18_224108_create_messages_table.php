<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();

            // sender = the buyer               (auth user)
            $table->foreignId('sender_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            // receiver = the seller (shop owner)
            $table->foreignId('receiver_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            // optional: link to a product
            $table->foreignId('product_id')
                  ->nullable()
                  ->constrained()
                  ->nullOnDelete();

            $table->text('body');
            $table->boolean('is_read')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};

