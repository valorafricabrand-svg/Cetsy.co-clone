<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRenewalTypeToProductsTable extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->enum('renewal_type', ['automatic', 'manual'])
                  ->default('automatic')
                  ->after('id'); 
                  // ← replace with a real column name in your table
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('renewal_type');
        });
    }
}
