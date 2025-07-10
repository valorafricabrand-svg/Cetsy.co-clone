<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This adds an unsignedBigInteger `country_id` column
     * and creates a foreign-key constraint to `countries.id`.
     * If a country is deleted, products keep the record but
     * `country_id` is set to NULL (use `cascade` if you prefer).
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // 1. Add the column (nullable so existing rows are fine)
            $table->unsignedBigInteger('country_id')
                  ->nullable()
                  ->after('featured_image');  // place it where you like

            // 2. Add the FK constraint
            $table->foreign('country_id')
                  ->references('id')
                  ->on('countries')
                  ->nullOnDelete();           // or ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * Drops the FK first, then the column.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // drop constraint explicitly (needed for MySQL / MariaDB)
            $table->dropForeign(['country_id']);
            $table->dropColumn('country_id');
        });
    }
};
