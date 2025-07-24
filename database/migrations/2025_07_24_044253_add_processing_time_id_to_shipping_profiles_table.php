<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipping_profiles', function (Blueprint $table) {
            // Add processing_time_id FK (nullable) after base_rate
            $table->unsignedBigInteger('processing_time_id')
                  ->nullable()
                  ->after('base_rate');

            // If you have a processing_times table, add a foreign key:
            // $table->foreign('processing_time_id')
            //       ->references('id')
            //       ->on('processing_times')
            //       ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('shipping_profiles', function (Blueprint $table) {
            // If you added the FK, drop it first:
            // $table->dropForeign(['processing_time_id']);

            $table->dropColumn('processing_time_id');
        });
    }
};
