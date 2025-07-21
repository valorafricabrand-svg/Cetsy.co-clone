<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShopIdToDealsTable extends Migration
{
    public function up()
    {
        Schema::table('deals', function (Blueprint $table) {
            // If you have a shops table, constrain it:
            $table->foreignId('shop_id')
                  ->after('id')
                  ->constrained('shops')
                  ->cascadeOnDelete();
        });
    }

    public function down()
    {
        Schema::table('deals', function (Blueprint $table) {
            $table->dropForeign(['shop_id']);
            $table->dropColumn('shop_id');
        });
    }
}
