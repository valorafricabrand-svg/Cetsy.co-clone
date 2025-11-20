<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bulk_price_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_id')->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('direction', 10); // up or down
            $table->decimal('percent', 8, 2);
            $table->string('column', 32); // price or sale_price
            $table->unsignedTinyInteger('round_to')->default(2);
            $table->boolean('apply_all')->default(false);
            $table->unsignedInteger('selection_count')->nullable(); // when not apply_all
            $table->unsignedInteger('affected_products')->default(0);
            $table->unsignedInteger('affected_variants')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bulk_price_logs');
    }
};

