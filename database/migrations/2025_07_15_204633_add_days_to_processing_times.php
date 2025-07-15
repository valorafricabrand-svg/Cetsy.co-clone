<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDaysToProcessingTimes extends Migration
{
    public function up()
    {
        Schema::table('processing_times', function (Blueprint $table) {
            $table->integer('days')->unsigned()->after('id')->default(0);
        });
    }

    public function down()
    {
        Schema::table('processing_times', function (Blueprint $table) {
            $table->dropColumn('days');
        });
    }
}
