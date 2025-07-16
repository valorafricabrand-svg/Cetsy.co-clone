<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDiscountPercentToProductsTable extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            // Remove old discount_price if you haven’t already
            if (Schema::hasColumn('products', 'discount_price')) {
                $table->dropColumn('discount_price');
            }
            // Add new percentage discount column
            $table->unsignedTinyInteger('discount_percent')
                  ->nullable()
                  ->after('price')
                  ->comment('Percentage discount, e.g. 24 for 24%');
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            // Revert: drop discount_percent and restore discount_price
            if (Schema::hasColumn('products', 'discount_percent')) {
                $table->dropColumn('discount_percent');
            }
            $table->decimal('discount_price', 10, 2)
                  ->default(0)
                  ->after('price');
        });
    }
}
