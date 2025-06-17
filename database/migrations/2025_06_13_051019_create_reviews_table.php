<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_item_id')->constrained('order_items')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');              // 1–5
            $table->text('comment')->nullable();
            $table->boolean('approved')->default(false);        // if you want moderation
            $table->timestamps();

            $table->unique(['order_item_id']);                  // one review per item
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
