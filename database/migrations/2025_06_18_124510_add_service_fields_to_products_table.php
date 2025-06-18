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
        Schema::table('products', function (Blueprint $table) {
            $table->string('price_type')->nullable();
            $table->json('available_days')->nullable();
            $table->time('available_time')->nullable(); // Optional if you use from/to instead
            $table->time('available_time_from')->nullable();
            $table->time('available_time_to')->nullable();
            $table->integer('duration_value')->nullable();
            $table->string('duration_unit')->nullable(); // e.g., "minutes", "hours", "days"
            $table->boolean('is_remote')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'price_type',
                'available_days',
                'available_time',
                'available_time_from',
                'available_time_to',
                'duration_value',
                'duration_unit',
                'is_remote',
            ]);
        });
    }
};
