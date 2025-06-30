<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameCountryToCountryIdOnShippingProfilesTable extends Migration
{
    public function up(): void
    {
        Schema::table('shipping_profiles', function (Blueprint $table) {
            $table->renameColumn('country', 'country_id');
        });
    }

    public function down(): void
    {
        Schema::table('shipping_profiles', function (Blueprint $table) {
            $table->renameColumn('country_id', 'country');
        });
    }
}
