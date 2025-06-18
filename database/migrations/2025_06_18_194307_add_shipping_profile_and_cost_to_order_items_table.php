<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('order_items', function (Blueprint $table) {


            $table->decimal('shipping_cost', 10, 2)->default(0)->after('shipping_profile_id');

        });
    }

    public function down()
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('shipping_cost');
        });
    }
};
