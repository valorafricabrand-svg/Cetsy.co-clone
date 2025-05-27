<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOptionKeyAndOptionValueToSettingsTable extends Migration
{
    public function up()
    {
        Schema::table('settings', function (Blueprint $table) {
            // Add the new columns after the existing ones
            $table->string('option_key')->after('id')->unique();
            $table->text('option_value')->nullable()->after('option_key');
        });
    }

    public function down()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['option_key', 'option_value']);
        });
    }
}
