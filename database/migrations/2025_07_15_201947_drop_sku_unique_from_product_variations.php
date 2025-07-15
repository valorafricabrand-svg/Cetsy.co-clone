<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropSkuUniqueFromProductVariations extends Migration
{
    public function up()
    {
        Schema::table('product_variations', function (Blueprint $table) {
            // MySQL default index name is table_column_unique
            $table->dropUnique('product_variations_sku_unique');
        });
    }

    public function down()
    {
        Schema::table('product_variations', function (Blueprint $table) {
            $table->unique('sku');
        });
    }
}
