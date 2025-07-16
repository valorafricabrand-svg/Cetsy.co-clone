<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeSkuNullableOnProductVariations extends Migration
{
    public function up()
    {
        Schema::table('product_variations', function (Blueprint $table) {
            // Requires doctrine/dbal to change column
            $table->string('sku')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('product_variations', function (Blueprint $table) {
            $table->string('sku')->nullable(false)->change();
        });
    }
}
