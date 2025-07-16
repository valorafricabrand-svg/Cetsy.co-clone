<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOriginPostalCodeAndProcessingTimeIdToProductsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // 1) Add postal code
            $table->string('origin_postal_code', 20)
                  ->nullable()
                  ->after('id');

            // 2) Add the processing_time_id column
            $table->unsignedBigInteger('processing_time_id')
                  ->nullable()
                  ->after('origin_postal_code');

        
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {


            // Then drop the columns
            $table->dropColumn('processing_time_id');
            $table->dropColumn('origin_postal_code');
        });
    }
}
