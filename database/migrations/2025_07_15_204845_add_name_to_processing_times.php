<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNameToProcessingTimes extends Migration
{
    public function up()
    {
        Schema::table('processing_times', function (Blueprint $table) {
            // Add 'name' column before 'days' (or adjust position as you like)
            if (! Schema::hasColumn('processing_times', 'name')) {
                $table->string('name')->after('id')->default('');
            }
        });
    }

    public function down()
    {
        Schema::table('processing_times', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }
}
