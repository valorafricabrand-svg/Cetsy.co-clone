<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVariationSummaryToOrderItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds a nullable text column `variation_summary` to store the
     * chosen variation description (e.g. "Color: Red, Size: M").
     */
    public function up()
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->text('variation_summary')
                  ->nullable()
                  ->after('product_id')
                  ->comment('Human-readable summary of chosen options');
        });
    }

    /**
     * Reverse the migrations.
     *
     * Drops the `variation_summary` column.
     */
    public function down()
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('variation_summary');
        });
    }
}
