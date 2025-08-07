<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Adds a nullable unsigned BIGINT column and FK -> processing_times.id
            // nullOnDelete() keeps product rows but sets the FK to NULL if the processing time is deleted.
            if (!Schema::hasColumn('products', 'processing_time_id')) {
                $table->foreignId('processing_time_id')
                      ->nullable()
                      ->after('origin_postal_code') // adjust position as you prefer
                      ->constrained('processing_times')
                      ->nullOnDelete()
                      ->cascadeOnUpdate();
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'processing_time_id')) {
                // Drop FK first, then the column
                $table->dropForeign(['processing_time_id']);
                $table->dropColumn('processing_time_id');
            }
        });
    }
};
