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
        Schema::create('product_reviews', function (Blueprint $table) {
            $table->id();

            // Foreign keys
            $table->foreignId('product_id')
                  ->constrained()
                  ->cascadeOnDelete();              // remove reviews if product is deleted

            $table->foreignId('user_id')
                  ->constrained()
                  ->cascadeOnDelete();              // remove reviews if user is deleted

            // Review data
            $table->unsignedTinyInteger('rating')   // 1-5 stars
                  ->comment('1=worst, 5=best');

            $table->text('comment')->nullable();    // optional written review

            // Optionally track if seller replied / approved, etc.
            $table->boolean('is_approved')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_reviews');
    }
};
