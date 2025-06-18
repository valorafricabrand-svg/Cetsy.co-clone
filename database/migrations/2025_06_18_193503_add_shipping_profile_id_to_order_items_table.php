<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShippingProfileIdToOrderItemsTable extends Migration
{
    public function up()
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->unsignedBigInteger('shipping_profile_id')->nullable()->after('price');
            $table->foreign('shipping_profile_id')->references('id')->on('shipping_profiles')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['shipping_profile_id']);
            $table->dropColumn('shipping_profile_id');
        });
    }
}
