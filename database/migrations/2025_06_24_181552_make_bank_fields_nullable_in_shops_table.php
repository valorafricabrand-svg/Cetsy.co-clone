<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeBankFieldsNullableInShopsTable extends Migration
{
    public function up()
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->string('bank_account')->nullable()->change();
            $table->string('routing_number')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->string('bank_account')->nullable(false)->change();
            $table->string('routing_number')->nullable(false)->change();
        });
    }
}
