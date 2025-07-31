<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('variant_variation_option', function (Blueprint $table) {
            $table->unsignedBigInteger('variant_id');
            $table->unsignedBigInteger('variation_option_id');

            // Composite primary key to prevent duplicates
            $table->primary(['variant_id', 'variation_option_id']);

            // Indexes (optional but helpful)
            $table->index('variant_id');
            $table->index('variation_option_id');

            // FKs
            $table->foreign('variant_id')
                  ->references('id')->on('variants')
                  ->onDelete('cascade');

            $table->foreign('variation_option_id')
                  ->references('id')->on('variation_options')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('variant_variation_option');
    }
};
