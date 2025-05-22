<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            // 1) Preferences
            $table->string('language')->after('user_id');
            $table->string('country')->after('language');
            $table->string('currency')->after('country');

            // 3) Payment details
            $table->string('bank_account')->after('logo');
            $table->string('routing_number')->after('bank_account');

            // 4) Billing address
            $table->string('address')->after('routing_number');
            $table->string('city')->after('address');
            $table->string('postal')->after('city');

            // 5) Security flag (true or false)
            $table->boolean('enable_2fa')
                  ->default(false)
                  ->after('postal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn([
                'language',
                'country',
                'currency',
                'bank_account',
                'routing_number',
                'address',
                'city',
                'postal',
                'enable_2fa',
            ]);
        });
    }
};
